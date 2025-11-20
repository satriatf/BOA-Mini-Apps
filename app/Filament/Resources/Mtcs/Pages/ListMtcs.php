<?php

namespace App\Filament\Resources\Mtcs\Pages;

use App\Filament\Resources\Mtcs\MtcResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MtcsExport;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;

class ListMtcs extends ListRecords
{
    protected static string $resource = MtcResource::class;
    protected static ?string $title = 'Non-Projects';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('New Non-Project'),
            Action::make('export_mtc')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->action(function () {
                    $query = $this->getTableQueryForExport();
                    $rows = $query->with(['createdBy', 'resolver'])->get();
                    $timestamp = now()->format('Ymd_His');
                    $filename = "mtcs_{$timestamp}.xlsx";
                    return Excel::download(new MtcsExport($rows), $filename);
                }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No Non-Projects yet');
    }
}
