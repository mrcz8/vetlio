<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages;

use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\EmailTemplateResource;
use App\Models\Language;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected static ?string $title = 'Edit template';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->name;
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        $record->update([
            'name' => $data['name'],
        ]);

        $subjects = $data['translated']['subject'];
        $bodies = $data['translated']['body'];

        foreach ($subjects as $locale => $subject) {
            $record->emailTemplateContents()->updateOrCreate(
                ['language' => $locale],
                [
                    'subject' => $subject ?? '-',
                    'content' => $bodies[$locale] ?? '-',
                ]
            );
        }

        return $record;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $locales = Language::pluck('iso_639_1')->toArray();

        $subjectsFromDb = $this->record->emailTemplateContents->pluck('subject', 'language')->toArray();
        $bodiesFromDb = $this->record->emailTemplateContents->pluck('content', 'language')->toArray();

        $subjects = [];
        $bodies = [];

        foreach ($locales as $locale) {
            $subjects[$locale] = isset($subjectsFromDb[$locale]) && $subjectsFromDb[$locale] !== null
                ? $subjectsFromDb[$locale]
                : '';

            $bodies[$locale] = isset($bodiesFromDb[$locale]) && $bodiesFromDb[$locale] !== null
                ? $bodiesFromDb[$locale]
                : '';
        }

        $data['translated'] = [
            'subject' => $subjects,
            'body' => $bodies,
        ];

        return $data;
    }
}
