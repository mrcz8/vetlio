<?php

namespace App\Filament\App\Resources\MedicalDocuments\Pages;

use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use App\Models\Client;
use App\Models\Patient;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Locked;

class EditMedicalDocument extends EditRecord
{
    protected static string $resource = MedicalDocumentResource::class;

    #[Locked]
    public ?Client $client = null;

    #[Locked]
    public ?Patient $patient = null;

    public function getSubNavigation(): array
    {
        return [];
    }

    public function mount($record): void
    {
        parent::mount($record);

        $this->client = $this->getRecord()->client;
        $this->patient = $this->getRecord()->patient;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->patient?->description ?? null;
    }

    public function beforeSave(): void
    {
        if (empty($this->form->getStateOnly(['items']))) {
            Notification::make()
                ->danger()
                ->title('No items were added.')
                ->send();
            $this->halt();
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['items'] = $this->getRecord()->items()->get()->toArray();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        $record->items()->delete();

        $record->items()->createMany($data['items']);

        return $record;
    }
}
