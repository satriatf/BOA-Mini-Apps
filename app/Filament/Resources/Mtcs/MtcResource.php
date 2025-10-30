<?php

namespace App\Filament\Resources\Mtcs;

use App\Filament\Resources\Mtcs\Pages\CreateMtc;
use App\Filament\Resources\Mtcs\Pages\EditMtc;
use App\Filament\Resources\Mtcs\Pages\ListMtcs;
use App\Filament\Resources\Mtcs\Schemas\MtcForm;
use App\Filament\Resources\Mtcs\Tables\MtcsTable;
use App\Filament\Resources\Mtcs\Actions\ViewAttachment;
use App\Models\Mtc;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MtcResource extends Resource
{
    protected static ?string $model = Mtc::class;

    protected static ?string $recordTitleAttribute = 'application';

    protected static ?string $navigationLabel = 'Non-Projects';
    // Use wrench-screwdriver icon to distinguish Non-Projects from Projects
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static string|\UnitEnum|null $navigationGroup  = 'Tasks';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'non-project';

    public static function getNavigationBadge(): ?string
    {
        return (string) Mtc::where('is_delete', false)->count();
    }

    public static function form(Schema $schema): Schema
    {
        return MtcForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MtcsTable::configure($table);
    }

    public static function getModelLabel(): string
    {
        return 'Non-Project';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Non-Projects';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMtcs::route('/'),
            'create' => CreateMtc::route('/create'),
            'edit'   => EditMtc::route('/{record}/edit'),
        ];
    }
}
