<?php

namespace App\Filament\Resources\MasterLeaveTypes;

use App\Filament\Resources\MasterLeaveTypes\Pages\CreateMasterLeaveType;
use App\Filament\Resources\MasterLeaveTypes\Pages\EditMasterLeaveType;
use App\Filament\Resources\MasterLeaveTypes\Pages\ListMasterLeaveTypes;
use App\Filament\Resources\MasterLeaveTypes\Schemas\MasterLeaveTypeForm;
use App\Filament\Resources\MasterLeaveTypes\Tables\MasterLeaveTypesTable;
use App\Models\MasterLeaveType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MasterLeaveTypeResource extends Resource
{
    protected static ?string $model = MasterLeaveType::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master';

    protected static ?string $navigationLabel = 'Leave Types';

    protected static ?int $navigationSort = 51;

    public static function getNavigationBadge(): ?string
    {
        return (string) MasterLeaveType::count();
    }

    public static function getModelLabel(): string
    {
        return 'Leave Type';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Leave Types';
    }

    protected static function isAdminUser(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return (bool) ($user->is_admin ?? false);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isAdminUser();
    }

    public static function canViewAny(): bool
    {
        return static::isAdminUser();
    }

    public static function canView($record): bool
    {
        return static::isAdminUser();
    }

    public static function canCreate(): bool
    {
        return static::isAdminUser();
    }

    public static function canEdit($record): bool
    {
        return static::isAdminUser();
    }

    public static function canDelete($record): bool
    {
        return static::isAdminUser();
    }

    public static function canDeleteAny(): bool
    {
        return static::isAdminUser();
    }

    public static function form(Schema $schema): Schema
    {
        return MasterLeaveTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MasterLeaveTypesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMasterLeaveTypes::route('/'),
            'create' => CreateMasterLeaveType::route('/create'),
            'edit' => EditMasterLeaveType::route('/{record}/edit'),
        ];
    }
}
