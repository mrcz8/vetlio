<?php

namespace App\Services\EmailTags;

use App\Contracts\EmailTagProvider;
use App\Models\Client;

class ClientTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof Client;
    }

    public static function getAvailableTags(): array
    {
        return [
            'client.id' => 'Client ID',
            'client.first_name' => 'Client First Name',
            'client.email'=> 'Client Email',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'client.id' => $model->id,
            'client.name' => $model->name,
            'client.email' => $model->email ?? '',
        ];
    }
}
