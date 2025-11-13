<?php

namespace App\Filament\Portal\Actions;

use App\Enums\AppointmentRequestStatus;
use App\Enums\Icons\PhosphorIcons;
use App\Models\AppointmentRequest;
use App\Models\Branch;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class AppointmentRequestAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $branches = Branch::where('organisation_id', auth()->user()->organisation_id)->get();
        $patients = Patient::where('client_id', auth()->id())->get();
        $services = Service::all();

        $this->requiresConfirmation();
        $this->slideOver();
        $this->modalWidth(Width::FourExtraLarge);
        $this->modalHeading('New appointment request');
        $this->modalDescription('Select date and time for your appointment');
        $this->modalIcon(PhosphorIcons::CalendarPlus);
        $this->steps([
            Step::make('Select pet')
                ->icon(PhosphorIcons::Dog)
                ->schema(function () use ($patients, $services, $branches) {
                    return [
                        Radio::make('branch_id')
                            ->label('Select preferred location')
                            ->options(function () use ($patients, $branches) {
                                return $branches->pluck('name', 'id');
                            })
                            ->descriptions(function () use ($branches) {
                                return $branches
                                    ->mapWithKeys(fn($branch) => [
                                        $branch->id => $branch->name
                                    ])
                                    ->toArray();
                            }),

                        Radio::make('patient_id')
                            ->label('Select your pet')
                            ->options(function () use ($patients) {
                                return $patients->pluck('name', 'id');
                            })
                            ->descriptions(function () use ($patients) {
                                return $patients
                                    ->mapWithKeys(fn($patient) => [
                                        $patient->id => "{$patient->species->name} ({$patient->breed->name})"
                                    ])
                                    ->toArray();
                            }),

                        Flex::make([
                            TextInput::make('reason_for_comming')
                                ->label('Reason for coming'),

                            Select::make('service_id')
                                ->live(true)
                                ->label('Select service')
                                ->options($services->pluck('name', 'id'))
                                ->required()
                        ])
                    ];
                }),
            Step::make('Select time')
                ->icon(Heroicon::Clock)
                ->schema([
                    DatePicker::make('date')
                        ->label('Select date')
                        ->native(false)
                        ->live(true)
                        ->default(now())
                        ->required(),

                    Radio::make('time')
                        ->hiddenLabel()
                        ->required()
                        ->columns(3)
                        ->disabled(function ($get) {
                            return !$get('date');
                        })
                        ->descriptions(function ($get) {
                            if (!$get('date') || !$get('service_id')) return [];

                            return $this->calculateAvailableSlots($get)
                                ->mapWithKeys(fn($slot) => [$slot['start_time'] => $slot['user']['full_name']])
                                ->toArray();
                        })
                        ->options(function ($get) {
                            if (!$get('date') || !$get('service_id')) return [];

                            return $this->calculateAvailableSlots($get)
                                ->mapWithKeys(fn($slot) => [$slot['start_time'] => $slot['start_time']])
                                ->toArray();
                        })
                ]),
            Step::make('Additional information')
                ->icon(PhosphorIcons::Note)
                ->schema([
                    Textarea::make('note')
                        ->hint('Enter any additional information about the appointment, such as special instructions or any other details.')
                        ->label('Note'),

                    FileUpload::make('attachments')
                        ->label('Attachments')
                ]),
            Step::make('Summary')
                ->icon(Heroicon::CheckCircle)
                ->schema([
                    Text::make('Summary of your request')
                        ->size(TextSize::Large)
                        ->icon(PhosphorIcons::CheckCircleBold)
                        ->weight(FontWeight::Bold),

                    TextEntry::make('patient.name')
                        ->label('Pet')
                        ->icon(PhosphorIcons::Dog)
                        ->getStateUsing(function ($get, $action) use ($patients) {
                            if (!$get('patient_id')) return null;

                            return $patients->find($get('patient_id'))->name;
                        })
                ])
        ]);
        $this->action(function (array $data) {
            $serviceDuration = Service::find($data['service_id'])->duration->minute;

            $date = Carbon::parse($data['date']);
            $from = $date->copy()->setTimeFromTimeString($data['time']);
            $to = $from->copy()->addMinutes($serviceDuration);

            $data['from'] = $from;
            $data['to'] = $to;
            $data['client_id'] = auth()->id();
            $data['approval_status_id'] = AppointmentRequestStatus::Pending->value;
            $data['organisation_id'] = auth()->user()->organisation_id;

            AppointmentRequest::create($data);
        });
        $this->successNotificationTitle('Appointment request created successfully');
        $this->icon(PhosphorIcons::CalendarPlus);
        $this->color('success');
        $this->label('New appointment');
    }

    private function calculateAvailableSlots($get): Collection
    {
        $date = Carbon::parse($get('date'))->format('Y-m-d');

        $service = Service::with('users')->find($get('service_id'));

        $slots = $service->users->flatMap(function ($user) use ($date, $service) {
            return collect($user->getAvailableSlots(
                date: $date,
                slotDuration: $service->duration->minute
            ))->map(function ($slot) use ($user) {
                $slot['user'] = $user->only('id', 'full_name');
                return $slot;
            });
        });

        return collect($slots)
            ->filter(fn($slot) => $slot['is_available']);
    }

    public static function getDefaultName(): ?string
    {
        return 'appointment-request';
    }
}
