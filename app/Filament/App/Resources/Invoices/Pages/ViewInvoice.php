<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use App\Filament\App\Actions\ClientCardAction;
use App\Filament\App\Actions\SendEmailAction;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\Payments\Schemas\PaymentForm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Number;

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
                $data['subject'] = 'Your invoice: ' . $this->getRecord()->code;
                $data['body'] = 'Please find attached your invoice. The total amount is: ' . Number::currency($this->getRecord()->total) . '.';
                return $data;
            })
            ->outlined()
            ->hiddenLabel()
            ->subject('Your invoice')
            ->label('Send email')
            ->icon(PhosphorIcons::Envelope);
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

                Action::make('download-pdf')
                    ->label('Download PDF')
                    ->outlined()
                    ->schema([
                        TextInput::make('code'),
                    ])
                    ->action(fn() => dd('Generating print...'))
                    ->icon(PhosphorIcons::Download),
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

            Action::make('createPayment')
                ->label('Add payment')
                ->color('success')
                ->icon(PhosphorIcons::CreditCard)
                ->modalIcon(PhosphorIcons::CreditCard)
                ->modalHeading('Payment for invoice')
                ->model(Payment::class)
                ->schema(fn($schema) => PaymentForm::configure($schema)->columns(2))
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
                ->action(fn($record, $data) => $record->payments()->create($data))
                ->visible(fn($record) => !$record->payed),
        ];
    }
}
