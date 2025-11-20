<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\ProjectPic;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Delete Project'),

            Action::make('addPic')
                ->label('Add PIC')
                ->icon('heroicon-o-plus')
                ->modalHeading('Add PIC')
                ->form([
                    Select::make('sk_user')
                        ->label('Select PIC')
                        ->options(function () {
                            $assigned = [];
                            if ($this->record) {
                                $assigned = $this->record->projectPics()->pluck('sk_user')->toArray();
                            }

                            return User::where('is_active', 'Active')
                                ->where('level', 'Staff')
                                ->when(! empty($assigned), fn($q) => $q->whereNotIn('sk_user', $assigned))
                                ->pluck('employee_name', 'sk_user');
                        })
                        ->searchable()
                        ->required(),

                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->reactive()
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if ($get('end_date') && $state && $get('end_date') < $state) {
                                $set('end_date', null);
                            }
                        }),

                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->reactive()
                        ->nullable()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->minDate(fn($get) => $get('start_date'))
                        ->closeOnDateSelection()
                        ->disabled(fn($get) => !$get('start_date')),
                ])
                ->action(function (array $data) {
                    $project = $this->record;
                    if (!$project) {
                        Notification::make()->warning()->title('Project not found')->send();
                        return;
                    }

                    Validator::make($data, [
                        'sk_user' => ['required', 'string', 'exists:users,sk_user'],
                        'start_date' => ['required', 'date'],
                        'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                    ])->validate();

                    ProjectPic::create([
                        'sk_project' => $project->sk_project,
                        'sk_user' => $data['sk_user'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'] ?? null,
                        'created_by' => optional(Auth::user())->employee_name ?? null,
                    ]);

                    Notification::make()->success()->title('PIC added')->send();
                }),

            Action::make('viewPic')
                ->label('View PIC')
                ->icon('heroicon-o-eye')
                ->modalHeading('View PIC')
                ->modalContent(fn () => view('filament.projects.pics_modal', ['project' => $this->record]))
                ->modalActions([
                    Action::make('close')
                        ->label('Close')
                        ->close(),
                ]),
        ];
    }
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Cancel');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // PICs are now stored in the `project_pics` table and managed via modal UI.
        return $data;
    }

    public function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }
    public function deleteProjectPic(int $id)
    {
        $pic = ProjectPic::find($id);

        if (! $pic) {
            Notification::make()->warning()->title('PIC not found')->send();
            return;
        }

        try {
            $pic->deleted_by = optional(Auth::user())->employee_name ?? null;
            $pic->save();
            $pic->delete();
            Notification::make()->success()->title('PIC removed')->send();
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Failed to remove PIC')->body($e->getMessage())->send();
        }
    }
}