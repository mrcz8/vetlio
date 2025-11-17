<?php

namespace App\Filament\App\Resources\Reservations\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\Schemas\ClientForm;
use App\Models\Client;
use App\Models\Patient;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SimpleAlert::make('no-availability')
                    ->warning()
                    ->border()
                    ->hidden(fn($get) => !$get('availability_conflicts'))
                    ->columnSpanFull()
                    ->title('Warning!')
                    ->description('The selected time is not available for the selected veterinarian and room.'),

                Select::make('client_id')
                    ->label('Client')
                    ->options(Client::pluck('first_name', 'id'))
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->full_name)
                    ->createOptionForm(fn($schema) => ClientForm::configure($schema))
                    ->createOptionUsing(fn($data) => Client::create($data)->getKey())
                    ->required()
                    ->prefixIcon(PhosphorIcons::User)
                    ->live(true)
                    ->afterStateUpdated(fn($state, $get, $set) => $set('patient_id', null)),

                Select::make('patient_id')
                    ->prefixIcon(PhosphorIcons::Dog)
                    ->label('Patient')
                    ->required()
                    ->live(true)
                    ->disabled(fn($get) => !$get('client_id'))
                    ->options(function (Get $get) {
                        $patients = Patient::query();
                        if ($get('client_id')) {
                            $patients->where('client_id', $get('client_id'));
                        }

                        return $patients->pluck('name', 'id');
                    }),

                Select::make('service_id')
                    ->label('Service')
                    ->live(true)
                    ->required()
                    ->options(function (Get $get) {
                        $services = Service::whereHas('currentPrice');
                        if ($get('service_provider_id')) {
                            $services->whereHas('users', function ($query) use ($get) {
                                $query->where('user_id', $get('service_provider_id'));
                            });
                        }
                        return $services->pluck('name', 'id');
                    })
                    ->afterStateUpdated(function ($state, $get, $set) {
                        $startTime = $get('from');
                        if ($startTime && $state) {
                            $totalMinutes = Service::find($state)->duration->minute;
                            $endTime = Carbon::parse($startTime)->addMinutes($totalMinutes);
                            $set('to', $endTime);
                        }
                    }),

                Select::make('service_provider_id')
                    ->label('Veterinarian')
                    ->disabled(fn($get) => !$get('service_id'))
                    ->required()
                    ->options(function (Get $get) {
                        $users = User::query();
                        if ($get('service_id')) {
                            $users->whereHas('services', function ($query) use ($get) {
                                $query->where('user_id', $get('user_id'));
                            });
                        }
                        return $users->pluck('first_name', 'id');
                    })
                    ->prefixIcon(PhosphorIcons::UserPlus)
                    ->live(true)
                    ->options(User::whereServiceProvider(true)->pluck('first_name', 'id'))
                    ->afterStateUpdated(fn($state, $get, $set) => self::checkAvailability($get, $set)),

                Select::make('room_id')
                    ->required()
                    ->prefixIcon(PhosphorIcons::Bed)
                    ->disabled(fn($get) => !$get('service_id'))
                    ->options(function (Get $get) {
                        $rooms = Room::query();
                        if ($get('service_id')) {
                            $rooms->whereHas('services', function ($query) use ($get) {
                                $query->where('service_id', $get('service_id'));
                            });
                        }
                        return $rooms->pluck('name', 'id');
                    })
                    ->label('Room')
                    ->live(true)
                    ->afterStateUpdated(fn($state, $get, $set) => self::checkAvailability($get, $set)),

                Flex::make([
                    DateTimePicker::make('from')
                        ->live(true)
                        ->required()
                        ->label('Start time')
                        ->seconds(false)
                        ->afterStateUpdated(function ($state, $get, $set) {
                            if ($get('service_id') == null) return;

                            $totalMinutes = Service::find($get('service_id'))->duration->minute;
                            $set('to', Carbon::parse($state)->addMinutes($totalMinutes));

                            self::checkAvailability($get, $set);

                            $reminders = $get('reservationReminders');
                            if (!$reminders) return;

                            foreach ($reminders as $index => $item) {
                                $offsetAmount = $item['offset_amount'] ?? null;
                                $offsetUnit = $item['offset_unit'] ?? null;

                                if (!$offsetAmount || !$offsetUnit) continue;

                                $scheduled = self::calculateSendingTime($state, $offsetAmount, $offsetUnit);
                                $set("reservationReminders.{$index}.scheduled_at", $scheduled);
                            }
                        }),

                    DateTimePicker::make('to')
                        ->label('End time')
                        ->readOnly()
                        ->required()
                        ->seconds(false),
                ]),

                Textarea::make('note')
                    ->columnSpanFull()
                    ->label('Note'),


                Hidden::make('availability_conflicts')
                    ->columnSpanFull()
                    ->label('Availability conflicts')
                    ->disabled(),

                Tabs::make()
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tabs\Tab::make('Reminders')
                            ->badge(fn(Get $get) => count($get('reservationReminders') ?? []))
                            ->icon(PhosphorIcons::Bell)
                            ->schema([
                                Repeater::make('reservationReminders')
                                    ->columns(7)
                                    ->columnSpanFull()
                                    ->live(true)
                                    ->relationship()
                                    ->reorderable(false)
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        if (!$state) return;

                                        foreach ($state as $index => $item) {
                                            $offsetAmount = $item['offset_amount'] ?? null;
                                            $offsetUnit = $item['offset_unit'] ?? null;

                                            if (!$offsetAmount || !$offsetUnit) continue;

                                            $scheduled = self::calculateSendingTime($get('from'), $offsetAmount, $offsetUnit);

                                            $set("{$index}.scheduled_at", $scheduled);
                                        }
                                    })
                                    ->maxItems(3)
                                    ->hint('You can define up to 3 reminders for the client.')
                                    ->label('Client reminders')
                                    ->addActionLabel('Add reminder')
                                    ->schema([
                                        TextInput::make('offset_amount')
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $scheduledAt = self::calculateSendingTime($get('../../from'), $state, $get('offset_unit'));
                                                $set('scheduled_at', $scheduledAt);
                                            })
                                            ->default(2)
                                            ->columnSpan(1)
                                            ->required()
                                            ->live(true)
                                            ->minValue(1)
                                            ->inputMode('numeric')
                                            ->integer()
                                            ->label('Offset amount'),

                                        Select::make('offset_unit')
                                            ->default(3)
                                            ->required()
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $scheduledAt = self::calculateSendingTime($get('../../from'), $get('offset_unit'), $state);
                                                $set('scheduled_at', $scheduledAt);
                                            })
                                            ->prefixIcon(PhosphorIcons::Clock)
                                            ->label('Time unit')
                                            ->columnSpan(2)
                                            ->live(true)
                                            ->options([
                                                1 => 'Minutes',
                                                2 => 'Hours',
                                                3 => 'Days',
                                                4 => 'Weeks'
                                            ]),
                                        Select::make('channels')
                                            ->minItems(1)
                                            ->columnSpan(2)
                                            ->default(['email'])
                                            ->multiple()
                                            ->label('Notification channels')
                                            ->options([
                                                'email' => 'Email',
                                                'sms' => 'SMS',
                                            ]),

                                        DateTimePicker::make('scheduled_at')
                                            ->columnSpan(2)
                                            ->required()
                                            ->after('from', true)
                                            ->seconds(false)
                                            ->date()
                                            ->readOnly()
                                            ->validationMessages([
                                                'after' => 'The reminder time must be after the reservation start.',
                                            ])
                                            ->prefixIcon(PhosphorIcons::Bell)
                                            ->label('Scheduled at')
                                    ])
                            ])
                    ])
            ]);
    }

    public static function checkAvailability($get, $set)
    {
        $start = $get('from');
        $end = $get('to');
        $userId = $get('service_provider_id');
        $roomId = $get('room_id');

        $conflicts = [];

        if ($start && $end) {
            $date = Carbon::parse($start)->format('Y-m-d');
            $start = Carbon::parse($start)->format('H:i');
            $end = Carbon::parse($end)->format('H:i');

            $branch = Filament::getTenant();

            if (!$branch->getBookableSlots($date)) {
                $conflicts[] = 'No schedule is set for this branch.';
            }

            if (!Filament::getTenant()->isAvailableAt($date, $start, $end)) {
                $conflicts[] = 'The selected time is not available for branch.';
            }

            $doctor = User::find($userId);
            if ($doctor && !$doctor->isAvailableAt($date, $start, $end)) {
                $conflicts[] = 'The veterinarian is not available during the selected time.';
            }

            $room = Room::find($roomId);
            if ($room && !$room->isAvailableAt($date, $start, $end)) {
                $conflicts[] = 'The room is occupied during the selected time.';
            }
        }

        $set('availability_conflicts', implode(PHP_EOL, $conflicts));
    }

    private static function calculateSendingTime($from, $offsetAmount, $offsetUnit): ?Carbon
    {
        if (!$from || !$offsetAmount || !$offsetUnit) return null;

        $scheduledAt = Carbon::parse($from);

        switch ($offsetUnit) {
            case 1:
                $scheduledAt->subMinutes($offsetAmount);
                break;
            case 2:
                $scheduledAt->subHours($offsetAmount);
                break;
            case 3:
                $scheduledAt->subDays($offsetAmount);
                break;
            case 4:
                $scheduledAt->subWeeks($offsetAmount);
                break;
        }

        return $scheduledAt;
    }
}
