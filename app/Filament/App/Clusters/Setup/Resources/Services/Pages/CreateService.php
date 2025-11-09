<?php

namespace App\Filament\App\Clusters\Setup\Resources\Services\Pages;

use App\Filament\App\Clusters\Setup\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected static ?string $title = 'New service';

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
