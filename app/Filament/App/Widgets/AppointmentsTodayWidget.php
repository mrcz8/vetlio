<?php

namespace App\Filament\App\Widgets;

use App\Models\Reservation;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsTodayWidget extends TableWidget
{

    protected function getTableQuery(): Builder
    {
        return Reservation::query()
            ->canceled(false)
            ->where('branch_id', Filament::getTenant()->id)
            ->whereDate('from', today())
            ->with(['patient', 'client'])
            ->latest()
            ->take(5);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns($this->getTableColumns())
            ->paginated(false);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('client.full_name')
                ->sortable()
                ->label('Client'),

            TextColumn::make('patient.name')
                ->sortable()
                ->description(fn($record) => $record->patient->breed->name . ', ' . $record->patient->species->name)
                ->label('Patient'),

            TextColumn::make('from')
                ->sortable()
                ->date()
                ->description(fn($record) => $record->from->format('H:i') . ' - ' . $record->to->format('H:i'))
                ->label('Reservation Time'),

            TextColumn::make('service.name')
                ->sortable()
                ->description(fn($record) => $record->service->duration->format('i') . ' min')
                ->label('Service'),

            TextColumn::make('serviceProvider.full_name')
                ->label('Doctor'),

        ];
    }
}
