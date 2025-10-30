<?php

namespace App\Filament\Resources\MasterNonProjectTypes;

use App\Filament\Resources\MasterNonProjectTypes\Pages\CreateMasterNonProjectType;
use App\Filament\Resources\MasterNonProjectTypes\Pages\EditMasterNonProjectType;
use App\Filament\Resources\MasterNonProjectTypes\Pages\ListMasterNonProjectTypes;
use App\Filament\Resources\MasterNonProjectTypes\Schemas\MasterNonProjectTypeForm;
use App\Filament\Resources\MasterNonProjectTypes\Tables\MasterNonProjectTypesTable;
use App\Models\MasterNonProjectType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MasterNonProjectTypeResource extends Resource
{
    protected static ?string $model = MasterNonProjectType::class;

    // Use briefcase icon to represent Non-Project Types in Master group
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Non-Project Types';

    protected static ?int $navigationSort = 40;

    public static function getNavigationBadge(): ?string
    {
        return (string) MasterNonProjectType::count();
    }

    public static function getModelLabel(): string
    {
        return 'Non-Project Type';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Non-Project Types';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterNonProjectTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterNonProjectTypesTable::configure($table);
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
            'index' => ListMasterNonProjectTypes::route('/'),
            'create' => CreateMasterNonProjectType::route('/create'),
            'edit' => EditMasterNonProjectType::route('/{record}/edit'),
        ];
    }
}
