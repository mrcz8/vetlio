<?php

namespace App\Filament\App\Clusters\Setup\Resources\EmailTemplates;

use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages\EditEmailTemplate;
use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Schemas\EmailTemplateForm;
use App\Filament\App\Clusters\Setup\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use App\Filament\App\Clusters\Setup\SetupCluster;
use App\Models\EmailTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

    protected static ?int $navigationSort = 11;

    protected static ?string $cluster = SetupCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $label = 'email template';

    protected static ?string $pluralLabel = 'email templates';

    protected static ?string $navigationLabel = 'Email templates';

    public static function form(Schema $schema): Schema
    {
        return EmailTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailTemplatesTable::configure($table);
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
            'index' => ListEmailTemplates::route('/'),
            //'create' => CreateEmailTemplate::route('/create'),
            'edit' => EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
