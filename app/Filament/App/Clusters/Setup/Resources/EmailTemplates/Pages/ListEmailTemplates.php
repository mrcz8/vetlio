<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages;

use App\Enums\EmailTemplateType;
use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\EmailTemplate;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('new-template')
                ->modalWidth(Width::Large)
                ->label('New template')
                ->model(EmailTemplate::class)
                ->modalHeading('New template')
                ->modalDescription('Select template type to create')
                ->modalIcon(Heroicon::OutlinedAtSymbol)
                ->slideOver(false)
                ->schema([
                    TextInput::make('name')
                        ->label('Name')
                        ->required(),

                    Select::make('type_id')
                        ->options(function () {
                            $existingTypeIds = EmailTemplate::pluck('type_id')->toArray();

                            return collect(EmailTemplateType::cases())
                                ->reject(fn($type) => in_array($type->value, $existingTypeIds))
                                ->groupBy(fn($type) => $type->group()['label'])
                                ->mapWithKeys(function ($group, $groupLabel) {
                                    $items = $group->mapWithKeys(fn($type) => [$type->value => $type->getLabel()])->toArray();
                                    return $items ? [$groupLabel => $items] : [];
                                })
                                ->toArray();
                        })
                        ->required()
                        ->label('Vrsta')
                ])->action(function ($data) {
                    $groupId = EmailTemplateType::from($data['type_id'])->groupId();

                    EmailTemplate::create([
                        'name' => $data['name'],
                        'type_id' => $data['type_id'],
                        'group_id' => $groupId,
                        'active' => false,
                    ]);
                })->successNotificationTitle('Email template created successfully.'),
        ];
    }
}
