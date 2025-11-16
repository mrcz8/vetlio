<?php

namespace App\Filament\App\Resources\MedicalDocuments\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Models\MedicalDocument;
use App\Models\Service;
use App\Models\User;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class MedicalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->disabled(fn(?MedicalDocument $record) => $record?->locked_at)
            ->columns(1)
            ->components([
                SimpleAlert::make('locked')
                    ->danger()
                    ->hiddenOn('create')
                    ->visible(fn(?MedicalDocument $record) => $record?->locked_at)
                    ->border()
                    ->columnSpanFull()
                    ->action(
                        Action::make('unlock')
                            ->action(fn($record) => $record->update([
                                'locked_at' => null,
                                'locked_user_id' => null,
                            ]))
                            ->color('danger')
                            ->icon(Heroicon::LockClosed)
                            ->link()
                            ->requiresConfirmation()
                            ->label('Unlock')
                    )
                    ->description(function ($record) {
                        return new HtmlString(
                            "The document was locked on <b>{$record->locked_at->format('d.m.Y H:i')} ("
                            . " {$record->locked_at->diffForHumans()})</b> by user: "
                            . "<b>{$record->userLocked->full_name}</b>"
                        );
                    }),

                Tabs::make('tabs')
                    ->contained(false)
                    ->tabs([
                        Tabs\Tab::make('General information')
                            ->key('main-info')
                            ->columns(4)
                            ->icon(Heroicon::Document)
                            ->schema([
                                Section::make()
                                    ->compact()
                                    ->columnSpan(3)
                                    ->schema([
                                        TagsInput::make('diagnosis')
                                            ->reorderable()
                                            ->required()
                                            ->suggestions([
                                                'A.00', 'C.00'
                                            ]),
                                        RichEditor::make('content')
                                            ->autofocus()
                                            ->hintActions([
                                                self::getLoadFromTemplateAction(),
                                                self::saveAsTemplateAction(),
                                            ])
                                            ->hiddenLabel()
                                            ->extraInputAttributes([
                                                'style' => 'min-height: 600px;',
                                            ])
                                            ->label('Content')
                                            ->required(),
                                    ]),
                                Section::make()
                                    ->compact()
                                    ->columnSpan(1)
                                    ->schema([
                                        Select::make('service_provider_id')
                                            ->label('Doctor')
                                            ->required()
                                            ->options(User::get()->pluck('full_name', 'id')),

                                        Fieldset::make('Patient')
                                            ->visible(fn($livewire) => $livewire->patient)
                                            ->schema([
                                                Flex::make([
                                                    ImageEntry::make('patient.avatar_url')
                                                        ->defaultImageUrl('https://www.svgrepo.com/show/420337/animal-avatar-bear.svg')
                                                        ->circular()
                                                        ->imageSize(100)
                                                        ->hiddenLabel(),
                                                    Grid::make(1)
                                                        ->gap(false)
                                                        ->schema([
                                                            TextEntry::make('patient.name')
                                                                ->state(fn($livewire) => $livewire?->patient?->name)
                                                                ->live()
                                                                ->hiddenLabel()
                                                                ->size(TextSize::Large),

                                                            TextEntry::make('patient.breed.name')
                                                                ->state(fn($livewire) => $livewire?->patient?->breed?->name)
                                                                ->icon(PhosphorIcons::Dog)
                                                                ->size(TextSize::Small)
                                                                ->hiddenLabel(),

                                                            TextEntry::make('patient.species.name')
                                                                ->state(fn($livewire) => $livewire?->patient?->species?->name)
                                                                ->icon(PhosphorIcons::Cow)
                                                                ->size(TextSize::Small)
                                                                ->hiddenLabel(),
                                                        ]),
                                                ])->gap(false)->columnSpanFull(),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make('Items')
                            ->key('items')
                            ->icon(Heroicon::DocumentText)
                            ->label(fn($get) => 'Items (' . count($get('items') ?? []) . ')')
                            ->schema([
                                Section::make()
                                    ->contained()
                                    ->columns(4)
                                    ->schema([
                                        Select::make('service_id')
                                            ->columnSpan(2)
                                            ->live()
                                            ->hiddenLabel()
                                            ->placeholder('Select a service...')
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $service = Service::find($get('service_id'));
                                                $item = [
                                                    'priceable_id' => $service->id,
                                                    'priceable_type' => Service::class,
                                                    'name' => $service->name,
                                                    'description' => 'Service',
                                                    'quantity' => 1,
                                                    'price' => Number::format($service->currentPrice->price, 2),
                                                    'vat' => 25,
                                                    'discount' => 0,
                                                    'total' => Number::format($service->currentPrice->price_with_vat, 2),
                                                ];

                                                $items = collect($get('items') ?? []);
                                                $items->push($item);
                                                $set('items', $items->toArray());
                                                $set('service_id', null);
                                            })
                                            ->options(Service::whereHas('currentPrice')->get()->pluck('name', 'id')),

                                        SimpleAlert::make('no-items')
                                            ->warning()
                                            ->border()
                                            ->title('No items added')
                                            ->visible(fn($get) => !$get('items'))
                                            ->columnSpanFull(),

                                        ItemsRepeater::make('items')
                                            ->visible(fn($get) => $get('items')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getLoadFromTemplateAction(): Action
    {
        return Action::make('load-from-template')
            ->link()
            ->label('Load from template')
            ->modalIcon(Heroicon::Document)
            ->modalDescription('Select a template to load')
            ->icon(Heroicon::Document)
            ->modalSubmitActionLabel('Add')
            ->schema([
                Select::make('id')->live(),

                Grid::make(2)
                    ->visible(fn($get) => $get('id'))
                    ->schema([
                        TextEntry::make('subject')->label('Title'),
                        TextEntry::make('user')->label('Created by'),
                        TextEntry::make('content')
                            ->columnSpanFull()
                            ->html()
                            ->label('Content'),
                    ]),
            ])
            ->action(function ($data, Set $set) {
                //
            });
    }

    private static function saveAsTemplateAction()
    {
        return Action::make('save-as-template')
            ->link()
            ->label('Save as template')
            ->modalIcon(Heroicon::DocumentMagnifyingGlass)
            ->modalDescription('Enter a template name')
            ->icon(Heroicon::DocumentMagnifyingGlass)
            ->schema([
                TextInput::make('subject')
                    ->label('Title')
                    ->required(),
            ])
            ->action(function ($data, Get $get) {
                //
            });
    }
}
