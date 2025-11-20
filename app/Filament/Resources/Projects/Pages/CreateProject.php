<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\ProjectPic;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function getTitle(): string { return 'Add Project'; }
    public function getBreadcrumb(): string { return 'Add Project'; }

    protected function hasCreateAnother(): bool { return false; }
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /** Ensure pics data is properly formatted */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            // Submit → simpan → list
            $this->getCreateFormAction()
                ->label('Submit')
                ->action(function () {
                    $this->create();
                    $this->redirect(static::getResource()::getUrl('index'));
                }),

            // Draft → simpan → notif → edit
            Action::make('draft')
                ->label('Draft')
                ->action(function () {
                    $this->create();

                    Notification::make()
                        ->title('Saved as draftt')
                        ->success()
                        ->send();

                    $this->redirect(
                        static::getResource()::getUrl('edit', ['record' => $this->record])
                    );
                }),

            $this->getCancelFormAction()->label('Cancel'),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
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
                    if (! $this->record) {
                        $this->create();
                    }

                    $project = $this->record;
                    if (! $project) {
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
}
