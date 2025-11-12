<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Schemas;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\BladeFlags;
use App\Enums\Icons\CountryFlags;
use App\Models\EmailTemplate;
use App\Models\Language;
use App\Services\EmailTags\MergeTagResolver;
use App\Services\EmailTemplateMergeTags;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Config;

class EmailTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = Language::all()->pluck('iso_639_1')->toArray();

        $defaultLocale = Config::get('app.locale');

        return $schema
            ->schema([
                SimpleAlert::make('not-active')
                    ->border()
                    ->visible(function (?EmailTemplate $record) {
                        return $record != null && !$record->active;
                    })
                    ->warning()
                    ->description('Email template is not active. It will not be sent until it is activated.')
                    ->columnSpanFull(),

                Section::make()
                    ->compact()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->required(),

                        Select::make('type_id')
                            ->label('Template type')
                            ->options(EmailTemplateType::class)
                            ->disabled()
                            ->required(),
                    ]),

                ...collect($locales)->map(function ($locale) use ($defaultLocale) {
                    return Section::make("Content ({$locale})")
                        ->icon(function() use ($locale) {
                            return CountryFlags::tryFrom('1x1-' . $locale);
                        })
                        ->columnSpanFull()
                        ->statePath('translated')
                        ->compact()
                        ->schema([
                            TextInput::make("subject.{$locale}")
                                ->required($locale === $defaultLocale)
                                ->label("Naslov ({$locale})"),

                            RichEditor::make("body.{$locale}")
                                ->label("HTML content ({$locale})")
                                ->activePanel('mergeTags')
                                ->extraAttributes([
                                    'style' => 'min-height:300px;'
                                ])
                                ->mergeTags(function($get, $record) {
                                    $resolver = (new MergeTagResolver())
                                        ->forEmailTemplate(EmailTemplateType::tryFrom($record->type_id));

                                    return $resolver->getAvailableTags();
                                })
                                ->required($locale === $defaultLocale)
                                ->columnSpanFull(),
                        ])
                        ->collapsible()
                        ->collapsed($locale !== $defaultLocale);
                })->toArray()
            ]);
    }
}
