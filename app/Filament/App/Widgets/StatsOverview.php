<?php

namespace App\Filament\App\Widgets;

use App\Enums\Icons\PhosphorIcons;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Reservation;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $branchId = Filament::getTenant()->id;

        return [
            Stat::make('Appointments today', Reservation::where('branch_id', $branchId)
                ->whereDate('from', today())
                ->canceled(false)
                ->count())
                ->color('success')
                ->description('Appointments today')
                ->icon(PhosphorIcons::Calendar),

            Stat::make('New patients', Patient::where('organisation_id', $branchId)
                ->whereDate('created_at', today())
                ->count())
                ->description('New patients today')
                ->color('info')
                ->icon(PhosphorIcons::Dog),

            Stat::make('Invoices today (€)', Invoice::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->sum('total'))
                ->color('success')
                ->description('Total revenue today')
                ->icon(PhosphorIcons::Money),

            Stat::make('Canceled', Reservation::where('organisation_id', $branchId)
                ->canceled()
                ->whereDate('from', today())
                ->count())
                ->color('danger')
                ->description('Canceled appointments today')
                ->icon(PhosphorIcons::CalendarMinus),
        ];
    }
}
