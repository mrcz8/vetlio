<?php

namespace App\Filament\App\Resources\Reservations\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Models\ReservationReminderDelivery;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

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

    /**
     * @return SimpleAlert
     */
    public static function reservationCanceledAlert(): SimpleAlert
    {
        return SimpleAlert::make('canceled')
            ->danger()
            ->icon(PhosphorIcons::CalendarMinus)
            ->border()
            ->title('Reservation Canceled')
            ->description(function ($record) {
                $cancelReason = $record->cancelReason->name ?? 'No reason provided';

                return "Cancellation reason: $cancelReason";
            })
            ->columnSpanFull();
    }

    /**
     * @return Grid
     */
    public static function mainInformation(): Grid
    {
        return Grid::make(3)
            ->columnSpanFull()
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
                    ->label('Duration'),

                TextEntry::make('service.name')
                    ->icon(PhosphorIcons::Hand)
                    ->state(function ($record) {
                        return $record->service->name . ' (' . Number::currency($record->service->currentPrice->price_with_vat) . ')';
                    })
                    ->label('Service'),

                TextEntry::make('serviceProvider.full_name')
                    ->icon(PhosphorIcons::User)
                    ->label('Veterinarian'),

                TextEntry::make('room.name')
                    ->icon(PhosphorIcons::Bed)
                    ->label('Room'),

                TextEntry::make('note')
                    ->columnSpanFull()
                    ->default('-')
                    ->label('Note')
                    ->icon(PhosphorIcons::Note),
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
                                ->defaultImageUrl('https://www.svgrepo.com/show/420337/animal-avatar-bear.svg')
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
            EditAction::make()
                ->outlined(),
            Action::make('cancel-reservation')
                ->outlined()
                ->label('Cancel Reservation')
                ->color('danger')
                ->icon(PhosphorIcons::CalendarMinus)
        ]);
    }
}
