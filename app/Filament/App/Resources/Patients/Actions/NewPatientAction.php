<?php

namespace App\Filament\App\Resources\Patients\Actions;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Patients\Schemas\PatientForm;
use App\Models\Patient;
use Filament\Actions\Action;

class NewPatientAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->model(Patient::class);
        $this->modalHeading('New patient');
        $this->modalDescription('Create a new patient');
        $this->modalIcon(PhosphorIcons::Dog);
        $this->icon(PhosphorIcons::Dog);
        $this->label('New patient');
        $this->successNotificationTitle('Patient created successfully');
        $this->schema(function ($schema) {
            return PatientForm::configure($schema)
                ->model(Patient::class)
                ->columns(2);
        });
        $this->action(function ($data, $action) {
            return Patient::create($data);
        });
    }

    public static function getDefaultName(): ?string
    {
        return 'create-patient-action';
    }
}
