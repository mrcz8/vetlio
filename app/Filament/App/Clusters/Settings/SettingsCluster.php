<?php

namespace App\Filament\App\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog;

    protected static ?string $title = 'Settings';

    protected static ?string $navigationLabel = 'Settings';
}
