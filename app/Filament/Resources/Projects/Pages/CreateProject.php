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
use Filament\Forms\Components\Checkbox;
use App\Models\Holiday;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
                        ->disabledDates(function () {
                            // Disable weekends (Saturday = 6, Sunday = 0)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
                            
                            while ($startDate <= $endDate) {
                                $formatted = $startDate->format('Y-m-d');
                                if ($startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                                    $disabledDates[] = $formatted;
                                }
                                $startDate->addDay();
                            }
                            
                            return $disabledDates;
                        })
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
                        ->disabledDates(function () {
                            // Disable weekends (Saturday = 6, Sunday = 0)
                            $disabledDates = [];
                            $startDate = now()->subYear();
                            $endDate = now()->addYears(2);
                            $holidayDates = Holiday::pluck('date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
                            
                            while ($startDate <= $endDate) {
                                $formatted = $startDate->format('Y-m-d');
                                if ($startDate->isWeekend() || in_array($formatted, $holidayDates, true)) {
                                    $disabledDates[] = $formatted;
                                }
                                $startDate->addDay();
                            }
                            
                            return $disabledDates;
                        })
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
                        ->disabledDates(function () {
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
                            
                            return $disabledDates;
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
                        ->disabledDates(function () {
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
                            
                            return $disabledDates;
                        })
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                        }),
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
                ->modalContent(fn () => view('filament.projects.pics_modal', ['project' => $this->record]))
                ->modalActions([
                    Action::make('close')
                        ->label('Close')
                        ->close(),
                ]),
        ];
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
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            while ($start->lte($end)) {
                if (!$start->isWeekend() && !in_array($start->format('Y-m-d'), $holidayDates, true)) {
                    $regularDays++;
                }
                $start->addDay();
            }
        }

        if ($hasOvertime && $overtimeStartDate && $overtimeEndDate) {
            $overtimeStart = Carbon::parse($overtimeStartDate);
            $overtimeEnd = Carbon::parse($overtimeEndDate);

            while ($overtimeStart->lte($overtimeEnd)) {
                if ($overtimeStart->isWeekend() && !in_array($overtimeStart->format('Y-m-d'), $holidayDates, true)) {
                    $overtimeDays++;
                }
                $overtimeStart->addDay();
            }
        }

        return $regularDays + $overtimeDays;
    }
}