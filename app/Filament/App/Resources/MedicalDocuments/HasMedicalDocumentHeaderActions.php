<?php

namespace App\Filament\App\Resources\MedicalDocuments;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Actions\PatientCardAction;
use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\MedicalDocuments\Actions\EditMedicalDocumentAction;
use App\Filament\App\Resources\MedicalDocuments\Pages\MedicalDocumentTasks;
use App\Filament\App\Resources\MedicalDocuments\Pages\MedicalDocumentUploadDocuments;
use App\Models\MedicalDocument;
use App\Services\EmailTemplate\EmailTemplateRenderer;
use App\Services\EmailTemplate\EmailTemplateService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;

trait HasMedicalDocumentHeaderActions
{
    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('create-task')
                ->label('Create Task')
                ->icon('heroicon-s-plus')
                ->fillForm(function ($data) {
                    $data['start_at'] = now();
                    $data['related_type'] = MedicalDocument::class;
                    $data['related_id'] = $this->getRecord()->id;

                    return $data;
                })
                ->visible(function ($livewire) {
                    return $livewire instanceof MedicalDocumentTasks;
                }),

            CreateAction::make('create-document')
                ->label('Attach Document')
                ->icon(PhosphorIcons::Paperclip)
                ->visible(function ($livewire) {
                    return $livewire instanceof MedicalDocumentUploadDocuments;
                }),

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

            Action::make('print')
                ->outlined()
                ->hiddenLabel()
                ->icon(Heroicon::Printer),

            SendEmailAction::make()
                ->fillForm(function ($data) {
                    $data['receivers'] = [$this->getRecord()->client->email];

                    $branch = $this->getRecord()->branch;

                    $email = EmailTemplateRenderer::make()
                        ->forBranch($branch->id)
                        ->for(EmailTemplateType::SendMedicalDocument)
                        ->withContext([
                            'branch' => $branch,
                            'client' => $this->getRecord()->client,
                            'patient' => $this->getRecord()->patient,
                            'organisation' => $this->getRecord()->organisation,
                            'medical-document' => $this->getRecord(),
                        ])
                        ->resolve();

                    if($email === null) return $data;

                    $data['subject'] = $email['subject'];
                    $data['body'] = $email['body'];;

                    return $data;
                }),

            ActionGroup::make([
                ClientCardAction::make(),
                PatientCardAction::make(),
                EditMedicalDocumentAction::make(),
                DeleteAction::make()
                    ->visible(auth()->user()->administrator),
            ])->outlined()->label('More'),
        ];
    }
}
