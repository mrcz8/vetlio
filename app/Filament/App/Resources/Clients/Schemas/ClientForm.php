<?php

namespace App\Filament\App\Resources\Clients\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Models\Language;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->alignCenter()
                    ->avatar()
                    ->columnSpanFull()
                    ->label('Profile picture'),

                Tabs::make()
                    ->columnSpanFull()
                    ->contained(false)
                    ->tabs([
                        Tabs\Tab::make('Basic information')
                            ->columns(2)
                            ->icon(Heroicon::UserCircle)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('First name')
                                    ->required(),

                                TextInput::make('last_name')
                                    ->required()
                                    ->label('Last name'),

                                ToggleButtons::make('gender_id')
                                    ->label('Gender')
                                    ->inline()
                                    ->default(3)
                                    ->icons([
                                        1 => PhosphorIcons::GenderMale,
                                        2 => PhosphorIcons::GenderFemale,
                                        3 => PhosphorIcons::GenderIntersex,
                                    ])
                                    ->grouped()
                                    ->options([
                                        1 => 'Male',
                                        2 => 'Female',
                                        3 => 'Unspecified',
                                    ]),

                                DatePicker::make('date_of_birth')
                                    ->before(now())
                                    ->label('Date of birth'),

                                TextInput::make('oib')
                                    ->label('OIB')
                                    ->unique('clients', 'oib', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                                    })
                                    ->validationMessages([
                                        'unique' => 'OIB is already in use.',
                                    ])
                                    ->numeric()
                                    ->maxLength(11),

                                Select::make('language_id')
                                    ->label('Language')
                                    ->default(auth()->user()->organisation->language_id)
                                    ->required()
                                    ->options(Language::get()->pluck('name_native', 'id'))
                                    ->prefixIcon(Heroicon::Flag),

                                Select::make('how_did_you_hear')
                                    ->prefixIcon(PhosphorIcons::FacebookLogo)
                                    ->label('How did you hear about us?')
                                    ->options([
                                        'facebook' => 'Facebook',
                                        'instagram' => 'Instagram',
                                    ]),

                                SpatieTagsInput::make('tags')
                                    ->label('Tags'),
                            ]),

                        Tabs\Tab::make('Address')
                            ->columns(2)
                            ->icon(Heroicon::Map)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Address'),

                                TextInput::make('city')
                                    ->label('City'),

                                TextInput::make('zip_code')
                                    ->label('Postal code'),

                                Select::make('country_id')
                                    ->relationship('country', 'name_native')
                                    ->required()
                                    ->default(auth()->user()->organisation->country_id)
                                    ->label('Country'),
                            ]),

                        Tabs\Tab::make('Contact')
                            ->columns(2)
                            ->icon(Heroicon::Phone)
                            ->schema([
                                TextInput::make('email')
                                    ->email()
                                    ->unique('clients', 'email', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                                    })
                                    ->validationMessages([
                                        'unique' => 'Email address is already in use.',
                                    ])
                                    ->prefixIcon('heroicon-o-at-symbol')
                                    ->label('Email'),

                                TextInput::make('phone')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->unique('clients', 'phone', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                                        return $rule->where('organisation_id', auth()->user()->organisation_id);
                                    })
                                    ->validationMessages([
                                        'unique' => 'Phone number is already in use.',
                                    ])
                                    ->label('Phone'),
                            ]),
                    ]),
            ]);
    }
}
