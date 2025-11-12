<?php

namespace App\Contracts;

interface EmailTagProvider
{
    public function supports(mixed $model): bool;

    public static function getAvailableTags(): array;

    public function resolve(mixed $model): array;
}
