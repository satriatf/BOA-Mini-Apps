/**
 * Gantt Chart JavaScript
 * Vanilla JS implementation for employee task visualization
 */

// English month short names (match PHP 'M' format, e.g. 'Oct')
const MONTH_NAMES = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

// Color tokens
const PROJECT_COLOR = '#3b82f6';
const PROJECT_BADGE_BG = '#dbeafe';
const PROJECT_BADGE_COLOR = '#1e40af';
const OVERTIME_COLOR = '#a855f7';
const OVERTIME_BADGE_BG = '#f3e8ff';
const OVERTIME_BADGE_COLOR = '#7e22ce';
const NON_PROJECT_COLOR = '#f59e0b';
const NON_PROJECT_BADGE_BG = '#fef3c7';
const NON_PROJECT_BADGE_COLOR = '#b45309';
const HOLIDAY_COLOR = '#22c55e';
const HOLIDAY_BADGE_BG = '#dcfce7';
const HOLIDAY_BADGE_COLOR = '#166534';

/**
 * Get number of days in a month for a given year
 */
function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

/**
 * Get all days in a month
 * Returns array of day objects with date and label
 */
function getMonthDays(year, month) {
    const days = [];
    const daysInMonth = getDaysInMonth(year, month);
    
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month - 1, day);
        days.push({
            date: new Date(date),
            label: day.toString()
        });
    }
    
    return days;
}

/**
 * Check if two date ranges overlap (inclusive)
 */
function overlaps(aStart, aEnd, bStart, bEnd) {
    // Normalize dates to start of day to avoid timezone issues
    const aStartNorm = new Date(aStart.getFullYear(), aStart.getMonth(), aStart.getDate());
    const aEndNorm = new Date(aEnd.getFullYear(), aEnd.getMonth(), aEnd.getDate());
    const bStartNorm = new Date(bStart.getFullYear(), bStart.getMonth(), bStart.getDate());
    const bEndNorm = new Date(bEnd.getFullYear(), bEnd.getMonth(), bEnd.getDate());
    
    const result = aStartNorm <= bEndNorm && bStartNorm <= aEndNorm;
    return result;
}

/**
 * Create tooltip element
 */
function createTooltip() {
    const tooltip = document.createElement('div');
    tooltip.className = 'gantt-tooltip';
    document.body.appendChild(tooltip);
    return tooltip;
}

/**
 * Show tooltip with task information (only title)
 */
function showTooltip(tooltip, cell, tasks) {
    if (!tasks || tasks.length === 0) return;
    
    const task = tasks[0]; // Show first task info
    const title = task.title || 'Task';
    
    // Show only title in tooltip
    tooltip.innerHTML = `
        <div class="gantt-tooltip-title">${title}</div>
    `;
    
    // Position tooltip
    const rect = cell.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2) + 'px';
    tooltip.style.top = (rect.bottom + 5) + 'px';
    tooltip.classList.add('show');
}

/**
 * Hide tooltip
 */
function hideTooltip(tooltip) {
    tooltip.classList.remove('show');
}

/**
 * Show holiday detail popup with green title
 */
function showHolidayDetailPopup(dateStr, holidays) {
    // Remove existing popups
    document.querySelectorAll('.gantt-detail-overlay').forEach(x => x.remove());
    
    // Fetch holiday data from backend (we need the holiday name)
    fetch('/admin/holidays-data?date=' + dateStr)
        .then(res => res.json())
        .then(holiday => {
            if (!holiday) return;
            
            const overlay = document.createElement('div');
            overlay.className = 'gantt-detail-overlay';
            
            const card = document.createElement('div');
            card.className = 'gantt-detail-card';
            
            // Holiday title in GREEN with badge
            const taskTitle = document.createElement('div');
            taskTitle.style.cssText = `font-size:14px;font-weight:600;margin-bottom:8px;color:${HOLIDAY_COLOR};`;
            taskTitle.textContent = holiday.description || holiday.name || 'Holiday';
            
            const badge = document.createElement('span');
            badge.textContent = 'Holiday';
            badge.style.cssText = `margin-left:8px;padding:2px 6px;border-radius:4px;background:${HOLIDAY_BADGE_BG};color:${HOLIDAY_BADGE_COLOR};font-size:11px;font-weight:700;`;
            taskTitle.appendChild(badge);
            
            card.appendChild(taskTitle);
            
            // Format date
            function formatMonthDay(dateStr) {
                const d = new Date(dateStr);
                if (Number.isNaN(d.getTime())) return '';
                const month = MONTH_NAMES[d.getMonth()];
                const day = d.getDate();
                const year = d.getFullYear();
                return `${month} ${day}, ${year}`;
            }
            
            const dateFormatted = formatMonthDay(holiday.date);
            const whenEl = document.createElement('div');
            whenEl.style.cssText = `color:${HOLIDAY_BADGE_COLOR};margin-bottom:10px;font-size:12px;`;
            whenEl.textContent = dateFormatted + ' — ' + dateFormatted;
            card.appendChild(whenEl);
            
            // Holiday description
            const contentEl = document.createElement('div');
            contentEl.style.cssText = 'color:#4b5563;font-size:13px;';
            
            const typeRow = document.createElement('div');
            typeRow.style.cssText = 'display:flex;margin-bottom:4px;';
            typeRow.innerHTML = '<div style="font-weight:700;width:100px;"><strong>Type</strong></div><div>' + (holiday.type || 'Holiday') + '</div>';
            contentEl.appendChild(typeRow);
            
            const dateRow = document.createElement('div');
            dateRow.style.cssText = 'display:flex;margin-bottom:4px;';
            dateRow.innerHTML = '<div style="font-weight:700;width:100px;"><strong>Date</strong></div><div>' + dateFormatted + '</div>';
            contentEl.appendChild(dateRow);
            
            const descRow = document.createElement('div');
            descRow.style.cssText = 'display:flex;';
            descRow.innerHTML = '<div style="font-weight:700;width:100px;"><strong>Description</strong></div><div>' + (holiday.description || '-') + '</div>';
            contentEl.appendChild(descRow);
            
            card.appendChild(contentEl);
            
            const actionsEl = document.createElement('div');
            actionsEl.className = 'gantt-detail-actions';
            
            const closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.className = 'gantt-btn gantt-btn-gray';
            closeBtn.textContent = 'Close';
            closeBtn.onclick = () => overlay.remove();
            actionsEl.appendChild(closeBtn);
            
            card.appendChild(actionsEl);
            overlay.appendChild(card);
            document.body.appendChild(overlay);
            
            // Close on overlay click
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.remove();
            });
            
            // Close on Escape key
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    overlay.remove();
                    document.removeEventListener('keydown', escHandler);
                }
            };
            document.addEventListener('keydown', escHandler);
        })
        .catch(err => console.error('Failed to fetch holiday data:', err));
}

/**
 * Show detail popup (similar to yearly/monthly)
 */
function showDetailPopup(tasks) {
    if (!tasks || tasks.length === 0) return;

    // Deduplicate tasks (avoid double entries when regular + overtime overlap)
    const uniqueTasks = [];
    const seenKeys = new Set();
    tasks.forEach(t => {
        const key = `${t.title || ''}|${t.start || ''}|${t.end || ''}|${t.type || ''}`;
        if (!seenKeys.has(key)) {
            seenKeys.add(key);
            uniqueTasks.push(t);
        }
    });
    tasks = uniqueTasks;
    
    // Remove existing popups
    document.querySelectorAll('.gantt-detail-overlay').forEach(x => x.remove());
    
    const overlay = document.createElement('div');
    overlay.className = 'gantt-detail-overlay';
    
    const card = document.createElement('div');
    card.className = 'gantt-detail-card';
    
    // Title for multiple tasks
    const titleEl = document.createElement('div');
    titleEl.style.cssText = 'font-size:16px;font-weight:700;margin-bottom:8px;';
    if (tasks.length === 1) {
        titleEl.textContent = tasks[0].title || 'Task Detail';
    } else {
        titleEl.textContent = `${tasks.length} Tasks in this Week`;
    }
    
    // Show all tasks
    tasks.forEach((task, index) => {
        if (index > 0) {
            const separator = document.createElement('hr');
            separator.style.cssText = 'margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;';
            card.appendChild(separator);
        }
        
        const isOvertime = !!task.has_overtime;
        const isNonProject = task.type === 'mtc';
        const titleColor = isOvertime ? OVERTIME_COLOR : (isNonProject ? NON_PROJECT_COLOR : PROJECT_COLOR);
        const dateColor = isOvertime ? OVERTIME_BADGE_COLOR : (isNonProject ? NON_PROJECT_BADGE_COLOR : PROJECT_BADGE_COLOR);
        
        const taskTitle = document.createElement('div');
        taskTitle.style.cssText = `font-size:14px;font-weight:600;margin-bottom:8px;color:${titleColor};`;
        taskTitle.textContent = task.title || 'Task Detail';
        
        // Add badge
        const badge = document.createElement('span');
        if (isOvertime) {
            badge.textContent = 'Overtime';
            badge.style.cssText = `margin-left:8px;padding:2px 6px;border-radius:4px;background:${OVERTIME_BADGE_BG};color:${OVERTIME_BADGE_COLOR};font-size:11px;font-weight:700;`;
            taskTitle.appendChild(badge);
        } else if (isNonProject) {
            badge.textContent = 'Non-Project';
            badge.style.cssText = `margin-left:8px;padding:2px 6px;border-radius:4px;background:${NON_PROJECT_BADGE_BG};color:${NON_PROJECT_BADGE_COLOR};font-size:11px;font-weight:700;`;
            taskTitle.appendChild(badge);
        } else {
            badge.textContent = 'Project';
            badge.style.cssText = `margin-left:8px;padding:2px 6px;border-radius:4px;background:${PROJECT_BADGE_BG};color:${PROJECT_BADGE_COLOR};font-size:11px;font-weight:700;`;
            taskTitle.appendChild(badge);
        }
        card.appendChild(taskTitle);
        
        // Format as "Month Day, Year" (e.g. Oct 24, 2024)
        function formatMonthDay(dateStr) {
            const d = new Date(dateStr);
            if (Number.isNaN(d.getTime())) return '';
            const month = MONTH_NAMES[d.getMonth()];
            const day = d.getDate();
            const year = d.getFullYear();
            return `${month} ${day}, ${year}`;
        }

        const startDate = task.start ? formatMonthDay(task.start) : '';
        const endDate = task.end ? formatMonthDay(task.end) : '';
        
        const whenEl = document.createElement('div');
        whenEl.style.cssText = `color:${dateColor};margin-bottom:10px;font-size:12px;`;
        whenEl.textContent = startDate + (endDate && endDate !== startDate ? ' — ' + endDate : '');
        card.appendChild(whenEl);
        
        const contentEl = document.createElement('div');
        contentEl.innerHTML = task.details || '';
        card.appendChild(contentEl);
    });
    
    const actionsEl = document.createElement('div');
    actionsEl.className = 'gantt-detail-actions';
    
    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'gantt-btn gantt-btn-gray';
    closeBtn.textContent = 'Close';
    closeBtn.onclick = () => overlay.remove();
    actionsEl.appendChild(closeBtn);
    
    card.appendChild(actionsEl);
    overlay.appendChild(card);
    document.body.appendChild(overlay);
    
    // Close on overlay click
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) overlay.remove();
    });
    
    // Close on Escape key
    const escHandler = (e) => {
        if (e.key === 'Escape') {
            overlay.remove();
            document.removeEventListener('keydown', escHandler);
        }
    };
    document.addEventListener('keydown', escHandler);
}

/**
 * Render the Gantt chart
 */
function renderGantt(container, data, year, showProject = true, showNonProject = true) {
    // Clear existing content
    container.innerHTML = '';
    
    // Handle data structure - can be array or object with rows/holidays
    const rows = data.rows ? data.rows : data;
    const holidays = data.holidays ? data.holidays : [];
    
    // Create tooltip
    const tooltip = createTooltip();
    
    // If no data, show empty state
    if (!rows || rows.length === 0) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'gantt-empty';
        emptyDiv.innerHTML = `
            <div style="text-align: center; padding: 3rem 1rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;">📅</div>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem; font-weight: 600;">No Projects Found</h3>
                <p style="color: #9ca3af; margin-bottom: 1rem;">No project or non-project tasks found for ${year}.</p>
                <p style="color: #9ca3af; font-size: 0.875rem;">Try selecting a different year or add some projects and tasks.</p>
            </div>
        `;
        container.appendChild(emptyDiv);
        return;
    }
    
    // Note: At least one filter should always be enabled due to UI validation
    
    // Create table
    const table = document.createElement('table');
    table.className = 'gantt-table';
    
    // Create header
    const thead = document.createElement('thead');
    
    // Row 1: Month headers
    const monthRow = document.createElement('tr');
    const employeeHeader = document.createElement('th');
    employeeHeader.className = 'employee-header';
    employeeHeader.textContent = 'Employee';
    employeeHeader.rowSpan = 2;
    monthRow.appendChild(employeeHeader);
    
    // Add month headers with colspan
    for (let month = 1; month <= 12; month++) {
        const days = getMonthDays(year, month);
        const daysInMonth = getDaysInMonth(year, month);
        const monthHeader = document.createElement('th');
        monthHeader.className = 'month-header';
        monthHeader.textContent = `${MONTH_NAMES[month - 1]} ${year}`;
        monthHeader.colSpan = days.length;
        monthRow.appendChild(monthHeader);
    }
    
    thead.appendChild(monthRow);
    
    // Row 2: Day headers
    const dayRow = document.createElement('tr');
    for (let month = 1; month <= 12; month++) {
        const days = getMonthDays(year, month);
        days.forEach(day => {
            const dayHeader = document.createElement('th');
            const dayOfWeek = day.date.getDay(); // 0 = Sun, 6 = Sat
            const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
            dayHeader.className = 'day-header' + (isWeekend ? ' weekend' : '');
            dayHeader.textContent = day.label;
            dayRow.appendChild(dayHeader);
        });
    }
    
    thead.appendChild(dayRow);
    table.appendChild(thead);
    
    // Create body
    const tbody = document.createElement('tbody');
    
    // Create rows for each employee - create up to 2 rows per employee (Project and Non-Project).
    // Always render a row for each employee (so active employees without tasks still appear).
    rows.forEach((row, rowIndex) => {
        // Ensure row has required properties
        if (!row || typeof row !== 'object') {
            console.error('Invalid row:', row);
            return;
        }
        
        // Separate tasks by type
        const projectTasks = (row.tasks || []).filter(task => task.type === 'project');
        const nonProjectTasks = (row.tasks || []).filter(task => task.type === 'mtc');

        let projectTr = null;
        let nonProjectTr = null;

        // Only create project row if filter Project aktif dan ada task project
        if (showProject && projectTasks.length > 0) {
            projectTr = document.createElement('tr');
        }
        // Only create non-project row jika filter Non-Project aktif dan ada task non-project
        if (showNonProject && nonProjectTasks.length > 0) {
            nonProjectTr = document.createElement('tr');
        }

        // Jika tidak ada satupun row, tetap tampilkan baris kosong untuk karyawan
        if (!projectTr && !nonProjectTr) {
            // Baris kosong, employee tetap muncul
            projectTr = document.createElement('tr');
        }

        // Build employee cell: jika dua row, pakai rowspan=2
        if (projectTr && nonProjectTr) {
            const employeeCell = document.createElement('td');
            employeeCell.className = 'employee';
            employeeCell.textContent = row.name || 'Unknown';
            employeeCell.rowSpan = 2;
            projectTr.appendChild(employeeCell);
        } else if (projectTr || nonProjectTr) {
            const targetRow = projectTr || nonProjectTr;
            const employeeCell = document.createElement('td');
            employeeCell.className = 'employee';
            employeeCell.textContent = row.name || 'Unknown';
            targetRow.appendChild(employeeCell);
        }
        
        // Create day cells for Project row (hanya jika project row ada dan filter aktif)
        if (projectTr && showProject) {
            for (let month = 1; month <= 12; month++) {
                const days = getMonthDays(year, month);
                
                days.forEach(day => {
                    const cell = document.createElement('td');
                    
                    // Check if this day is a holiday (use local date formatting)
                    const year = day.date.getFullYear();
                    const month = String(day.date.getMonth() + 1).padStart(2, '0');
                    const dayNum = String(day.date.getDate()).padStart(2, '0');
                    const dayDateStr = `${year}-${month}-${dayNum}`;
                    const isHoliday = holidays.includes(dayDateStr);
                    
                    // Find overlapping project tasks for this day
                    const overlappingTasks = projectTasks.filter(task => {
                        const taskStart = new Date(task.start + 'T00:00:00');
                        const taskEnd = new Date(task.end + 'T00:00:00');
                        const dayStart = new Date(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
                        const dayEnd = new Date(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
                        
                        // For single-day tasks, check exact date match
                        if (taskStart.getTime() === taskEnd.getTime()) {
                            return taskStart.toDateString() === dayStart.toDateString();
                        }
                        
                        // For multi-day tasks, use overlap function
                        return overlaps(taskStart, taskEnd, dayStart, dayEnd);
                    });
                    
                    if (overlappingTasks.length > 0) {
                        // Check if any overlapping task has overtime
                        const hasOvertimeTask = overlappingTasks.some(task => task.has_overtime);
                        cell.className = hasOvertimeTask ? 'project-overtime' : 'project';
                        console.log('Added project cell with class:', cell.className);
                        // Count unique projects (by title) to show 2+ badge
                        const uniqueProjects = new Set(overlappingTasks.map(t => t.title));
                        if (uniqueProjects.size > 1) {
                            // Add badge showing number of projects
                            const badge = document.createElement('div');
                            badge.className = 'task-count-badge';
                            badge.textContent = uniqueProjects.size + '+';
                            badge.style.cssText = 'position:absolute;top:2px;right:2px;color:white;font-size:11px;font-weight:700;';
                            cell.style.position = 'relative';
                            cell.appendChild(badge);
                        }
                        // We keep a single colored cell even if multiple tasks overlap
                        // (avoid showing “2+” so overtime overlay doesn’t create duplicates)

                        // Filter tasks for tooltip/popup: if overtime cell, show only overtime tasks
                        const displayTasks = hasOvertimeTask
                            ? overlappingTasks.filter(t => t.has_overtime)
                            : overlappingTasks;

                        // Add tooltip functionality
                        cell.addEventListener('mouseenter', () => {
                            showTooltip(tooltip, cell, displayTasks);
                        });
                        
                        cell.addEventListener('mouseleave', () => {
                            hideTooltip(tooltip);
                        });

                        // Add click functionality for popup detail
                        cell.addEventListener('click', () => {
                            showDetailPopup(displayTasks);
                        });
                    } else if (isHoliday) {
                        // Show holiday if no task on this day
                        cell.className = 'holiday';
                        
                        // Add click functionality for holiday detail
                        cell.addEventListener('click', () => {
                            showHolidayDetailPopup(dayDateStr, holidays);
                        });
                    }
                    
                    projectTr.appendChild(cell);
                });
            }
        }
        
        // Create day cells for Non-Project row (hanya jika non-project row ada dan filter aktif)
        if (nonProjectTr && showNonProject) {
            for (let month = 1; month <= 12; month++) {
                const days = getMonthDays(year, month);
                
                days.forEach(day => {
                    const cell = document.createElement('td');
                    
                    // Check if this day is a holiday (use local date formatting)
                    const year = day.date.getFullYear();
                    const month = String(day.date.getMonth() + 1).padStart(2, '0');
                    const dayNum = String(day.date.getDate()).padStart(2, '0');
                    const dayDateStr = `${year}-${month}-${dayNum}`;
                    const isHoliday = holidays.includes(dayDateStr);
                    
                    // Find overlapping non-project tasks for this day
                    const overlappingTasks = nonProjectTasks.filter(task => {
                        const taskStart = new Date(task.start + 'T00:00:00');
                        const taskEnd = new Date(task.end + 'T00:00:00');
                        const dayStart = new Date(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
                        const dayEnd = new Date(day.date.getFullYear(), day.date.getMonth(), day.date.getDate());
                        
                        // For single-day tasks, check exact date match
                        if (taskStart.getTime() === taskEnd.getTime()) {
                            return taskStart.toDateString() === dayStart.toDateString();
                        }
                        
                        // For multi-day tasks, use overlap function
                        return overlaps(taskStart, taskEnd, dayStart, dayEnd);
                    });
                    
                    if (overlappingTasks.length > 0) {
                        cell.className = 'mtc';
                        console.log('Added MTC cell with class:', cell.className);
                        
                        // Add text content for multiple tasks
                        if (overlappingTasks.length > 1) {
                            cell.textContent = `${overlappingTasks.length}+`;
                            cell.style.fontSize = '0.75rem';
                            cell.style.fontWeight = '600';
                        }
                        
                        // Add tooltip functionality
                        cell.addEventListener('mouseenter', () => {
                            showTooltip(tooltip, cell, overlappingTasks);
                        });
                        
                        cell.addEventListener('mouseleave', () => {
                            hideTooltip(tooltip);
                        });

                        // Add click functionality for popup detail
                        cell.addEventListener('click', () => {
                            showDetailPopup(overlappingTasks);
                        });
                    } else if (isHoliday) {
                        // Show holiday if no task on this day
                        cell.className = 'holiday';
                        
                        // Add click functionality for holiday detail
                        cell.addEventListener('click', () => {
                            showHolidayDetailPopup(dayDateStr, holidays);
                        });
                    }
                    
                    nonProjectTr.appendChild(cell);
                });
            }
        }
        
        // Add rows to tbody (only if they exist)
        if (projectTr) {
            tbody.appendChild(projectTr);
        }
        if (nonProjectTr) {
            tbody.appendChild(nonProjectTr);
        }
    });
    
    table.appendChild(tbody);
    container.appendChild(table);
}

/**
 * Initialize the Gantt chart when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('#timeline-root .gantt-container');
    const year = window.GANTT_YEAR;
    const data = window.GANTT_DATA;
    
    if (container && year && data !== undefined) {

        
        // Initial render with both filters enabled
        renderGantt(container, data, year, true, true);
        
        // Add event listeners for external filter checkboxes
        const projectFilterCheckbox = document.getElementById('filter-project');
        const nonProjectFilterCheckbox = document.getElementById('filter-non-project');
        
        if (projectFilterCheckbox && nonProjectFilterCheckbox) {
            const updateFilters = (event) => {
                const showProjectTasks = projectFilterCheckbox.checked;
                const showNonProjectTasks = nonProjectFilterCheckbox.checked;
                
                // Prevent both checkboxes from being unchecked
                if (!showProjectTasks && !showNonProjectTasks) {
                    // Keep the checkbox that was just clicked as checked
                    event.target.checked = true;
                    return;
                }
                
                renderGantt(container, data, year, showProjectTasks, showNonProjectTasks);
            };
            
            projectFilterCheckbox.addEventListener('change', updateFilters);
            nonProjectFilterCheckbox.addEventListener('change', updateFilters);
        }
    } else {
        console.error('Project Timeline initialization failed: missing data or container');
    }
});
