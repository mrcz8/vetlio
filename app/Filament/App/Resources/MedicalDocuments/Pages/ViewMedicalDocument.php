<?php

namespace App\Filament\App\Resources\MedicalDocuments\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Actions\PatientCardAction;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewMedicalDocument extends ViewRecord
{
    protected static string $resource = MedicalDocumentResource::class;

    protected static ?string $navigationLabel = 'View';

    public bool $showItemsToPay = true;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-invoice')
                ->visible(fn($record) => !$record->isPaid())
                ->color('success')
                ->icon(Heroicon::Eye)
                ->label('Create Invoice')
                ->action(function ($record, $action) {
                    $recordIds = $record->items->pluck('id')->toArray();

                    $action->redirect(InvoiceResource::getUrl('create', [
                        'medicalDocumentItems' => implode(',', $recordIds),
                        'client' => $this->getRecord()->client_id,
                    ]));
                }),

            Action::make('toggle-items')
                ->outlined(fn() => !$this->showItemsToPay)
                ->hiddenLabel()
                ->icon(PhosphorIcons::Eye)
                ->tooltip('Show billable items')
                ->action(function () {
                    $this->showItemsToPay = !$this->showItemsToPay;
                }),

            Action::make('lock')
                ->icon(Heroicon::LockClosed)
                ->outlined()
                ->color('danger')
                ->tooltip('Lock report')
                ->modalHeading('Lock Medical Report')
                ->modalIcon(Heroicon::LockClosed)
                ->modalDescription('Once locked, this report can no longer be edited. Are you sure you want to continue?')
                ->visible(fn($record) => !$record->locked_at)
                ->hiddenLabel()
                ->requiresConfirmation()
                ->successNotificationTitle('The report has been successfully locked.')
                ->action(function ($record) {
                    $record->update([
                        'locked_at' => now(),
                        'locked_user_id' => auth()->user()->id,
                    ]);
                }),

            ClientCardAction::make(),
            PatientCardAction::make(),

            Action::make('print')
                ->outlined()
                ->hiddenLabel()
                ->icon(Heroicon::Printer),

            EditAction::make()
                ->outlined(),

            DeleteAction::make()
                ->visible(auth()->user()->administrator),
        ];
    }
}
