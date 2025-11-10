<?php

namespace App\Filament\Portal\Pages;

use App\Enums\Icons\PhosphorIcons;
use BackedEnum;
use Filament\Pages\Page;

class Invoices extends Page
{
    protected string $view = 'filament.portal.pages.invoices';

    protected static string | BackedEnum | null $navigationIcon = PhosphorIcons::Money;

    protected static ?int $navigationSort = 3;
}
