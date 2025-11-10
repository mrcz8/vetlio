<?php

namespace App\Filament\Portal\Resources\Patients\Schemas;

use App\Enums\PatientGender;
use App\Models\Breed;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                FileUpload::make('photo')
                    ->label('Photo')
                    ->columnSpanFull()
                    ->alignCenter()
                    ->avatar()
                    ->uploadButtonPosition('left')
                    ->image(),

                TextInput::make('name')
                    ->label('Name')
                    ->required(),

                ToggleButtons::make('gender_id')
                    ->columns(3)
                    ->grouped()
                    ->default(PatientGender::Other)
                    ->options(PatientGender::class)
                    ->label('Gender')
                    ->inline(),

                DatePicker::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date(),

                TextInput::make('color')
                    ->label('Color'),

                Select::make('species_id')
                    ->required()
                    ->relationship('species', 'name')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('breed_id', null);
                    })
                    ->label('Species'),

                Select::make('breed_id')
                    ->options(function (Get $get) {
                        if ($get('species_id') != null) {
                            return Breed::where('species_id', $get('species_id'))->pluck('name', 'id');
                        }
                        return Breed::pluck('name', 'id');
                    })
                    ->label('Breed')
                    ->disabled(fn(Get $get) => $get('species_id') == null)
                    ->required(),

                ToggleButtons::make('dangerous')
                    ->label('Is the patient dangerous?')
                    ->default(false)
                    ->grouped()
                    ->inline()
                    ->live()
                    ->boolean('Yes', 'No')
                    ->colors([
                        1 => 'danger',
                        0 => 'success',
                    ]),

                TextInput::make('dangerous_note')
                    ->visible(fn(Get $get) => $get('dangerous'))
                    ->label('Dangerous (Note)'),
                Textarea::make('remarks')
                    ->columnSpanFull()
                    ->placeholder('Enter remarks or notes about the patient...')
                    ->label('Remarks'),

                Textarea::make('allergies')
                    ->columnSpanFull()
                    ->placeholder('Enter any allergies...')
                    ->label('Allergies'),
            ]);
    }
}
