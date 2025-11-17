<?php

namespace App\Observers;

use Zap\Facades\Zap;

class BranchObserver
{
    public function updated($model)
    {
        $this->syncAvailability($model, $model->work_schedule);
    }

    public function deleted($model): void
    {
        $model->availabilitySchedules()->delete();
    }

    public function syncAvailability($model, array $formData, ?int $year = null): void
    {
        $year ??= now()->year;

        $model->availabilitySchedules()
            ->whereYear('start_date', $year)
            ->delete();

        $days = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        foreach ($days as $day) {
            if (!array_key_exists($day, $formData)) {
                continue;
            }

            $periods = collect($formData[$day] ?? [])
                ->filter(function ($period) {
                    return !empty($period['from']) && !empty($period['to']);
                })
                ->values();

            if ($periods->isEmpty()) {
                continue;
            }

            $builder = Zap::for($model)
                ->named("Availability {$day} {$year}")
                ->availability()
                ->forYear($year)
                ->withMetadata([
                    'branch_id' => $model->id,
                ])
                ->weekly([$day]);

            foreach ($periods as $period) {
                $builder->addPeriod($period['from'], $period['to']);
            }

            $builder->save();
        }
    }
}
