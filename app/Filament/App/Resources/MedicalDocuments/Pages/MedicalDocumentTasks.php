<?php

namespace App\Filament\App\Resources\MedicalDocuments\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\MedicalDocuments\HasHeaderActions;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use App\Filament\App\Resources\Tasks\Schemas\TaskForm;
use App\Filament\App\Resources\Tasks\Tables\TasksTable;
use App\Models\MedicalDocument;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class MedicalDocumentTasks extends ManageRelatedRecords
{
    use HasHeaderActions;

    protected static string $resource = MedicalDocumentResource::class;

    protected static string $relationship = 'tasks';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::TagSimple;

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $title = 'Tasks';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->patient->description;
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->tasks_count;
    }

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return TasksTable::configure($table)
            ->modifyQueryUsing(function ($query) {
                return $query->with('related');
            });
    }
}
