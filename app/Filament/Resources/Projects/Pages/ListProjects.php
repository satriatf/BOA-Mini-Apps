<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectsExport;
use Illuminate\Database\Eloquent\Builder;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Add New Project'),

            
            Action::make('export_project')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-up')
                ->action(function () {
                        $query = $this->getTableQueryForExport();
                        $rows = $query->with('techLead')->get();
                        $timestamp = now()->format('Ymd_His');
                        $filename = "Project_{$timestamp}.xlsx";
                        return Excel::download(new ProjectsExport($rows), $filename);
                    }),
        ];
    }

    public function table(Table $table): Table
    {
        return $table->emptyStateHeading('No projects yet');
    }
}
