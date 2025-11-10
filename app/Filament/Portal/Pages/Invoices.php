<?php

namespace App\Filament\Portal\Pages;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\Shared\Columns\CreatedAtColumn;
use App\Models\Invoice;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class Invoices extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.portal.pages.invoices';

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Money;

    protected static ?int $navigationSort = 3;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendar')
                ->label('Calendar')
                ->link()
                ->color('neutral')
                ->icon(PhosphorIcons::Calendar),

            Action::make('documents')
                ->label('Documents')
                ->link()
                ->color('neutral')
                ->icon(PhosphorIcons::Paperclip)
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage your veterinary bills and payment history';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable()
                    ->label('Code'),

                TextColumn::make('branch.name')
                    ->sortable()
                    ->searchable()
                    ->label('Branch'),

                TextColumn::make('invoice_date')
                    ->sortable()
                    ->date()
                    ->label('Invoice date'),

                TextColumn::make('payment_method_id')
                    ->sortable()
                    ->label('Payment method'),

                TextColumn::make('user.full_name')
                    ->sortable()
                    ->searchable()
                    ->label('Created by'),

                TextColumn::make('total_base_price')
                    ->label('Base amount')
                    ->numeric(2)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total_tax')
                    ->label('VAT amount')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total_discount')
                    ->label('Discount')
                    ->numeric(2)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null),

                TextColumn::make('total')
                    ->label('Total')
                    ->numeric(2)
                    ->sortable()
                    ->suffix(' EUR')
                    ->color(fn($record) => $record->storno_of_id ? 'danger' : null)
                    ->weight(FontWeight::Bold),

                CreatedAtColumn::make('created_at'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View'),
                Action::make('download')
                    ->url(fn(Invoice $record) => route('print.invoices.download', ['record' => $record]))
                    ->icon(PhosphorIcons::Download),
            ])
            ->emptyStateActions([])
            ->emptyStateHeading('No invoices found')
            ->emptyStateDescription('You have not made any invoices yet.')
            ->query(Invoice::query()->where('client_id', auth()->id()));
    }
}
