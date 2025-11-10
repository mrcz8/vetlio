<?php

namespace App\Filament\Portal\Pages;

use App\Filament\Portal\Widgets\ClientStats;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected string $view = 'filament.portal.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getSubheading(): string|Htmlable|null
    {
        return 'Welcome back, ' . auth()->user()->full_name . '.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ClientStats::make()
        ];
    }
}
