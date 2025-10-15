/**
 * Gantt Chart JavaScript
 * Vanilla JS implementation for employee task visualization
 */

// English month names (full names)
const MONTH_NAMES = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

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
 * Show detail popup (similar to yearly/monthly)
 */
function showDetailPopup(tasks) {
    if (!tasks || tasks.length === 0) return;
    
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
        
        const taskTitle = document.createElement('div');
        taskTitle.style.cssText = 'font-size:14px;font-weight:600;margin-bottom:8px;color:#3b82f6;';
        taskTitle.textContent = task.title || 'Task Detail';
        card.appendChild(taskTitle);
        
        const startDate = task.start ? new Date(task.start).toLocaleDateString('id-ID') : '';
        const endDate = task.end ? new Date(task.end).toLocaleDateString('id-ID') : '';
        
        const whenEl = document.createElement('div');
        whenEl.style.cssText = 'color:#4b5563;margin-bottom:10px;font-size:12px;';
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
function renderGantt(container, rows, year, showProject = true, showNonProject = true) {
    // Clear existing content
    container.innerHTML = '';
    
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
    
    // If both filters are disabled, show all tasks (default behavior)
    if (!showProject && !showNonProject) {
        showProject = true;
        showNonProject = true;
    }
    
    // Create legend
    const legend = document.createElement('div');
    legend.className = 'gantt-legend';
    legend.innerHTML = `
        <div class="gantt-legend-item">
            <div class="gantt-legend-color project"></div>
            <span>Project</span>
        </div>
        <div class="gantt-legend-item">
            <div class="gantt-legend-color non-project"></div>
            <span>Non-Project</span>
        </div>
    `;
    container.appendChild(legend);
    
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
            dayHeader.className = 'day-header';
            dayHeader.textContent = day.label;
            dayRow.appendChild(dayHeader);
        });
    }
    
    thead.appendChild(dayRow);
    table.appendChild(thead);
    
    // Create body
    const tbody = document.createElement('tbody');
    
    // Create rows for each employee - create 2 rows per employee (Project and Non-Project)
    rows.forEach((row, rowIndex) => {
        // Ensure row has required properties
        if (!row || typeof row !== 'object') {
            console.error('Invalid row:', row);
            return;
        }
        
        // Separate tasks by type and apply filters
        const projectTasks = (row.tasks || []).filter(task => task.type === 'project');
        const nonProjectTasks = (row.tasks || []).filter(task => task.type === 'mtc');
        
        // Check if employee has tasks that match the current filter
        const hasProjectTasks = projectTasks.length > 0;
        const hasNonProjectTasks = nonProjectTasks.length > 0;
        
        // If both filters are disabled (default), show all employees with tasks
        if (!showProject && !showNonProject) {
            const hasAnyTasks = hasProjectTasks || hasNonProjectTasks;
            if (!hasAnyTasks) {
                return; // Skip this employee if they have no tasks at all
            }
        } else {
            // If filters are active, only show employees with matching tasks
            const shouldShowProject = showProject && hasProjectTasks;
            const shouldShowNonProject = showNonProject && hasNonProjectTasks;
            
            if (!shouldShowProject && !shouldShowNonProject) {
                return; // Skip this employee if they don't have tasks matching the active filters
            }
        }
        
        
        // Create Project row
        let projectTr = null;
        if ((showProject && hasProjectTasks) || (!showProject && !showNonProject && hasProjectTasks)) {
            projectTr = document.createElement('tr');
            const projectEmployeeCell = document.createElement('td');
            projectEmployeeCell.className = 'employee';
            projectEmployeeCell.textContent = row.name || 'Unknown';
            projectTr.appendChild(projectEmployeeCell);
        }
        
        // Create Non-Project row
        let nonProjectTr = null;
        if ((showNonProject && hasNonProjectTasks) || (!showProject && !showNonProject && hasNonProjectTasks)) {
            nonProjectTr = document.createElement('tr');
            const nonProjectEmployeeCell = document.createElement('td');
            nonProjectEmployeeCell.className = 'employee';
            nonProjectEmployeeCell.textContent = row.name || 'Unknown';
            nonProjectTr.appendChild(nonProjectEmployeeCell);
        }
        
        // Create day cells for Project row (only if project row exists)
        if (projectTr) {
            for (let month = 1; month <= 12; month++) {
                const days = getMonthDays(year, month);
                
                days.forEach(day => {
                    const cell = document.createElement('td');
                    
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
                        cell.className = 'project';
                        
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
                    }
                    
                    projectTr.appendChild(cell);
                });
            }
        }
        
        // Create day cells for Non-Project row (only if non-project row exists)
        if (nonProjectTr) {
            for (let month = 1; month <= 12; month++) {
                const days = getMonthDays(year, month);
                
                days.forEach(day => {
                    const cell = document.createElement('td');
                    
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
    const container = document.getElementById('timeline-root');
    const year = window.GANTT_YEAR;
    const data = window.GANTT_DATA;
    
    if (container && year && data !== undefined) {
        // Initial render with both filters enabled
        renderGantt(container, data, year, true, true);
        
        // Add event listeners for filter checkboxes
        const projectCheckbox = document.getElementById('filter-project');
        const nonProjectCheckbox = document.getElementById('filter-non-project');
        
        if (projectCheckbox && nonProjectCheckbox) {
            // Function to re-render the chart based on current filter state
            function updateChart() {
                const showProject = projectCheckbox.checked;
                const showNonProject = nonProjectCheckbox.checked;
                renderGantt(container, data, year, showProject, showNonProject);
            }
            
            // Add event listeners
            projectCheckbox.addEventListener('change', updateChart);
            nonProjectCheckbox.addEventListener('change', updateChart);
        }
    } else {
        console.error('Project Timeline initialization failed: missing data or container');
    }
});
