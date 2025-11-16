<?php

namespace App\Filament\App\Resources\Reservations\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\ReservationStatus;
use App\Filament\App\Pages\AppointmentRequests;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use App\Filament\App\Resources\Reservations\Actions\NewAppointmentAction;
use App\Filament\App\Resources\Reservations\ReservationResource;
use App\Services\ReservationService;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class ListReservations extends ListRecords
{
    protected static string $resource = ReservationResource::class;

    public ?Collection $appointmentRequests = null;

    public function getTitle(): string|Htmlable
    {
        return 'Waiting room';
    }

    public function mount(): void
    {
        parent::mount();

        $this->appointmentRequests = Filament::getTenant()->appointmentRequests()->where('approval_at', null)->get();
    }

    public function appointmentRequestsAction(): Action
    {
        return Action::make('appointment-requests')
            ->modalIcon(PhosphorIcons::UserPlus)
            ->modalDescription(function () {
                return 'You have ' . count($this->appointmentRequests) . ' appointment requests';
            })
            ->url(function () {
                return count($this->appointmentRequests) < 1 ? AppointmentRequests::getUrl() : null;
            })
            ->hiddenLabel()
            ->tooltip('Appointment requests')
            ->badge(function () {
                return count($this->appointmentRequests);
            })
            ->modalSubmitAction(false)
            ->button()
            ->slideOver()
            ->record(Filament::getTenant())
            ->schema([
                Action::make('view-all')
                    ->url(AppointmentRequests::getUrl())
                    ->icon(PhosphorIcons::CalendarPlus)
                    ->link()
                    ->label('View all request')
                    ->extraAttributes(['class' => 'text-right']),
                SimpleAlert::make('no-requests')
                    ->success()
                    ->border()
                    ->visible(fn() => count($this->appointmentRequests) < 1)
                    ->icon(PhosphorIcons::CheckCircleBold)
                    ->columnSpanFull()
                    ->title('No requests available'),
                RepeatableEntry::make('appointmentRequests')
                    ->hiddenLabel()
                    ->contained(false)
                    ->getStateUsing(function () {
                        return $this->appointmentRequests;
                    })
                    ->visible(fn() => $this->appointmentRequests)
                    ->schema([
                        Section::make(function ($record) {
                            return new HtmlString("<small>Request from {$record->client->full_name}</small>");
                        })
                            ->compact()
                            ->icon(PhosphorIcons::UserPlus)
                            ->columns(3)
                            ->headerActions([
                                Action::make('approve')
                                    ->label('Approve')
                                    ->link()
                                    ->requiresConfirmation()
                                    ->icon(PhosphorIcons::CheckCircleBold)
                                    ->successNotificationTitle('The appointment request has been approved')
                                    ->action(function ($record, $component, Component $livewire, $get) {
                                        app(ReservationService::class)->approveRequest($record);
                                        $livewire->appointmentRequests = $livewire->appointmentRequests->reject(fn($r) => $r->id === $record->id);
                                    }),
                                Action::make('deny')
                                    ->label('Deny')
                                    ->link()
                                    ->color('danger')
                                    ->icon(PhosphorIcons::XCircleBold)
                                    ->successNotificationTitle('The appointment request has been denied')
                                    ->action(function ($record, $livewire) {
                                        app(ReservationService::class)->denyRequest($record);

                                        $livewire->appointmentRequests = $livewire->appointmentRequests->reject(fn($r) => $r->id === $record->id);
                                    })
                            ])
                            ->schema([
                                TextEntry::make('client.full_name')
                                    ->label('Client'),
                                TextEntry::make('client.email')
                                    ->label('Email'),
                                TextEntry::make('client.phone')
                                    ->label('Phone')
                                    ->default('-'),
                                TextEntry::make('patient.name')
                                    ->label('Patient'),
                                TextEntry::make('from')
                                    ->label('Date')
                                    ->dateTime(),
                                TextEntry::make('service.name')
                                    ->label('Service'),

                            ])
                    ])
            ])
            ->badgeColor('success')
            ->icon(PhosphorIcons::UserPlus);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->appointmentRequestsAction(),

            Action::make('new-medical-document')
                ->icon(PhosphorIcons::FilePlus)
                ->label('New Medical Record')
                ->outlined()
                ->url(fn() => MedicalDocumentResource::getUrl('create')),

            NewAppointmentAction::make(),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 1;
    }

    public function getTabs(): array
    {
        $room = $this->getTable()->getFilter('room_id')?->getState()['value'] ?? null;
        $serviceProvider = $this->getTable()->getFilter('service_provider_id')?->getState()['value'] ?? null;
        $from = $this->getTable()->getFilter('date')?->getState()['from'] ?? now()->startOfDay();
        $to = $this->getTable()->getFilter('date')?->getState()['to'] ?? now()->endOfDay();

        $baseQuery = $this->getModel()::query()
            ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            ->when($room, fn($q) => $q->where('room_id', $room))
            ->when($serviceProvider, fn($q) => $q->where('service_provider_id', $serviceProvider));

        $counts = $baseQuery->clone()
            ->select('status_id', DB::raw('COUNT(*) as total'))
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $tabs = [];

        foreach (ReservationStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make($status->getLabel())
                ->badge($counts[$status->value] ?? 0)
                ->modifyQueryUsing(function (Builder $query) use ($status, $serviceProvider, $room, $from, $to) {
                    return $query
                        ->where('status_id', $status->value)
                        ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                        ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                        ->when($room, fn($q) => $q->where('room_id', $room))
                        ->when($serviceProvider, fn($q) => $q->where('service_provider_id', $serviceProvider));
                });
        }

        return $tabs;
    }

}
