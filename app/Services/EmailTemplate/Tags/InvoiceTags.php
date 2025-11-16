<?php

namespace App\Services\EmailTemplate\Tags;

use App\Contracts\EmailTagProvider;
use App\Models\Invoice;
use App\Models\Reservation;

class InvoiceTags implements EmailTagProvider
{
    public function supports(mixed $model): bool
    {
        return $model instanceof Invoice;
    }

    public static function getAvailableTags(): array
    {
        return [
            'invoice.id' => 'Invoice ID',
            'invoice.code' => 'Invoice Code',
        ];
    }

    public function resolve(mixed $model): array
    {
        return [
            'invoice.id' => $model->id,
            'invoice.code' => $model->code,
        ];
    }
}
