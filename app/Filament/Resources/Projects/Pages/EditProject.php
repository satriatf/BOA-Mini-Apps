<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\ProjectResource;
use App\Models\Project;
use App\Models\ProjectPic;
use App\Models\User;
use App\Models\Holiday;
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
use Carbon\Carbon;

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
                        ->disabledDates(fn ($get) => $this->getDisabledDatesForUser($get('sk_user')))
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
                        ->closeOnDateSelection()
                        ->minDate(fn($get) => $get('start_date'))
                        ->disabled(fn($get) => !$get('start_date'))
                        ->disabledDates(fn ($get) => $this->getDisabledDatesForUser($get('sk_user')))
                        ->afterStateUpdated(function ($state, callable $set, $get) {
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
                        ->disabledDates(function ($get) {
                            // Hanya izinkan weekend (Sabtu-Minggu)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
                            
                            while ($startDate <= $endDate) {
                                $formatted = $startDate->format('Y-m-d');
                                if (!$startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                                    $disabledDates[] = $formatted;
                                }
                                $startDate->addDay();
                            }
                            
                            // Block only weekdays from regular date range (weekends can be used for overtime)
                            if ($get('start_date') && $get('end_date')) {
                                $regularStart = Carbon::parse($get('start_date'));
                                $regularEnd = Carbon::parse($get('end_date'));
                                
                                while ($regularStart->lte($regularEnd)) {
                                    // Only block weekdays, allow weekends for overtime
                                    if (!$regularStart->isWeekend()) {
                                        $disabledDates[] = $regularStart->format('Y-m-d');
                                    }
                                    $regularStart->addDay();
                                }
                            }
                            
                            // Block existing overtime dates untuk PIC yang sama
                            if ($get('sk_user') && $this->record) {
                                $existingPics = ProjectPic::where('sk_project', $this->record->sk_project)
                                    ->where('sk_user', $get('sk_user'))
                                    ->whereNull('deleted_at')
                                    ->get();
                                
                                foreach ($existingPics as $pic) {
                                    if ($pic->has_overtime && $pic->overtime_start_date && $pic->overtime_end_date) {
                                        $overtimeStart = Carbon::parse($pic->overtime_start_date);
                                        $overtimeEnd = Carbon::parse($pic->overtime_end_date);
                                        
                                        while ($overtimeStart->lte($overtimeEnd)) {
                                            $disabledDates[] = $overtimeStart->format('Y-m-d');
                                            $overtimeStart->addDay();
                                        }
                                    }
                                }
                            }
                            
                            return array_values(array_unique($disabledDates));
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            if ($get('overtime_end_date') && $state && $get('overtime_end_date') < $state) {
                                $set('overtime_end_date', null);
                            }
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
                        ->disabledDates(function ($get) {
                            // Hanya izinkan weekend (Sabtu-Minggu)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
                            
                            while ($startDate <= $endDate) {
                                $formatted = $startDate->format('Y-m-d');
                                if (!$startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                                    $disabledDates[] = $formatted;
                                }
                                $startDate->addDay();
                            }
                            
                            // Block only weekdays from regular date range (weekends can be used for overtime)
                            if ($get('start_date') && $get('end_date')) {
                                $regularStart = Carbon::parse($get('start_date'));
                                $regularEnd = Carbon::parse($get('end_date'));
                                
                                while ($regularStart->lte($regularEnd)) {
                                    // Only block weekdays, allow weekends for overtime
                                    if (!$regularStart->isWeekend()) {
                                        $disabledDates[] = $regularStart->format('Y-m-d');
                                    }
                                    $regularStart->addDay();
                                }
                            }
                            
                            // Block existing overtime dates untuk PIC yang sama
                            if ($get('sk_user') && $this->record) {
                                $existingPics = ProjectPic::where('sk_project', $this->record->sk_project)
                                    ->where('sk_user', $get('sk_user'))
                                    ->whereNull('deleted_at')
                                    ->get();
                                
                                foreach ($existingPics as $pic) {
                                    if ($pic->has_overtime && $pic->overtime_start_date && $pic->overtime_end_date) {
                                        $overtimeStart = Carbon::parse($pic->overtime_start_date);
                                        $overtimeEnd = Carbon::parse($pic->overtime_end_date);
                                        
                                        while ($overtimeStart->lte($overtimeEnd)) {
                                            $disabledDates[] = $overtimeStart->format('Y-m-d');
                                            $overtimeStart->addDay();
                                        }
                                    }
                                }
                            }
                            
                            return array_values(array_unique($disabledDates));
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                        }),
                    
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

                    $startDate = Carbon::parse($data['start_date']);
                    $endDate = !empty($data['end_date'])
                        ? Carbon::parse($data['end_date'])
                        : $startDate->copy();

                    $existingPics = ProjectPic::where('sk_project', $project->sk_project)
                        ->where('sk_user', $data['sk_user'])
                        ->whereNull('deleted_at')
                        ->get();

                    foreach ($existingPics as $existingPic) {
                        $existingStart = $existingPic->start_date ?? Carbon::parse($existingPic->start_date);
                        $existingEnd = $existingPic->end_date ?? $existingStart;

                        if ($startDate->lte($existingEnd) && $endDate->gte($existingStart)) {
                            Notification::make()
                                ->danger()
                                ->title('PIC already scheduled on these dates')
                                ->body('Pick a different date range for this PIC to avoid overlap.')
                                ->send();
                            return;
                        }
                    }
                    
                    $totalDays = $this->calculateTotalDaysStatic(
                        $data['start_date'],
                        $data['end_date'] ?? null,
                        $data['has_overtime'] ?? false,
                        $data['overtime_start_date'] ?? null,
                        $data['overtime_end_date'] ?? null
                    );

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

    protected function getDisabledDatesForUser(?string $picId): array
    {
        $disabledDates = [];

        $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        $startDate = now()->subYear();
        $endDate = now()->addYears(2);

        while ($startDate <= $endDate) {
            $formatted = $startDate->format('Y-m-d');
            if ($startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                $disabledDates[] = $formatted;
            }
            $startDate->addDay();
        }

        if (!$picId || !$this->record) {
            return $disabledDates;
        }

        $existingPics = ProjectPic::where('sk_project', $this->record->sk_project)
            ->where('sk_user', $picId)
            ->whereNull('deleted_at')
            ->get();

        foreach ($existingPics as $pic) {
            $current = ($pic->start_date ?? Carbon::parse($pic->start_date))->copy();
            $end = $pic->end_date ?? $pic->start_date;
            $end = $end instanceof Carbon ? $end->copy() : Carbon::parse($end);

            while ($current->lte($end)) {
                $disabledDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        return array_values(array_unique($disabledDates));
    }

    /**
     * Calculate total days for regular (weekdays, excluding holidays) and overtime (weekends, excluding holidays)
     */
    protected function calculateTotalDaysStatic($startDate, $endDate, $hasOvertime, $overtimeStartDate, $overtimeEndDate)
    {
        $regularDays = 0;
        $overtimeDays = 0;
        $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate);
            $end = \Carbon\Carbon::parse($endDate);

            while ($start->lte($end)) {
                if (!$start->isWeekend() && !in_array($start->format('Y-m-d'), $holidayDates, true)) {
                    $regularDays++;
                }
                $start->addDay();
            }
        }

        if ($hasOvertime && $overtimeStartDate && $overtimeEndDate) {
            $overtimeStart = \Carbon\Carbon::parse($overtimeStartDate);
            $overtimeEnd = \Carbon\Carbon::parse($overtimeEndDate);

            while ($overtimeStart->lte($overtimeEnd)) {
                if ($overtimeStart->isWeekend() && !in_array($overtimeStart->format('Y-m-d'), $holidayDates, true)) {
                    $overtimeDays++;
                }
                $overtimeStart->addDay();
            }
        }

        return $regularDays + $overtimeDays;
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