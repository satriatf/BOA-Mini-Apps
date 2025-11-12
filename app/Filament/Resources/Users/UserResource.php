<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Employees';
    protected static ?string $pluralLabel = 'Employees';
    protected static ?string $label = 'Employee';
    // Tampilkan Employees di grup "Master" pada sidebar
    protected static string|\UnitEnum|null $navigationGroup = 'Master';
    // Urutan dalam grup Master (lebih kecil = lebih atas)
    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return "Employees";
    }

    public static function getNavigationBadge(): ?string
    {
        $count = User::where('is_active', 'Active')
            ->where(function ($q) {
                $q->where('is_admin', false)->orWhereNull('is_admin');
            })
            ->count();

        return (string) $count;
    }

    /**
     * Exclude script/admin users (is_admin = true) from resource queries so they
     * do not appear anywhere in the Filament UI listings.
     */
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        return $query->where(function ($q) {
            $q->where('is_admin', false)->orWhereNull('is_admin');
        });
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
