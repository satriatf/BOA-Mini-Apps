<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 5; $y <= now()->year + 2; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit">Apply</x-filament::button>
    </form>

    {{-- FullCalendar CDN --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

    <div id="calendar" class="rounded-lg border bg-white dark:bg-gray-900 p-2"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            // Ambil warna dari theme Filament
            const root = getComputedStyle(document.documentElement);
            const cssVar = (name, fallback) => (root.getPropertyValue(name).trim() || fallback);

            // Warna mengikuti theme: primary utk Projects, warning utk MTC
            const colorProject = cssVar('--fi-color-primary-600', '#3b82f6');
            const colorMtc     = cssVar('--fi-color-warning-600', '#f59e0b');

            const events = @json($events);

            // Set warna berdasarkan tipe
            const coloredEvents = events.map(e => {
                const type = e.extendedProps?.type;
                if (type === 'project') {
                    e.color = colorProject;
                    e.textColor = '#ffffff';
                } else if (type === 'mtc') {
                    e.color = colorMtc;
                    e.textColor = '#1f2937'; // gray-800
                }
                return e;
            });

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: '{{ $year }}-01-01',
                height: 'auto',
                dayMaxEvents: true,
                fixedWeekCount: false,
                expandRows: true,
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                validRange: {
                    start: '{{ $year }}-01-01',
                    end:   '{{ $year + 1 }}-01-01', // exclusive
                },
                events: coloredEvents,

                // Tooltip ringan via title attribute
                eventDidMount(info) {
                    const details = info.event.extendedProps?.details;
                    if (details) info.el.title = details;
                },

                // Klik event → buka halaman Edit resource
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

        /* Dark mode overrides for FullCalendar */
        html.dark #calendar,
        html.dark .fc,
        html.dark .fc-theme-standard {
            background: #18181b !important; /* dark bg */
            color: #f3f4f6 !important; /* light text */
        }
        html.dark .fc .fc-toolbar,
        html.dark .fc .fc-toolbar-title,
        html.dark .fc .fc-button,
        html.dark .fc .fc-button-primary {
            background: #23272f !important;
            color: #f3f4f6 !important;
            border-color: #374151 !important;
        }
        html.dark .fc .fc-button-primary:not(:disabled):hover,
        html.dark .fc .fc-button-primary:focus {
            background: #374151 !important;
        }
        html.dark .fc-theme-standard .fc-scrollgrid,
        html.dark .fc-theme-standard td,
        html.dark .fc-theme-standard th {
            background: #23272f !important;
            border-color: rgba(243,244,246,0.12) !important; /* light border */
        }
        html.dark .fc-daygrid-day-number,
        html.dark .fc-col-header-cell-cushion {
            color: #f3f4f6 !important;
        }
        html.dark .fc-daygrid-day.fc-day-today {
            background: #334155 !important;
        }
        html.dark .fc-event {
            background: #3b82f6 !important;
            color: #fff !important;
            border: none !important;
        }
        html.dark .fc-event.fc-event-mtc {
            background: #f59e0b !important;
            color: #1f2937 !important;
        }
        html.dark .fc-popover {
            background: #23272f !important;
            color: #f3f4f6 !important;
        }
        html.dark .fc .fc-daygrid-day-frame {
            background: transparent !important;
        }
        html.dark .fc .fc-daygrid-day {
            background: #23272f !important;
        }
        html.dark .fc .fc-col-header-cell {
            background: #23272f !important;
        }
        html.dark .fc .fc-scrollgrid-section-header td {
            background: #23272f !important;
        }
        html.dark .fc .fc-scrollgrid-section-body td {
            background: #23272f !important;
        }
        html.dark .fc .fc-daygrid-day.fc-day-other {
            background: #18181b !important;
            color: #6b7280 !important;
        }
        /* End dark mode overrides */
    </style>
</x-filament-panels::page>