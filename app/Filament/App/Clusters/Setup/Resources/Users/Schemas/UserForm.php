<?php

namespace App\Filament\App\Clusters\Setup\Resources\Users\Schemas;

use App\Models\Branch;
use Awcodes\Palette\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('First Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->label('Last Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('titule')
                    ->label('Title / Signature')
                    ->columnSpanFull(),

                ToggleButtons::make('gender_id')
                    ->label('Gender')
                    ->inline()
                    ->default(3)
                    ->options([
                        1 => 'Male',
                        2 => 'Female',
                        3 => 'Unspecified',
                    ]),

                TextInput::make('oib')
                    ->label('OIB (Tax ID)')
                    ->unique('users', 'oib', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                    })
                    ->validationMessages([
                        'unique' => 'This OIB is already in use.',
                    ])
                    ->live(true)
                    ->minLength(11)
                    ->maxLength(11),

                DatePicker::make('date_of_birth')
                    ->label('Date of Birth'),

                TextInput::make('name')
                    ->label('Username')
                    ->unique('users', 'name', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                    })
                    ->validationMessages([
                        'unique' => 'This username is already taken.',
                    ])
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->prefixIcon('heroicon-o-at-symbol')
                    ->unique('users', 'email', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                    })
                    ->validationMessages([
                        'unique' => 'This email address is already registered.',
                    ])
                    ->required()
                    ->maxLength(255),

                Select::make('branches')
                    ->label('Branches')
                    ->relationship('branches', 'name')
                    ->multiple()
                    ->preload()
                    ->validationMessages([
                        'required' => 'At least one branch must be selected.',
                    ])
                    ->afterStateUpdated(function (Select $component) {
                        $select = $component->getContainer()->getComponent('primary_branch_id');
                        $select->state(array_key_first($select->getOptions()));
                    })
                    ->live()
                    ->required(),

                Select::make('primary_branch_id')
                    ->label('Primary Branch')
                    ->key('primary_branch_id')
                    ->validationMessages([
                        'required' => 'Primary branch is required.',
                    ])
                    ->extraInputAttributes(['wire:key' => Str::random(10)])
                    ->disabled(function (Get $get) {
                        return collect($get('branches'))->isEmpty();
                    })
                    ->options(function (Get $get, $operation) {
                        if ($get('branches')) {
                            $branchIds = collect($get('branches'))->map(fn($branch) => (int) $branch);
                            return Branch::whereIn('id', $branchIds->toArray())->pluck('name', 'id');
                        }

                        return Branch::pluck('name', 'id');
                    })
                    ->native(false)
                    ->required(),

                ColorPicker::make('color')
                    ->label('Color')
                    ->required(fn(Get $get) => $get('service_provider')),

                Grid::make(3)
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('active')
                            ->default(true)
                            ->inline(false)
                            ->label('Active Employee'),

                        Toggle::make('service_provider')
                            ->default(false)
                            ->live()
                            ->onColor('success')
                            ->inline(false)
                            ->label('Veterinarian'),

                        Toggle::make('administrator')
                            ->default(false)
                            ->onColor('success')
                            ->inline(false)
                            ->disabled(fn() => ! auth()->user()->administrator)
                            ->label('Administrator'),

                        Toggle::make('fiscalization_enabled')
                            ->default(false)
                            ->inline(false)
                            ->label('Fiscalization Enabled')
                            ->disabled(fn($get) => ! $get('oib')),
                    ]),

                FileUpload::make('signature_path')
                    ->columnSpanFull()
                    ->hint('Upload a signature image to be displayed on medical documents.')
                    ->label('Signature'),
            ]);
    }
}
