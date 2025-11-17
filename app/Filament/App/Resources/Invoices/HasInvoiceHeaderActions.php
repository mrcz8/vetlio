<?php

namespace App\Filament\App\Resources\Invoices;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Invoices\Pages\InvoiceNotes;
use App\Filament\App\Resources\Invoices\Pages\InvoiceReminders;
use App\Filament\App\Resources\Invoices\Pages\InvoiceTasks;
use App\Filament\App\Resources\Payments\Schemas\PaymentForm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\EmailTemplate\EmailTemplateRenderer;
use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Field;
use Filament\Support\Icons\Heroicon;

trait HasInvoiceHeaderActions
{
    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('create-note')
                ->label('Add note')
                ->visible(fn($livewire) => $livewire instanceof InvoiceNotes),

            CreateAction::make('create-reminder')
                ->label('Create reminder')
                ->visible(fn($livewire) => $livewire instanceof InvoiceReminders)
                ->fillForm(function ($data) {
                    $data['user_to_remind_id'] = auth()->id();
                    $data['remind_at'] = now()->addDays(1);

                    return $data;
                }),

            CreateAction::make('create-task')
                ->label('Create task')
                ->visible(fn($livewire) => $livewire instanceof InvoiceTasks)
                ->fillForm(function ($data) {
                    $data['start_at'] = now();
                    $data['related_type'] = Invoice::class;
                    $data['related_id'] = $this->getRecord()->id;

                    return $data;
                }),

            Action::make('cancel')
                ->hidden(fn($record) => $record->storno_of_id != null)
                ->label('Cancel invoice')
                ->requiresConfirmation()
                ->icon(PhosphorIcons::Invoice)
                ->color('danger')
                ->successNotificationTitle('The invoice was successfully canceled')
                ->successRedirectUrl(fn($record) => InvoiceResource::getUrl('view', ['record' => $record->canceledInvoice]))
                ->action(fn($record, Action $action) => app(InvoiceService::class)->cancelInvoice($record)),

            Action::make('createPayment')
                ->label('Add payment')
                ->color('success')
                ->hidden(function (Invoice $record) {
                    return $record->payed || $record->storno_of_id != null;
                })
                ->icon(PhosphorIcons::CreditCard)
                ->modalIcon(PhosphorIcons::CreditCard)
                ->modalHeading('Payment for invoice')
                ->model(Payment::class)
                ->schema(function ($schema) {
                    $form = PaymentForm::configure($schema);
                    $form->columns(2);

                    collect($form->getFlatComponents())->each(function (Field $component) {
                        if (in_array($component->getName(), ['client_id', 'payment_method_id', 'branch_id'])) $component->disabled();
                    });

                    return $form;
                })
                ->fillForm(function ($data) {
                    $data['payment_at'] = now();
                    $data['client_id'] = $this->getRecord()->client_id;
                    $data['branch_id'] = $this->getRecord()->branch_id;
                    $data['amount'] = $this->getRecord()->total;
                    $data['payment_method_id'] = PaymentMethod::BANK;
                    $data['note'] = 'Payment for invoice: ' . $this->getRecord()->code;
                    return $data;
                })
                ->successNotificationTitle('Payment added successfully')
                ->action(function ($record, $data) {
                    $data['payment_method_id'] = PaymentMethod::BANK;
                    $data['branch_id'] = $record->branch_id;
                    $data['client_id'] = $record->client_id;

                    $record->payments()->create($data);
                })
                ->visible(fn($record) => !$record->payed),

            ActionGroup::make([
                Action::make('print')
                    ->label('Print')
                    ->outlined()
                    ->url(fn(Invoice $record) => route('print.invoices.inline', ['record' => $record]))
                    ->icon(PhosphorIcons::Printer)
                    ->openUrlInNewTab(),

                Action::make('pdf')
                    ->label('Open PDF')
                    ->outlined()
                    ->url(fn(Invoice $record) => route('print.invoices.download', ['record' => $record]))
                    ->icon(PhosphorIcons::FilePdfFill)
                    ->openUrlInNewTab(),
            ])
                ->hiddenLabel()
                ->icon(Heroicon::Printer)
                ->button()
                ->outlined(),

            SendEmailAction::make()
                ->fillForm(function ($data) {
                    $data['receivers'] = [$this->getRecord()->client->email];

                    $branch = $this->getRecord()->branch;

                    $email = EmailTemplateRenderer::make()
                        ->forBranch($branch->id)
                        ->for(EmailTemplateType::SendInvoice)
                        ->withContext([
                            'branch' => $branch,
                            'client' => $this->getRecord()->client,
                            'invoice' => $this->getRecord(),
                            'organisation' => $this->getRecord()->organisation,
                        ])
                        ->resolve();

                    if ($email === null) return $data;

                    $data['subject'] = $email['subject'];
                    $data['body'] = $email['body'];;

                    return $data;
                }),


            ActionGroup::make([

                ClientCardAction::make()
                    ->record($this->getRecord()->client),
            ])->label('More')->button()->outlined(),
        ];
    }
}
