<?php

namespace App\Filament\App\Resources\Invoices\Schemas;

use App\Enums\Icons\PhosphorIcons;
use App\Enums\PaymentMethod;
use App\Filament\App\Clusters\Setup\Resources\Banks\BankResource;
use App\Filament\Tables\ItemsToSelectTable;
use App\Models\Bank;
use App\Models\Organisation;
use App\Models\Service;
use CodeWithDennis\SimpleAlert\Components\SimpleAlert;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ModalTableSelect;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('client_id')
                            ->live(true)
                            ->relationship('client', 'first_name')
                            ->required()
                            ->label('Client'),

                        DatePicker::make('invoice_date')
                            ->required()
                            ->label('Invoice date')
                            ->default(now()),

                        DatePicker::make('invoice_due_date')
                            ->required(fn(Get $get) => $get('payment_method_id') == PaymentMethod::BANK)
                            ->label('Due date')
                            ->default(now()->addDays(15)),

                        ToggleButtons::make('payment_method_id')
                            ->label('Payment method')
                            ->required()
                            ->grouped()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('invoice_due_date', null);
                                $set('card_id', null);
                                $set('bank_account_id', null);

                                if ($get('payment_method_id') == PaymentMethod::BANK) {
                                    $set('invoice_due_date', now()->addDays(15));
                                }
                            })
                            ->live()
                            ->default(PaymentMethod::CASH)
                            ->options(PaymentMethod::class),

                        Select::make('issuer_id')
                            ->relationship('issuer', 'first_name')
                            ->label('Issuer')
                            ->required()
                            ->default(auth()->id())
                            ->prefixIcon(PhosphorIcons::User),

                        Select::make('card_id')
                            ->label('Card')
                            ->options([
                                1 => 'Visa',
                                2 => 'Mastercard',
                            ])
                            ->required(fn(Get $get) => $get('payment_method_id') == PaymentMethod::CARD)
                            ->disabled(fn(Get $get) => $get('payment_method_id') != PaymentMethod::CARD),

                        Select::make('bank_account_id')
                            ->relationship('bankAccount', 'name')
                            ->label('Bank')
                            ->createOptionForm(fn(Schema $schema) => BankResource::form($schema))
                            ->createOptionUsing(fn(array $data): int => Bank::create($data)->getKey())
                            ->required(fn(Get $get) => $get('payment_method_id') == PaymentMethod::BANK)
                            ->disabled(fn(Get $get) => $get('payment_method_id') != PaymentMethod::BANK)
                            ->prefixIcon(PhosphorIcons::Bank),

                        SpatieTagsInput::make('tags')
                            ->label('Tags'),

                        ModalTableSelect::make('service_id')
                            ->hiddenLabel()
                            ->badge(false)
                            ->model(Organisation::class)
                            ->dehydrated()
                            ->multiple()
                            ->relationship('services', 'name')
                            ->selectAction(
                                fn(Action $action) => $action
                                    ->label('Select services')
                                    ->modalIcon(Heroicon::DocumentText)
                                    ->modalHeading('Select services')
                                    ->modalSubmitActionLabel('Confirm'),
                            )
                            ->multiple()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                $services = Service::whereIn('id', $state)->get();

                                if (!$services) return;

                                $services->each(function ($service) use ($set, $get) {
                                    $item = [
                                        'priceable_id' => $service->id,
                                        'priceable_type' => Service::class,
                                        'name' => $service->name,
                                        'description' => 'Service',
                                        'quantity' => 1,
                                        'price' => Number::format($service->currentPrice->price, 2),
                                        'vat' => 25,
                                        'discount' => 0,
                                        'total' => Number::format($service->currentPrice->price_with_vat, 2),
                                    ];

                                    $items = collect($get('invoiceItems') ?? []);
                                    $items->push($item);
                                    $set('invoiceItems', $items->toArray());
                                });

                                $set('service_id', null);
                            })
                            ->tableConfiguration(ItemsToSelectTable::class),

                        self::getItemsComponent()
                            ->visible(fn(Get $get) => $get('invoiceItems')),

                        SimpleAlert::make('no-items')
                            ->visible(fn(Get $get) => !$get('invoiceItems'))
                            ->columnSpanFull()
                            ->title('No items added')
                            ->warning()
                            ->description('There are currently no items, please add some.'),

                        Grid::make(5)
                            ->columnStart(3)
                            ->columnSpanFull()
                            ->schema([
                                Grid::make(1)
                                    ->columnSpanFull()
                                    ->columnStart(3)
                                    ->schema([
                                        TextEntry::make('total')
                                            ->label('Total')
                                            ->inlineLabel()
                                            ->money('EUR')
                                            ->alignEnd()
                                            ->getStateUsing(fn(Get $get, Set $set) => self::calculateTotal($get, $set)['total']),

                                        TextEntry::make('discount')
                                            ->label('Total discount')
                                            ->inlineLabel()
                                            ->money('EUR')
                                            ->alignEnd()
                                            ->getStateUsing(fn(Get $get, Set $set) => self::calculateTotal($get, $set)['discount']),

                                        TextEntry::make('vat')
                                            ->label('Total VAT')
                                            ->inlineLabel()
                                            ->money('EUR')
                                            ->alignEnd()
                                            ->getStateUsing(fn(Get $get, Set $set) => self::calculateTotal($get, $set)['vat']),

                                        TextEntry::make('total_to_pay')
                                            ->label('Amount due')
                                            ->inlineLabel()
                                            ->size(TextSize::Large)
                                            ->money('EUR')
                                            ->weight(FontWeight::Bold)
                                            ->alignEnd()
                                            ->getStateUsing(fn(Get $get, Set $set) => self::calculateTotal($get, $set)['total_to_pay']),
                                    ]),
                            ]),
                        Textarea::make('client_note')
                            ->label('Note for client')
                            ->columnSpanFull(),
                        Textarea::make('terms_and_conditions')
                            ->label('Terms and conditions')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function calculateTotal(Get $get, Set $set): array
    {
        $items = $get('invoiceItems') ?? [];

        $total = $discount = $vat = 0;

        foreach ($items as $item) {
            $price = $item['price'] ?? 0;
            $itemVat = $item['vat'] ?? 0;
            $itemDiscount = $item['discount'] ?? 0;
            $quantity = $item['quantity'] ?? 1;

            $itemTotal = (($price + $itemVat) - $itemDiscount) * $quantity;

            $total += $itemTotal;
            $discount += ($itemDiscount * $quantity);
            $vat += ($itemVat * $quantity);
        }

        $set('total', Number::format($total, 2));

        return [
            'total' => $total,
            'discount' => $discount,
            'vat' => $vat,
            'total_to_pay' => $total,
        ];
    }

    public static function makeCalculations(Get $get, Set $set): void
    {
        $items = $get('invoiceItems');
        $collectItems = [];

        foreach ($items as $invoiceItem) {
            $priceableId = Arr::get($invoiceItem, 'priceable_id');
            $priceableType = Arr::get($invoiceItem, 'priceable_type');
            $price = Arr::get($invoiceItem, 'price', 0);
            $itemVat = Arr::get($invoiceItem, 'vat', 0);
            $itemDiscount = Arr::get($invoiceItem, 'discount', 0);
            $quantity = Arr::get($invoiceItem, 'quantity', 1);

            $itemTotal = (($price + $itemVat) - $itemDiscount) * $quantity;

            $invoiceItem['priceable_id'] = $priceableId;
            $invoiceItem['priceable_type'] = $priceableType;
            $invoiceItem['price'] = Number::format($price, 2);
            $invoiceItem['vat'] = Number::format($itemVat, 2);
            $invoiceItem['discount'] = Number::format($itemDiscount, 2);
            $invoiceItem['total'] = Number::format($itemTotal, 2);

            $collectItems[] = $invoiceItem;
        }

        $set('invoiceItems', $collectItems);

        self::calculateTotal($get, $set);
    }

    public static function getItemsComponent(): Repeater
    {
        return Repeater::make('invoiceItems')
            ->itemNumbers()
            ->cloneable()
            ->columnSpanFull()
            ->label('Invoice items')
            ->addActionLabel('Add item')
            ->compact()
            ->addable(false)
            ->defaultItems(0)
            ->minItems(1)
            ->table([
                TableColumn::make('Item')
                    ->width('350px')
                    ->markAsRequired(),
                TableColumn::make('Quantity')
                    ->markAsRequired()
                    ->alignEnd(),
                TableColumn::make('Price')
                    ->markAsRequired()
                    ->alignEnd(),
                TableColumn::make('VAT')
                    ->alignEnd(),
                TableColumn::make('Discount')
                    ->alignEnd(),
                TableColumn::make('Total')
                    ->alignEnd(),
            ])
            ->schema([
                TextInput::make('name'),
                TextInput::make('quantity')
                    ->required()
                    ->extraInputAttributes(['class' => 'text-right'])
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        if ($get('quantity') == null) {
                            $set('quantity', 1);
                        }
                    })
                    ->minValue(1)
                    ->default(1)
                    ->live(false, 500)
                    ->numeric(),

                TextInput::make('price')
                    ->required()
                    ->extraInputAttributes(['class' => 'text-right'])
                    ->formatStateUsing(fn($state) => Number::format($state ?? 0, 2))
                    ->default(0)
                    ->live(false, 500),

                TextInput::make('vat')
                    ->extraInputAttributes(['class' => 'text-right'])
                    ->default(0)
                    ->live(false, 500),

                TextInput::make('discount')
                    ->extraInputAttributes(['class' => 'text-right'])
                    ->default(0)
                    ->live(false, 500),

                TextInput::make('total')
                    ->extraInputAttributes(['class' => 'text-right font-bold'])
                    ->default(0),

                TextInput::make('priceable_type'),
                TextInput::make('priceable_id'),
            ])
            ->afterStateUpdated(fn(Get $get, Set $set) => self::makeCalculations($get, $set));
    }
}
