<?php

namespace App\Filament\Portal\Resources\Patients\Pages;

use App\Filament\Portal\Actions\AppointmentRequestAction;
use App\Filament\Portal\Resources\Patients\PatientResource;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class PatientAppointments extends ManageRelatedRecords
{
    protected static string $resource = PatientResource::class;

    protected static string $relationship = 'reservations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected static ?string $navigationLabel = 'Appointments';

    protected static ?string $breadcrumb = 'Appointments';

    public function getTitle(): string|Htmlable
    {
        return 'Appointments of ' . $this->getRecord()->name;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->description;
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->reservations_count;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->emptyStateActions([])
            ->columns([
                TextColumn::make('from')
                    ->sortable()
                    ->date()
                    ->description(fn($record) => $record->from->format('H:i') . ' - ' . $record->to->format('H:i'))
                    ->label('Reservation Time'),

                TextColumn::make('reason_for_coming')
                    ->label('Reason for visit'),

                TextColumn::make('branch.name')
                    ->label('Branch'),

                TextColumn::make('service.name')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->service->duration->format('i') . ' min')
                    ->label('Service'),

                TextColumn::make('serviceProvider.full_name')
                    ->searchable()
                    ->label('Doctor'),

                TextColumn::make('room.name')
                    ->label('Room'),

                TextColumn::make('status_id')
                    ->badge()
                    ->label('Status'),


            ])
            ->headerActions([
                AppointmentRequestAction::make(),
            ]);
    }
}
