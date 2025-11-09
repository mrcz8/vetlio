<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\CalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Calendar extends Page
{
    protected string $view = 'filament.app.pages.calendar';

    protected static bool $shouldRegisterNavigation = true;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Calendar;

    protected static ?string $title = 'Calendar';

    protected function getHeaderWidgets(): array
    {
        return [
            CalendarWidget::make()
        ];
    }
}
