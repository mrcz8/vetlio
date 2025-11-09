<?php

namespace App\Filament\App\Resources\Clients;

use App\Filament\App\Resources\Clients\Pages\ClientDocuments;
use App\Filament\App\Resources\Clients\Pages\ClientInvoices;
use App\Filament\App\Resources\Clients\Pages\ClientItemsToPay;
use App\Filament\App\Resources\Clients\Pages\ClientNotes;
use App\Filament\App\Resources\Clients\Pages\ClientPatients;
use App\Filament\App\Resources\Clients\Pages\ClientPayments;
use App\Filament\App\Resources\Clients\Pages\ClientReminders;
use App\Filament\App\Resources\Clients\Pages\ClientReservations;
use App\Filament\App\Resources\Clients\Pages\ClientTasks;
use App\Filament\App\Resources\Clients\Pages\ListClients;
use App\Filament\App\Resources\Clients\Pages\ViewClient;
use App\Filament\App\Resources\Clients\Schemas\ClientForm;
use App\Filament\App\Resources\Clients\Schemas\ClientInfolist;
use App\Filament\App\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'first_name';

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $label = 'client';

    protected static ?string $pluralLabel = 'clients';

    protected static bool $isScopedToTenant = false;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewClient::class,
            ClientPatients::class,
            ClientReservations::class,
            ClientInvoices::class,
            ClientPayments::class,
            ClientItemsToPay::class,
            ClientNotes::class,
            ClientReminders::class,
            ClientTasks::class,
            ClientDocuments::class,
        ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['first_name', 'last_name', 'oib', 'email'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery();
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Name' => $record->full_name,
            'Phone' => $record->phone ?? '-',
            'Email' => $record->email ?? '-',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
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
            'index' => ListClients::route('/'),
            //'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            //'edit' => EditClient::route('/{record}/edit'),
            'notes' => ClientNotes::route('/{record}/notes'),
            'reminders' => ClientReminders::route('/{record}/reminders'),
            'reservations' => ClientReservations::route('/{record}/reservations'),
            'patients' => ClientPatients::route('/{record}/patients'),
            'tasks' => ClientTasks::route('/{record}/tasks'),
            'documents' => ClientDocuments::route('/{record}/documents'),
            'invoices' => ClientInvoices::route('/{record}/invoices'),
            'payments' => ClientPayments::route('/{record}/payments'),
            'items-to-pay' => ClientItemsToPay::route('/{record}/items-to-pay'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('itemsToPay', 'total')
            ->withCount([
                'itemsToPay',
                'reservations',
                'patients',
                'invoices',
                'payments',
                'tasks',
                'notes',
                'reminders',
                'documents',
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
