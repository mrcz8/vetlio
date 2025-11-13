<?php

namespace App\Filament\Portal\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Actions\CancelReservationAction;
use App\Filament\Portal\Actions\AppointmentRequestAction;
use App\Models\Reservation;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class Appointments extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.portal.pages.appointments';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Calendar;

    protected static ?int $navigationSort = 2;

    public function getSubheading(): string|Htmlable|null
    {
        return 'Keep track of your upcoming vet visits and easily book new appointments for your pets.';
    }

    protected function getHeaderActions(): array
    {
        return [
            AppointmentRequestAction::make()
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateActions([
                AppointmentRequestAction::make()
            ])
            ->query(Reservation::query()->where('client_id', auth()->id()))
            ->columns([
                ImageColumn::make('patient.photo')
                    ->label('')
                    ->alignCenter()
                    ->width('50px')
                    ->circular(),

                TextColumn::make('patient.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->patient->breed->name . ', ' . $record->patient->species->name)
                    ->label('Patient'),

                TextColumn::make('from')
                    ->sortable()
                    ->date()
                    ->description(fn($record) => $record->from->format('H:i') . ' - ' . $record->to->format('H:i'))
                    ->label('Reservation Time'),

                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->service->duration->format('i') . ' min')
                    ->label('Service'),

                TextColumn::make('serviceProvider.full_name')
                    ->searchable()
                    ->label('Doctor'),

                TextColumn::make('status_id')
                    ->badge()
                    ->label('Status'),

                TextColumn::make('room.name')
                    ->label('Room'),

                TextColumn::make('service')
                    ->money()
                    ->state('200')
                    ->weight(FontWeight::SemiBold)
                    ->label('Total price'),
            ])->recordActions([
                ViewAction::make(),
                CancelReservationAction::make()
            ]);
    }
}
