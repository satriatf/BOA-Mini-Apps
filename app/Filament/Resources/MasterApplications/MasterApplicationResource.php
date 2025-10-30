<?php

namespace App\Filament\Resources\MasterApplications;

use App\Filament\Resources\MasterApplications\Pages\CreateMasterApplication;
use App\Filament\Resources\MasterApplications\Pages\EditMasterApplication;
use App\Filament\Resources\MasterApplications\Pages\ListMasterApplications;
use App\Filament\Resources\MasterApplications\Schemas\MasterApplicationForm;
use App\Filament\Resources\MasterApplications\Tables\MasterApplicationsTable;
use App\Models\MasterApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MasterApplicationResource extends Resource
{
    protected static ?string $model = MasterApplication::class;

    // Use a squares-2x2 icon for Applications in Master group
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?int $navigationSort = 50;

    public static function getNavigationBadge(): ?string
    {
        return (string) MasterApplication::count();
    }

    public static function getModelLabel(): string
    {
        return 'Application';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Applications';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterApplicationsTable::configure($table);
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
            'index' => ListMasterApplications::route('/'),
            'create' => CreateMasterApplication::route('/create'),
            'edit' => EditMasterApplication::route('/{record}/edit'),
        ];
    }
}
