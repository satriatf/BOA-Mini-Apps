<?php

namespace App\Filament\Resources\OnLeaves;

use App\Filament\Resources\OnLeaves\Pages\CreateOnLeave;
use App\Filament\Resources\OnLeaves\Pages\EditOnLeave;
use App\Filament\Resources\OnLeaves\Pages\ListOnLeaves;
use App\Filament\Resources\OnLeaves\Schemas\OnLeaveForm;
use App\Filament\Resources\OnLeaves\Tables\OnLeavesTable;
use App\Models\OnLeave;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OnLeaveResource extends Resource
{
    protected static ?string $model = OnLeave::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $recordTitleAttribute = 'id';

    // Put On Leaves under Tasks > Non Project (group name 'Tasks')
    protected static string|UnitEnum|null $navigationGroup = 'Tasks';

    protected static ?string $navigationLabel = 'On Leaves';

    protected static ?int $navigationSort = 120;

    public static function getNavigationBadge(): ?string
    {
        return (string) OnLeave::count();
    }

    public static function getModelLabel(): string
    {
        return 'On Leave';
    }

    public static function getPluralModelLabel(): string
    {
        return 'On Leaves';
    }

    public static function form(Schema $schema): Schema
    {
        return OnLeaveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OnLeavesTable::configure($table);
    }

    // Ensure Create button is visible (override any policy gating)
    public static function canCreate(): bool
    {
        return true;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnLeaves::route('/'),
            'create' => CreateOnLeave::route('/create'),
            'edit' => EditOnLeave::route('/{record}/edit'),
        ];
    }
}
