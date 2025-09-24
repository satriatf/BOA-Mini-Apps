<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 4; $y <= now()->year + 4; $y++)
                <option value="{{ $y }}" @selected($y == ($year ?? now()->year))>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit">Apply</x-filament::button>
    </form>

    {{-- Legend --}}
    <div class="mb-2 flex items-center gap-4 text-sm">
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#3b82f6;display:inline-block;border-radius:2px"></span> Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Non-Project (MTC)
        </span>
    </div>

    <div id="calendar" class="bg-white rounded-lg shadow p-4"></div>

    @php
        $events   = $this->getEvents();
        $initYear = $year ?? now()->year;
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        #calendar { width: 100%; }
        .fc .fc-daygrid-event { border: 0; }
        .fc .fc-toolbar-title { font-weight: 700; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1,
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                initialDate: '{{ $initYear }}-01-01',
                height: 'auto',
                expandRows: true,
                dayMaxEvents: true,
                events: @json($events),
                eventClick(info) {
                    info.jsEvent.preventDefault();
                    const url = info.event.extendedProps?.url;
                    if (url) window.open(url, '_blank');
                },
            });

            calendar.render();
        });
    </script>
</x-filament-panels::page>
