<x-filament-panels::page>
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 5; $y <= now()->year + 2; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit">Apply</x-filament::button>
    </form>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.19/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.19/index.global.min.js"></script>

    <div id="calendar" class="rounded-lg border bg-white dark:bg-gray-900 p-2"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('calendar');

            const root = getComputedStyle(document.documentElement);
            const cssVar = (name, fallback) => (root.getPropertyValue(name).trim() || fallback);
            const colorProject = cssVar('--fi-color-primary-600', '#3b82f6');
            const colorMtc     = cssVar('--fi-color-warning-600', '#f59e0b');

            const events = @json($events).map(e => {
                const type = e.extendedProps?.type;
                if (type === 'project') { e.color = colorProject; e.textColor = '#ffffff'; }
                if (type === 'mtc')     { e.color = colorMtc;     e.textColor = '#1f2937'; }
                return e;
            });

            const Y = {{ (int) $year }};

            const calendar = new FullCalendar.Calendar(el, {
                initialView: 'multiMonthYear',
                multiMonthMaxColumns: 3,
                height: 'auto',
                dayMaxEvents: true,
                headerToolbar: { left: '', center: 'title', right: '' },
                validRange: { start: `${Y}-01-01`, end: `${Y + 1}-01-01` },
                locale: 'id',
                firstDay: 1,
                fixedWeekCount: false,
                expandRows: true,
                events,
                eventDidMount(info) {
                    const details = info.event.extendedProps?.details;
                    if (details) info.el.title = details;
                },
                eventClick(info) {
                    const url = info.event.extendedProps?.url;
                    if (url) window.open(url, '_blank');
                },
            });

            calendar.render();
        });
    </script>

    <style>
        #calendar { width: 100%; }
        .fc .fc-toolbar-title { font-weight: 700; }
        .fc-theme-standard .fc-scrollgrid,
        .fc-theme-standard td,
        .fc-theme-standard th { border-color: rgba(107,114,128,.2); }
    </style>
</x-filament-panels::page>
