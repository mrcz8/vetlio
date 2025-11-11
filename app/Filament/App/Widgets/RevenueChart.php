<?php

namespace App\Filament\App\Widgets;

use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected ?string $heading = 'Revenue (Last 7 days)';

    protected function getData(): array
    {
        $data = Invoice::query()
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('branch_id', Filament::getTenant()->id)
            ->whereBetween('created_at', [now()->subDays(6), now()])
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (€)',
                    'data' => $data->values(),
                ],
            ],
            'labels' => $data->keys(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
