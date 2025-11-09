<?php

namespace App\Filament\App\Resources\MedicalDocuments;

use App\Filament\App\Resources\MedicalDocuments\Pages\CreateMedicalDocument;
use App\Filament\App\Resources\MedicalDocuments\Pages\EditMedicalDocument;
use App\Filament\App\Resources\MedicalDocuments\Pages\ListMedicalDocuments;
use App\Filament\App\Resources\MedicalDocuments\Pages\MedicalDocumentPastItems;
use App\Filament\App\Resources\MedicalDocuments\Pages\MedicalDocumentTasks;
use App\Filament\App\Resources\MedicalDocuments\Pages\MedicalDocumentUploadDocuments;
use App\Filament\App\Resources\MedicalDocuments\Pages\ViewMedicalDocument;
use App\Filament\App\Resources\MedicalDocuments\Schemas\MedicalDocumentForm;
use App\Filament\App\Resources\MedicalDocuments\Schemas\MedicalDocumentInfolist;
use App\Filament\App\Resources\MedicalDocuments\Tables\MedicalDocumentsTable;
use App\Models\MedicalDocument;
use BackedEnum;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicalDocumentResource extends Resource
{
    protected static ?string $model = MedicalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $navigationLabel = 'Medical documentation';

    protected static ?string $label = 'document';

    protected static ?string $pluralLabel = 'documents';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewMedicalDocument::class,
            MedicalDocumentPastItems::class,
            MedicalDocumentUploadDocuments::class,
            MedicalDocumentTasks::class,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return MedicalDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MedicalDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MedicalDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withSum('items', 'total')
            ->withCount(['pastMedicalDocuments', 'patient', 'documents', 'tasks']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedicalDocuments::route('/'),
            'create' => CreateMedicalDocument::route('/create'),
            'view' => ViewMedicalDocument::route('/{record}'),
            'edit' => EditMedicalDocument::route('/{record}/edit'),
            'past-items' => MedicalDocumentPastItems::route('/{record}/past-items'),
            'documents' => MedicalDocumentUploadDocuments::route('/{record}/documents'),
            'tasks' => MedicalDocumentTasks::route('/{record}/tasks'),
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
