<?php

namespace App\Services\EmailTags;

use App\Enums\EmailTemplateType;

class EmailTemplateTypeMap
{
    public static function getProvidersForType(int $typeId): array
    {
        return match ($typeId) {
            EmailTemplateType::CancelAppointment->value, EmailTemplateType::NewAppointment->value => [
                OrganisationTags::class,
                ClientTags::class,
                BranchTags::class,
                AppointmentTags::class
            ],
            default => [],
        };
    }
}
