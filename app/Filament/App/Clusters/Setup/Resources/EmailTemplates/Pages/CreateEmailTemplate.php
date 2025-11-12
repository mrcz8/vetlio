<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages;

use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\EmailTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;
}
