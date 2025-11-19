<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Tasks\Schemas\TaskForm;
use App\Filament\App\Resources\Tasks\Tables\TasksTable;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ClientTasks extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'tasks';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::TagSimple;

    protected static ?string $navigationLabel = 'Tasks';

    protected static ?string $title = 'Tasks';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->fillForm(function ($data) {
                    $data['start_at'] = now();
                    $data['related_type'] = Client::class;
                    $data['related_id'] = $this->getRecord()->id;

                    return $data;
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return TaskForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }
}
