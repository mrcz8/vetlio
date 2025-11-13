<?php

namespace App\Filament\App\Clusters\Setup\Resources\CancelReasons\Pages;

use App\Filament\App\Clusters\Setup\Resources\CancelReasons\CancelReasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCancelReasons extends ManageRecords
{
    protected static string $resource = CancelReasonResource::class;

    protected static ?string $title = 'Cancel reasons';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
