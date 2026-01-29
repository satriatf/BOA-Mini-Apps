<?php

namespace App\Filament\Resources\MasterProjectStatuses;

use App\Filament\Resources\MasterProjectStatuses\Pages\CreateMasterProjectStatus;
use App\Filament\Resources\MasterProjectStatuses\Pages\EditMasterProjectStatus;
use App\Filament\Resources\MasterProjectStatuses\Pages\ListMasterProjectStatuses;
use App\Filament\Resources\MasterProjectStatuses\Schemas\MasterProjectStatusForm;
use App\Filament\Resources\MasterProjectStatuses\Tables\MasterProjectStatusesTable;
use App\Models\MasterProjectStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MasterProjectStatusResource extends Resource
{
    protected static ?string $model = MasterProjectStatus::class;

    // Use briefcase icon for Project Statuses
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Project Statuses';

    protected static ?int $navigationSort = 30;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (! $user) return false;

        return ($user->is_admin) || in_array($user->level, ['Manager', 'Asisten Manager', 'Section Head']);
    }


    public static function getNavigationBadge(): ?string
    {
        return (string) MasterProjectStatus::count();
    }

    public static function getModelLabel(): string
    {
        return 'Project Status';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Project Statuses';
    }

    public static function form(Schema $schema): Schema
    {
        return MasterProjectStatusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterProjectStatusesTable::configure($table);
    }

    // ...existing code...

    public static function getPages(): array
    {
        return [
            'index' => ListMasterProjectStatuses::route('/'),
            'create' => CreateMasterProjectStatus::route('/create'),
            'edit' => EditMasterProjectStatus::route('/{record}/edit'),
        ];
    }
}
