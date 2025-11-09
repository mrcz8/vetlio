<?php

namespace App\Filament\App\Schemas;

use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ReminderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->label('Title')
                    ->maxLength(255),

                DateTimePicker::make('remind_at')
                    ->prefixIcon(Heroicon::Clock)
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->after(now())
                    ->label('Reminder date and time'),

                Select::make('user_to_remind_id')
                    ->options(User::get()->pluck('fullName', 'id'))
                    ->label('For employee')
                    ->required(),

                Textarea::make('description')
                    ->required()
                    ->label('Description'),

                Toggle::make('send_email')
                    ->label('Send email'),
            ]);
    }
}
