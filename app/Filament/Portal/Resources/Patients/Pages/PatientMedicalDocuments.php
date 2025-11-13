<?php

namespace App\Filament\Portal\Resources\Patients\Pages;

use App\Filament\Portal\Resources\Patients\PatientResource;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Filament\Shared\Columns\UpdatedAtColumn;
use App\Models\MedicalDocument;
use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class PatientMedicalDocuments extends Page implements HasTable
{
    use InteractsWithRecord, InteractsWithTable;

    protected static string $resource = PatientResource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $navigationLabel = 'Medical documents';

    protected string $view = 'filament.portal.resources.patients.pages.patient-medical-documents';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->description;
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->medical_documents_count;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Medical documents of ' . $this->getRecord()->name;
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateActions([])
            ->query(MedicalDocument::query()->where('patient_id', $this->record->id))
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->tooltip(function ($record) {
                        return $record->locked_at ? 'The report is locked' : null;
                    })
                    ->icon(function ($record) {
                        return $record->locked_at ? Heroicon::LockClosed : null;
                    })
                    ->iconColor('danger')
                    ->searchable(),

                TextColumn::make('reservation.from')
                    ->sortable()
                    ->dateTime('d.m.Y H:i')
                    ->label('Reservation'),

                TextColumn::make('patient.name')
                    ->label('Patient')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client.full_name')
                    ->label('Client')
                    ->sortable()
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('reason_for_coming')
                    ->label('Reason for visit')
                    ->searchable(),

                TextColumn::make('serviceProvider.name')
                    ->label('Doctor')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('items_sum_total')
                    ->money('EUR', 100)
                    ->label('Total')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->sum('items', 'total'),

                CreatedAtColumn::make('created_at'),
                UpdatedAtColumn::make('updated_at'),
            ]);
    }
}
