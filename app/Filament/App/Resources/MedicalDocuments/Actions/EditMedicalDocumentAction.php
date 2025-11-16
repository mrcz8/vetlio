<?php

namespace App\Filament\App\Resources\MedicalDocuments\Actions;

use Filament\Actions\EditAction;

class EditMedicalDocumentAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->outlined();
        $this->visible(function($record) {
            return !$record->locked_at;
        });
    }
}
