<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\EmailTemplateType;
use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\Payments\Schemas\PaymentForm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\EmailTemplate\EmailTemplateRenderer;
use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Field;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::End;

    protected static ?string $navigationLabel = 'Invoice';

    public function getTitle(): string
    {
        return 'Invoice: ' . $this->getRecord()->code;
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Client: ' . $this->getRecord()->client->full_name;
    }

    public function sendInvoiceByEmailAction(): SendEmailAction
    {
        return SendEmailAction::make()
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
            });
    }

    protected function getHeaderActions(): array
    {
        return [
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
                ->hidden(function(Invoice $record) {
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

            $this->sendInvoiceByEmailAction(),


            ActionGroup::make([

                ClientCardAction::make()
                    ->record($this->getRecord()->client),
            ])->label('More')->button()->outlined(),
        ];
    }
}
