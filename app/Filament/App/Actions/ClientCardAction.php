<?php

namespace App\Filament\App\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\Action;

class ClientCardAction
{
    public static function make()
    {
        return Action::make('client-card')
            ->mountUsing(function ($record, $action) {
                if (method_exists($record, 'client')) {
                    $action->record($record->client);
                }
            })
            ->action(function ($action, $record) {
                $action->redirect(ClientResource::getUrl('view', ['record' => $record]));
            })
            ->outlined()
            ->hiddenLabel()
            ->model(Client::class)
            ->tooltip('Client card')
            ->label('Client card')
            ->icon(PhosphorIcons::UserCircle);
    }
}
