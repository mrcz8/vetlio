<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Schemas\ReminderForm;
use App\Filament\App\Tables\RemindersTable;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;

class ClientReminders extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'reminders';

    protected static string|null|\BackedEnum $navigationIcon = Heroicon::Clock;

    protected static ?string $navigationLabel = 'Reminders';

    protected static ?string $title = 'Reminders';

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->reminders_count;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->fillForm(function ($data) {
                $data['user_to_remind_id'] = auth()->id();
                $data['remind_at'] = now()->addDays(1);

                return $data;
            })
        ];
    }

    public function form(Schema $schema): Schema
    {
        return ReminderForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return RemindersTable::configure($table);
    }
}
