<?php

namespace App\Services\EmailTags;

use App\Contracts\EmailTagProvider;
use App\Models\Organisation;

class OrganisationTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof Organisation;
    }

    public static function getAvailableTags(): array
    {
        return [
            'organisation.name' => 'Clinic Name',
            'organisation.email' => 'Clinic Email',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'organisation.name' => $model->name,
            'organisation.email' => $model->email ?? '',
        ];
    }
}
