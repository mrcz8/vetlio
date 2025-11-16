<?php

namespace App\Filament\App\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Patients\PatientResource;
use Filament\Actions\Action;

class PatientCardAction
{
    public static function make()
    {
        return Action::make('patient-card')
            ->mountUsing(function ($record, $action) {
                if (method_exists($record, 'patient')) {
                    $action->record($record->patient);
                }
            })
            ->action(function ($action, $record) {
                $action->redirect(PatientResource::getUrl('view', ['record' => $record]));
            })
            ->hiddenLabel()
            ->outlined()
            ->tooltip('Patient card')
            ->icon(PhosphorIcons::Dog);
    }
}
