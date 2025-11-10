<?php

namespace App\Filament\Portal\Pages;

use App\Enums\Icons\PhosphorIcons;
use BackedEnum;
use Filament\Pages\Page;

class Appointments extends Page
{
    protected string $view = 'filament.portal.pages.appointments';

    protected static string | BackedEnum | null $navigationIcon = PhosphorIcons::Calendar;

    protected static ?int $navigationSort = 2;
}
