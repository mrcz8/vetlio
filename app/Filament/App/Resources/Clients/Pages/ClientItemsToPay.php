<?php

namespace App\Filament\App\Resources\Clients\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Clients\ClientResource;
use App\Filament\App\Resources\Invoices\InvoiceResource;
use App\Filament\App\Resources\MedicalDocuments\MedicalDocumentResource;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Livewire;
use UnitEnum;

class ClientItemsToPay extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'itemsToPay';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::MoneyWavy;

    protected static ?string $navigationLabel = 'Items to pay';

    protected static ?string $title = 'Items to pay';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public static function getNavigationBadgeColor(): string|array|null
    {
        return intval(static::getNavigationBadge()) > 0 ? 'danger' : 'success';
    }

    public static function getNavigationBadge(): ?string
    {
        $record = Livewire::current()->getRecord();

        return $record->items_to_pay_count;
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateActions([])
            ->recordTitleAttribute('id')
            ->selectable()
            ->columns([
                TextColumn::make('medicalDocument.code')
                    ->description(function ($record) {
                        return 'Date: ' . $record->medicalDocument->created_at->format('d.m.Y');
                    })
                    ->label('Medical record'),

                TextColumn::make('medicalDocument.reservation.from')
                    ->dateTime('d.m.Y H:i')
                    ->description(function ($record) {
                        return $record->medicalDocument->reservation->from->diffForHumans();
                    })
                    ->label('Reservation'),

                TextColumn::make('priceable.name')
                    ->searchable()
                    ->sortable()
                    ->label('Name'),

                TextColumn::make('serviceProvider.full_name')
                    ->label('Doctor')
                    ->sortable()
                    ->searchable()
                    ->icon(PhosphorIcons::User),

                TextColumn::make('quantity')
                    ->alignRight()
                    ->sortable()
                    ->label('Quantity'),

                TextColumn::make('price')
                    ->alignRight()
                    ->searchable()
                    ->sortable()
                    ->money('EUR')
                    ->label('Price'),

                TextColumn::make('tax')
                    ->alignRight()
                    ->searchable()
                    ->sortable()
                    ->money('EUR')
                    ->label('VAT'),

                TextColumn::make('total')
                    ->searchable()
                    ->sortable()
                    ->alignRight()
                    ->money('EUR')
                    ->summarize(Sum::make()->money('EUR', 100))
                    ->weight(FontWeight::Bold)
                    ->label('Total'),
            ])
            ->recordActions([
                Action::make('medical-document')
                    ->hiddenLabel()
                    ->icon(Heroicon::DocumentText)
                    ->tooltip('Open medical document')
                    ->url(function ($record) {
                        return MedicalDocumentResource::getUrl('view', ['record' => $record->medicalDocument]);
                    }),
            ])
            ->headerActions([
                Action::make('create-invoice')
                    ->icon(PhosphorIcons::MoneyWavy)
                    ->label('Create invoice')
                    ->accessSelectedRecords()
                    ->action(function (Collection $selectedRecords, Action $action) {
                        $recordIds = $selectedRecords->map(fn($record) => $record->id)->toArray();

                        if (!$recordIds) {
                            Notification::make()
                                ->icon(Heroicon::MinusCircle)
                                ->title('No items selected for billing')
                                ->warning()
                                ->send();

                            $this->halt();
                        }

                        $action->redirect(InvoiceResource::getUrl('create', [
                            'medicalDocumentItems' => implode(',', $recordIds),
                            'client' => $this->getRecord()->id,
                        ]));
                    }),
            ]);
    }
}
