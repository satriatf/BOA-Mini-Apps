/**
 * Gantt Chart JavaScript
 * Vanilla JS implementation for employee task visualization
 */

// Indonesian month names
const MONTH_NAMES = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

/**
 * Get number of days in a month for a given year
 */
function getDaysInMonth(year, month) {
    return new Date(year, month, 0).getDate();
}

/**
 * Get week buckets for a given month (similar to yearly/monthly)
 * Returns array of week objects with start, end dates and labels
 */
function getMonthWeeks(year, month) {
    const weeks = [];
    const firstDay = new Date(year, month - 1, 1);
    const lastDay = new Date(year, month, 0);
    
    let currentWeek = 1;
    let currentDate = new Date(firstDay);
    
    while (currentDate <= lastDay && currentWeek <= 5) {
        const weekStart = new Date(currentDate);
        const weekEnd = new Date(currentDate);
        
        // Calculate week end (7 days later or end of month)
        weekEnd.setDate(currentDate.getDate() + 6);
        if (weekEnd > lastDay) {
            weekEnd.setTime(lastDay.getTime());
        }
        
        weeks.push({
            start: new Date(weekStart),
            end: new Date(weekEnd),
            label: `Week ${currentWeek}`
        });
        
        // Move to next week
        currentDate.setDate(currentDate.getDate() + 7);
        currentWeek++;
    }
    
    return weeks;
}

/**
 * Check if two date ranges overlap (inclusive)
 */
function overlaps(aStart, aEnd, bStart, bEnd) {
    return aStart <= bEnd && bStart <= aEnd;
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
function renderGantt(container, rows, year) {
    // Clear existing content
    container.innerHTML = '';
    
    // Create tooltip
    const tooltip = createTooltip();
    
    // If no data, show empty state
    if (!rows || rows.length === 0) {
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'gantt-empty';
        emptyDiv.textContent = 'Tidak ada data';
        container.appendChild(emptyDiv);
        return;
    }
    
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
        const weeks = getMonthWeeks(year, month);
        const daysInMonth = getDaysInMonth(year, month);
        const monthHeader = document.createElement('th');
        monthHeader.className = 'month-header';
        monthHeader.textContent = `${MONTH_NAMES[month - 1]} ${daysInMonth}`;
        monthHeader.colSpan = weeks.length;
        monthRow.appendChild(monthHeader);
    }
    
    thead.appendChild(monthRow);
    
    // Row 2: Week headers
    const weekRow = document.createElement('tr');
    for (let month = 1; month <= 12; month++) {
        const weeks = getMonthWeeks(year, month);
        weeks.forEach(week => {
            const weekHeader = document.createElement('th');
            weekHeader.className = 'week-header';
            weekHeader.textContent = week.label;
            weekRow.appendChild(weekHeader);
        });
    }
    
    thead.appendChild(weekRow);
    table.appendChild(thead);
    
    // Create body
    const tbody = document.createElement('tbody');
    
    // Create rows for each employee
    rows.forEach(row => {
        const tr = document.createElement('tr');
        
        // Employee name cell
        const employeeCell = document.createElement('td');
        employeeCell.className = 'employee';
        employeeCell.textContent = row.name;
        tr.appendChild(employeeCell);
        
        // Create week cells for each month
        for (let month = 1; month <= 12; month++) {
            const weeks = getMonthWeeks(year, month);
            
            weeks.forEach(week => {
                const cell = document.createElement('td');
                
                // Find overlapping tasks for this week
                const overlappingTasks = row.tasks.filter(task => {
                    const taskStart = new Date(task.start);
                    const taskEnd = new Date(task.end);
                    return overlaps(taskStart, taskEnd, week.start, week.end);
                });
                
                if (overlappingTasks.length > 0) {
                    // Determine cell class based on task types
                    // Both Technical Lead and PIC use type 'project' -> blue color
                    const hasProject = overlappingTasks.some(task => task.type === 'project');
                    const hasMtc = overlappingTasks.some(task => task.type === 'mtc');
                    
                    if (hasProject && hasMtc) {
                        // Mixed: use project color (blue) as primary
                        cell.className = 'project';
                    } else if (hasProject) {
                        // Technical Lead and PIC both use project color (blue)
                        cell.className = 'project';
                    } else if (hasMtc) {
                        // Resolver and Created By use mtc color (yellow)
                        cell.className = 'mtc';
                    } else {
                        cell.className = 'active';
                    }
                    
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
                
                tr.appendChild(cell);
            });
        }
        
        tbody.appendChild(tr);
    });
    
    table.appendChild(tbody);
    container.appendChild(table);
}

/**
 * Initialize the Gantt chart when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('gantt-root');
    const year = window.GANTT_YEAR;
    const data = window.GANTT_DATA;
    
    if (container && year && data !== undefined) {
        renderGantt(container, data, year);
    } else {
        console.error('Gantt chart initialization failed: missing data or container');
    }
});
