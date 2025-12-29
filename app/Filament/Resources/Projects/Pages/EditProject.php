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
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Delete')
                ->modalHeading(function ($record) {
                    $name = $record->project_name ?? 'this project';
                    return 'DELETE "' . $name . '"';
                })
                ->modalDescription('Are you sure you would like to do this?'),

            Action::make('addPic')
                ->label('Add PIC')
                ->icon('heroicon-o-plus')
                ->modalHeading('Add PIC')
                ->form([
                    Select::make('sk_user')
                        ->label('Select PIC')
                        ->options(function () {
                            return User::where('is_active', 'Active')
                                ->where('level', 'Staff')
                                ->pluck('employee_name', 'sk_user');
                        })
                        ->searchable()
                        ->required()
                        ->live(),

                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->reactive()
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->disabledDates(function ($get) {
                            $picId = $get('sk_user');
                            $disabledDates = [];
                            
                            // Disable weekends
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            
                            while ($startDate <= $endDate) {
                                if ($startDate->isWeekend()) {
                                    $disabledDates[] = $startDate->format('Y-m-d');
                                }
                                $startDate->addDay();
                            }
                            
                            if (!$picId) {
                                return $disabledDates;
                            }

                            $project = $this->record;

                            // Get all existing PIC assignments for this user
                            $existingPics = ProjectPic::where('sk_project', $project->sk_project)
                                ->where('sk_user', $picId)
                                ->whereNull('deleted_at')
                                ->get();

                            // Collect all dates that are already assigned
                            foreach ($existingPics as $pic) {
                                $current = $pic->start_date->copy();
                                $end = $pic->end_date ? $pic->end_date->copy() : $pic->start_date->copy();

                                while ($current->lte($end)) {
                                    $disabledDates[] = $current->format('Y-m-d');
                                    $current->addDay();
                                }
                            }

                            return array_unique($disabledDates);
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if ($get('end_date') && $state && $get('end_date') < $state) {
                                $set('end_date', null);
                            }
                            // Recalculate total days
                            $this->calculateTotalDays($get, $set);
                        }),

                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->reactive()
                        ->nullable()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->minDate(fn($get) => $get('start_date'))
                        ->disabled(fn($get) => !$get('start_date'))
                        ->disabledDates(function ($get) {
                            $picId = $get('sk_user');
                            $disabledDates = [];
                            
                            // Disable weekends
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            
                            while ($startDate <= $endDate) {
                                if ($startDate->isWeekend()) {
                                    $disabledDates[] = $startDate->format('Y-m-d');
                                }
                                $startDate->addDay();
                            }
                            
                            if (!$picId) {
                                return $disabledDates;
                            }

                            $project = $this->record;

                            // Get all existing PIC assignments for this user
                            $existingPics = ProjectPic::where('sk_project', $project->sk_project)
                                ->where('sk_user', $picId)
                                ->whereNull('deleted_at')
                                ->get();

                            // Collect all dates that are already assigned
                            foreach ($existingPics as $pic) {
                                $current = $pic->start_date->copy();
                                $end = $pic->end_date ? $pic->end_date->copy() : $pic->start_date->copy();

                                while ($current->lte($end)) {
                                    $disabledDates[] = $current->format('Y-m-d');
                                    $current->addDay();
                                }
                            }

                            return array_unique($disabledDates);
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            // Recalculate total days when end_date changes
                            $this->calculateTotalDays($get, $set);
                        }),
                    
                    Checkbox::make('has_overtime')
                        ->label('Include Overtime')
                        ->reactive()
                        ->live(),
                    
                    DatePicker::make('overtime_start_date')
                        ->label('Overtime Start Date')
                        ->reactive()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->visible(fn($get) => $get('has_overtime'))
                        ->required(fn($get) => $get('has_overtime'))
                        ->disabledDates(function () {
                            // Hanya izinkan weekend (Sabtu-Minggu)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            
                            while ($startDate <= $endDate) {
                                if (!$startDate->isWeekend()) {
                                    $disabledDates[] = $startDate->format('Y-m-d');
                                }
                                $startDate->addDay();
                            }
                            
                            return $disabledDates;
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if ($get('overtime_end_date') && $state && $get('overtime_end_date') < $state) {
                                $set('overtime_end_date', null);
                            }
                            // Recalculate total days
                            $this->calculateTotalDays($get, $set);
                        }),
                    
                    DatePicker::make('overtime_end_date')
                        ->label('Overtime End Date')
                        ->reactive()
                        ->nullable()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->closeOnDateSelection()
                        ->visible(fn($get) => $get('has_overtime'))
                        ->minDate(fn($get) => $get('overtime_start_date'))
                        ->disabled(fn($get) => !$get('overtime_start_date'))
                        ->disabledDates(function () {
                            // Hanya izinkan weekend (Sabtu-Minggu)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            
                            while ($startDate <= $endDate) {
                                if (!$startDate->isWeekend()) {
                                    $disabledDates[] = $startDate->format('Y-m-d');
                                }
                                $startDate->addDay();
                            }
                            
                            return $disabledDates;
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            // Recalculate total days
                            $this->calculateTotalDays($get, $set);
                        }),
                    
                    TextInput::make('total_days')
                        ->label('Total Days')
                        ->disabled()
                        ->reactive()
                        ->default(0)
                        ->helperText(fn($get) => $get('has_overtime') ? 'Regular days (weekdays only) + Overtime days (all days)' : 'Weekdays only'),
                ])
                ->action(function (array $data) {
                    $project = $this->record;
                    if (!$project) {
                        Notification::make()->warning()->title('Project not found')->send();
                        return;
                    }

                    $validationRules = [
                        'sk_user' => ['required', 'string', 'exists:users,sk_user'],
                        'start_date' => ['required', 'date'],
                        'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                    ];
                    
                    if (!empty($data['has_overtime'])) {
                        $validationRules['overtime_start_date'] = ['required', 'date'];
                        $validationRules['overtime_end_date'] = ['nullable', 'date', 'after_or_equal:overtime_start_date'];
                    }
                    
                    Validator::make($data, $validationRules)->validate();
                    
                    // Calculate total days
                    $totalDays = $this->calculateTotalDaysStatic(
                        $data['start_date'],
                        $data['end_date'] ?? null,
                        $data['has_overtime'] ?? false,
                        $data['overtime_start_date'] ?? null,
                        $data['overtime_end_date'] ?? null
                    );

                    // Simple create - overlaps will be handled in display logic
                    ProjectPic::create([
                        'sk_project' => $project->sk_project,
                        'sk_user' => $data['sk_user'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date'] ?? null,
                        'has_overtime' => $data['has_overtime'] ?? false,
                        'overtime_start_date' => $data['overtime_start_date'] ?? null,
                        'overtime_end_date' => $data['overtime_end_date'] ?? null,
                        'total_days' => $totalDays,
                        'created_by' => optional(Auth::user())->employee_name ?? null,
                    ]);

                    Notification::make()->success()->title('PIC added successfully')->send();
                }),

            Action::make('viewPic')
                ->label('View PIC')
                ->icon('heroicon-o-eye')
                ->modalHeading('View PIC')
                ->modalContent(fn () => view('Filament.projects.pics_modal', ['project' => $this->record]))
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
    
    protected function calculateTotalDays($get, $set)
    {
        $regularDays = 0;
        $overtimeDays = 0;
        
        // Calculate regular days (weekdays only)
        if ($get('start_date') && $get('end_date')) {
            $start = \Carbon\Carbon::parse($get('start_date'));
            $end = \Carbon\Carbon::parse($get('end_date'));
            
            while ($start->lte($end)) {
                if (!$start->isWeekend()) {
                    $regularDays++;
                }
                $start->addDay();
            }
        }
        
        // Calculate overtime days (all days)
        if ($get('has_overtime') && $get('overtime_start_date') && $get('overtime_end_date')) {
            $overtimeStart = \Carbon\Carbon::parse($get('overtime_start_date'));
            $overtimeEnd = \Carbon\Carbon::parse($get('overtime_end_date'));
            $overtimeDays = $overtimeStart->diffInDays($overtimeEnd) + 1;
        }
        
        $set('total_days', $regularDays + $overtimeDays);
    }
    
    protected function calculateTotalDaysStatic($startDate, $endDate, $hasOvertime, $overtimeStartDate, $overtimeEndDate)
    {
        $regularDays = 0;
        $overtimeDays = 0;
        
        // Calculate regular days (weekdays only)
        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);
            
            while ($start->lte($end)) {
                if (!$start->isWeekend()) {
                    $regularDays++;
                }
                $start->addDay();
            }
        }
        
        // Calculate overtime days (all days)
        if ($hasOvertime && $overtimeStartDate && $overtimeEndDate) {
            $overtimeStart = \Carbon\Carbon::parse($overtimeStartDate);
            $overtimeEnd = \Carbon\Carbon::parse($overtimeEndDate);
            $overtimeDays = $overtimeStart->diffInDays($overtimeEnd) + 1;
        }
        
        return $regularDays + $overtimeDays;
    }
}