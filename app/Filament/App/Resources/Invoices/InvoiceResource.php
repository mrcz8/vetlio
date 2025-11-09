<?php

namespace App\Filament\App\Resources\Invoices;

use App\Enums\Icons\PhosphorIcons;
use App\Filament\App\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\App\Resources\Invoices\Pages\EditInvoice;
use App\Filament\App\Resources\Invoices\Pages\InvoiceNotes;
use App\Filament\App\Resources\Invoices\Pages\InvoicePayments;
use App\Filament\App\Resources\Invoices\Pages\InvoiceReminders;
use App\Filament\App\Resources\Invoices\Pages\InvoiceTasks;
use App\Filament\App\Resources\Invoices\Pages\ListInvoices;
use App\Filament\App\Resources\Invoices\Pages\NotPayedInvoices;
use App\Filament\App\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\App\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\App\Resources\Invoices\Schemas\InvoiceInfolist;
use App\Filament\App\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use UnitEnum;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = PhosphorIcons::Money;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?string $label = 'invoice';

    protected static ?string $pluralLabel = 'invoices';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'client.first_name', 'client.last_name'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['client'])->whereNull('storno_of_id');
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Client' => $record->client->full_name,
            'Total amount' => Number::format($record->total) ?? '-',
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewInvoice::class,
            InvoicePayments::class,
            InvoiceTasks::class,
            InvoiceReminders::class,
            InvoiceNotes::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InvoiceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'not-payed-invoices' => NotPayedInvoices::route('/not-payed-invoices'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => EditInvoice::route('/{record}/edit'),
            'tasks' => InvoiceTasks::route('/{record}/tasks'),
            'payments' => InvoicePayments::route('/{record}/payments'),
            'reminders' => InvoiceReminders::route('/{record}/reminders'),
            'notes' => InvoiceNotes::route('/{record}/notes'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
