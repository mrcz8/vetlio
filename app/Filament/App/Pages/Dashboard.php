<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\AppointmentsTodayWidget;
use App\Filament\App\Widgets\RevenueChart;
use App\Filament\App\Widgets\StatsOverview;
use BackedEnum;
use Filament\Pages\Concerns\HasSubNavigation;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    use HasSubNavigation;

    protected string $view = 'filament.app.pages.dashboard';

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationLabel = 'Dashboard';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Home;

    public function getTitle(): string|Htmlable
    {
        return 'Hi, ' . auth()->user()->first_name;
    }

    public function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class,
            AppointmentsTodayWidget::class,
            //MyTasksWidget::class,
            RevenueChart::class,
            //RecentPatientsWidget::class,
            //NotificationsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'xl' => 2,
            '2xl' => 3,
        ];
    }
}
