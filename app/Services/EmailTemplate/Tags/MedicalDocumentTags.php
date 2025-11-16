<?php

namespace App\Services\EmailTemplate\Tags;

use App\Contracts\EmailTagProvider;
use App\Models\MedicalDocument;
use App\Models\Reservation;

class MedicalDocumentTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof MedicalDocument;
    }

    public static function getAvailableTags(): array
    {
        return [
            'medical-document.id' => 'Medical Document ID',
            'medical-document.code' => 'Medical Document Code',
            'medical-document.content' => 'Medical Document Content',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'medical-document.id' => $model->id,
            'medical-document.code' => $model->code,
            'medical-document.content' => $model->content,
        ];
    }
}
