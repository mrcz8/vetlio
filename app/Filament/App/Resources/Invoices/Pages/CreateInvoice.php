<?php

namespace App\Filament\App\Resources\Invoices\Pages;

use App\Enums\PaymentMethod;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Models\MedicalDocumentItem;
use App\Services\InvoiceService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public ?Collection $medicalDocumentItems;

    protected static ?string $title = 'New invoice';

    protected function handleRecordCreation(array $data): Model
    {
        $service = app(InvoiceService::class);

        return $service->createInvoice($data);
    }

    public function mount(): void
    {
        parent::mount();

        $this->medicalDocumentItems = collect();

        $data = [
            'client_id' => null,
            'invoice_date' => now(),
            'issuer_id' => auth()->id(),
            'payment_method_id' => PaymentMethod::CASH,
            'invoiceItems' => [],
        ];

        if (request()->has('client')) {
            $data['client_id'] = request('client');

            $this->form->getComponent('client_id')?->disabled();
        }

        if (request()->has('medicalDocumentItems')) {
            $recordIds = collect(explode(',', request()->query('medicalDocumentItems', '')))
                ->filter()
                ->map(fn($id) => (int)$id)
                ->all();

            $medicalDocumentItems = MedicalDocumentItem::with('priceable')
                ->whereIn('id', $recordIds)
                ->get();

            $this->medicalDocumentItems = $medicalDocumentItems;

            $data['invoiceItems'] = $medicalDocumentItems->map(function ($item) {
                return [
                    'name' => $item->name,
                    'description' => $item->description ?? null,
                    'quantity' => $item->quantity ?? 1,
                    'price' => $item->price ?? 0,
                    'vat' => $item->vat ?? 25,
                    'discount' => $item->discount ?? 0,
                    'total' => $item->total ?? 0,
                    'priceable_id' => $item->priceable_id,
                    'priceable_type' => $item->priceable_type,
                ];
            })->toArray();
        }

        $this->form->fill($data);
    }

    //Redirect and create payment form
    protected function getRedirectUrl(): string
    {
        if ($this->getRecord()->payment_method_id == PaymentMethod::BANK) {
            return InvoiceResource::getUrl('view', ['record' => $this->getRecord(), 'action' => 'createPayment']);
        }

        return parent::getRedirectUrl();
    }

    public function afterCreate(): void
    {
        if ($this->medicalDocumentItems) {

            $this->medicalDocumentItems->each(function ($item) {
                $item->update([
                    'invoice_id' => $this->getRecord()->id,
                ]);
            });
        }
    }

}
