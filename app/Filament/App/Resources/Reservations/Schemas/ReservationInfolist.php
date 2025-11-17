<?php

namespace App\Filament\App\Resources\Reservations\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Actions\CancelReservationAction;
use App\Filament\App\Resources\Reservations\Actions\EditAppointmentAction;
use App\Filament\App\Resources\Reservations\Pages\EditReservation;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Number;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                self::headerActions(),
                self::reservationCanceledAlert(),
                self::clientAndPatient(),
                self::mainInformation(),
            ]);
    }


    public static function reservationCanceledAlert(): SimpleAlert
    {
        return SimpleAlert::make('canceled')
            ->danger()
            ->icon(PhosphorIcons::CalendarMinus)
            ->border()
            ->visible(function ($record) {
                return $record->is_canceled;
            })
            ->title('Reservation Canceled')
            ->description(function ($record) {
                $cancelReason = $record->cancelReason->name ?? 'No reason provided';

                return "Cancellation reason: $cancelReason";
            })
            ->columnSpanFull();
    }

    public static function mainInformation(): Tabs
    {
        return Tabs::make()
            ->columnSpanFull()
            ->contained(false)
            ->tabs([
                Tabs\Tab::make('Appointment details')
                    ->icon(PhosphorIcons::Calendar)
                    ->columns(3)
                    ->schema([
                        TextEntry::make('date')
                            ->icon(PhosphorIcons::Calendar)
                            ->date('d.m.Y')
                            ->label('Date'),

                        TextEntry::make('from')
                            ->icon(PhosphorIcons::Clock)
                            ->state(function ($record) {
                                $diff = $record->from->diffInMinutes($record->to);
                                return $record->from->format('H:i') . ' - ' . $record->to->format('H:i') . ' (' . $diff . ' min)';
                            })
                            ->label('When'),

                        TextEntry::make('branch.name')
                            ->label('Location'),

                        TextEntry::make('service.name')
                            ->icon(PhosphorIcons::Hand)
                            ->state(function ($record) {
                                return $record->service->name . ' (' . Number::currency($record->service->currentPrice->price_with_vat) . ')';
                            })
                            ->label('Service'),

                        TextEntry::make('reason_for_coming')
                            ->default('-')
                            ->label('Reason for coming'),

                        TextEntry::make('serviceProvider.full_name')
                            ->icon(PhosphorIcons::User)
                            ->label('Veterinarian'),

                        TextEntry::make('room.name')
                            ->icon(PhosphorIcons::Bed)
                            ->label('Room'),

                        TextEntry::make('status_id')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('user.full_name')
                            ->label('Created by'),

                        TextEntry::make('note')
                            ->columnSpanFull()
                            ->default('-')
                            ->label('Note')
                            ->icon(PhosphorIcons::Note),
                    ]),
                Tabs\Tab::make('Reminders')
                    ->icon(PhosphorIcons::Bell)
                    ->schema([
                        SimpleAlert::make('no-reminders')
                            ->info()
                            ->border()
                            ->title('No reminders set')
                            ->description('This appointment has no reminders set')
                            ->columnSpanFull()
                    ]),

            ]);
    }

    private static function clientAndPatient()
    {
        return Grid::make(2)
            ->columnSpanFull()
            ->schema([
                Fieldset::make('Client')
                    ->schema([
                        Flex::make([
                            ImageEntry::make('client.avatar_url')
                                ->circular()
                                ->defaultImageUrl('https://png.pngtree.com/png-vector/20210604/ourmid/pngtree-gray-avatar-placeholder-png-image_3416697.jpg')
                                ->imageSize(100)
                                ->hiddenLabel(),
                            Grid::make(1)
                                ->gap(false)
                                ->schema([
                                    TextEntry::make('client.full_name')
                                        ->hiddenLabel()
                                        ->size(TextSize::Large),
                                    TextEntry::make('client.email')
                                        ->icon(PhosphorIcons::Envelope)
                                        ->size(TextSize::Small)
                                        ->hiddenLabel(),
                                    TextEntry::make('client.phone')
                                        ->icon(PhosphorIcons::Phone)
                                        ->size(TextSize::Small)
                                        ->hiddenLabel(),
                                ])
                        ])->gap(false)->columnSpanFull(),
                    ]),

                Fieldset::make('Patient')
                    ->schema([
                        Flex::make([
                            ImageEntry::make('patient.avatar_url')
                                ->defaultImageUrl(asset('img/default-patient-profile.jpg'))
                                ->circular()
                                ->imageSize(100)
                                ->hiddenLabel(),
                            Grid::make(1)
                                ->gap(false)
                                ->schema([
                                    TextEntry::make('patient.name')
                                        ->hiddenLabel()
                                        ->size(TextSize::Large),
                                    TextEntry::make('patient.species.name')
                                        ->icon(PhosphorIcons::Dog)
                                        ->size(TextSize::Small)
                                        ->hiddenLabel(),
                                    TextEntry::make('patient.breed.name')
                                        ->icon(PhosphorIcons::Cow)
                                        ->size(TextSize::Small)
                                        ->hiddenLabel(),
                                ])
                        ])->gap(false)->columnSpanFull(),
                    ])
            ]);
    }

    private static function headerActions()
    {
        return Flex::make([
            CancelReservationAction::make(),
            EditAppointmentAction::make(),
            DeleteAction::make(),
        ]);
    }
}
