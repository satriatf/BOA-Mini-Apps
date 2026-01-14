<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 3; $y <= now()->year + 5; $y++)
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
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#ef4444;display:inline-block;border-radius:2px"></span> On Leave
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#00ff00;display:inline-block;border-radius:2px"></span> Holiday
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

        /* force full opacity for holiday backgrounds */
        .fc-bg-event { opacity: 1 !important; }

        /* weekend (Sabtu & Minggu) merah: header & nomor tanggal saja, tanpa blok latar */
        .fc-day-sat .fc-col-header-cell-cushion,
        .fc-day-sun .fc-col-header-cell-cushion,
        .fc-day-sat .fc-daygrid-day-number,
        .fc-day-sun .fc-daygrid-day-number {
            color: #ef4444 !important;
        }

        /* overlay detail */
        .fc-detail-overlay{
            position:fixed;inset:0;background:rgba(0,0,0,.35);
            z-index:9999;display:flex;align-items:center;justify-content:center;
        }
        .fc-detail-card{
            background:#fff;border-radius:12px;
            box-shadow:0 20px 40px rgba(0,0,0,.2);
            width:min(860px,92vw);max-height:80vh;overflow:auto;
            padding:18px 20px;color:#111827;font-size:14px;line-height:1.5;
        }
        .fc-detail-actions{margin-top:14px;display:flex;gap:10px;justify-content:flex-end;}
        .fc-btn{padding:8px 12px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
        .fc-btn-primary{background:#2563eb;color:#fff}
        .fc-btn-gray{background:#e5e7eb;color:#111827}
    </style>
 
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            // compute Filament theme primary color for consistent blue link styling
            const root = getComputedStyle(document.documentElement);
            const cssVar = (name, fallback) => (root.getPropertyValue(name).trim() || fallback);
            const colorProject = cssVar('--fi-color-primary-600', '#3b82f6');

            const selectedYear = {{ $initYear }};
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1,
                headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
                initialDate: `${selectedYear}-01-01`,
                validRange: {
                    start: `${selectedYear}-01-01`,
                    end: `${selectedYear + 1}-01-01`, // exclusive end, jadi sampai 31 Dec tahun terpilih
                },
                height: 'auto',
                expandRows: true,
                dayMaxEvents: true,
                events: @json($events),

                // klik => overlay detail
                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    document.querySelectorAll('.fc-detail-overlay').forEach(x => x.remove());

                        const ev   = info.event;
                        const body = ev.extendedProps?.details || '';

                        // use English short month names to match Project Timeline
                        const MONTH_NAMES = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
                        function formatMonthDay(date) {
                            if (!date) return '';
                            const d = new Date(date);
                            if (Number.isNaN(d.getTime())) return '';
                            return `${MONTH_NAMES[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
                        }

                        const s = ev.start ? formatMonthDay(ev.start) : '';
                        const e = ev.end   ? formatMonthDay(new Date(ev.end.getTime()-86400000)) : '';

                    const overlay = document.createElement('div');
                    overlay.className = 'fc-detail-overlay';

                    const card = document.createElement('div');
                    card.className = 'fc-detail-card';

                    // Determine task type and colors (matching Project Timeline)
                    const taskType = ev.extendedProps?.type || 'project';
                    const isHoliday = taskType === 'holiday';
                    const isNonProject = taskType === 'mtc';
                    const isOnLeave = taskType === 'onleave';
                    
                    // Color constants matching gantt.js
                    const PROJECT_COLOR = '#3b82f6';
                    const PROJECT_BADGE_BG = '#dbeafe';
                    const PROJECT_BADGE_COLOR = '#1e40af';
                    const NON_PROJECT_COLOR = '#f59e0b';
                    const NON_PROJECT_BADGE_BG = '#fef3c7';
                    const NON_PROJECT_BADGE_COLOR = '#b45309';
                    const HOLIDAY_COLOR = '#22c55e';
                    const HOLIDAY_BADGE_BG = '#dcfce7';
                    const HOLIDAY_BADGE_COLOR = '#166534';
                    const ONLEAVE_COLOR = '#ef4444';
                    const ONLEAVE_BADGE_BG = '#fee2e2';
                    const ONLEAVE_BADGE_COLOR = '#991b1b';
                    
                    let titleColor, badgeText, badgeBg, badgeColor, dateColor;
                    if (isHoliday) {
                        titleColor = HOLIDAY_COLOR;
                        badgeText = 'Holiday';
                        badgeBg = HOLIDAY_BADGE_BG;
                        badgeColor = HOLIDAY_BADGE_COLOR;
                        dateColor = HOLIDAY_BADGE_COLOR;
                    } else if (isNonProject) {
                        titleColor = NON_PROJECT_COLOR;
                        badgeText = 'Non-Project';
                        badgeBg = NON_PROJECT_BADGE_BG;
                        badgeColor = NON_PROJECT_BADGE_COLOR;
                        dateColor = NON_PROJECT_BADGE_COLOR;
                    } else if (isOnLeave) {
                        titleColor = ONLEAVE_COLOR;
                        badgeText = 'On Leave';
                        badgeBg = ONLEAVE_BADGE_BG;
                        badgeColor = ONLEAVE_BADGE_COLOR;
                        dateColor = ONLEAVE_BADGE_COLOR;
                    } else {
                        titleColor = PROJECT_COLOR;
                        badgeText = 'Project';
                        badgeBg = PROJECT_BADGE_BG;
                        badgeColor = PROJECT_BADGE_COLOR;
                        dateColor = PROJECT_BADGE_COLOR;
                    }
                    
                    // Title with badge
                    const titleWrap = document.createElement('div');
                    titleWrap.style.cssText = 'font-size:16px;font-weight:700;margin-bottom:8px;color:' + titleColor + ';';
                    const detailTitle = ev.extendedProps?.detailTitle || ev.title || 'Detail';
                    
                    if (ev.extendedProps?.url) {
                        const a = document.createElement('a');
                        a.href = ev.extendedProps.url;
                        a.target = '_blank';
                        a.style.cssText = 'color:' + titleColor + ';text-decoration:none;font-weight:700;';
                        a.textContent = detailTitle;
                        titleWrap.appendChild(a);
                    } else {
                        titleWrap.textContent = detailTitle;
                    }
                    
                    // Add badge
                    const badge = document.createElement('span');
                    badge.textContent = badgeText;
                    badge.style.cssText = 'margin-left:8px;padding:2px 6px;border-radius:4px;background:' + badgeBg + ';color:' + badgeColor + ';font-size:11px;font-weight:700;';
                    titleWrap.appendChild(badge);

                    const when = document.createElement('div');
                    when.style.cssText = 'color:' + dateColor + ';margin-bottom:10px;font-size:12px;';
                    when.textContent = s + (e ? ' — ' + e : '');

                    const content = document.createElement('div');
                    content.innerHTML = body;

                    const actions = document.createElement('div');
                    actions.className = 'fc-detail-actions';

                    if (ev.extendedProps?.url) {
                        const edit = document.createElement('a');
                        edit.href = ev.extendedProps.url;
                        edit.target = '_blank';
                        edit.className = 'fc-btn fc-btn-primary';
                        edit.textContent = 'Edit';
                        actions.appendChild(edit);
                    }

                    const close = document.createElement('button');
                    close.type = 'button';
                    close.className = 'fc-btn fc-btn-gray';
                    close.textContent = 'Close';
                    close.onclick = () => overlay.remove();
                    actions.appendChild(close);

                    card.appendChild(titleWrap);
                    card.appendChild(when);
                    card.appendChild(content);
                    card.appendChild(actions);
                    overlay.appendChild(card);
                    document.body.appendChild(overlay);

                    overlay.addEventListener('click',(e)=>{ if(e.target===overlay) overlay.remove(); });
                    document.addEventListener('keydown', function esc(e){
                        if(e.key==='Escape'){ overlay.remove(); document.removeEventListener('keydown', esc); }
                    });
                },
            });

            calendar.render();

            // Fix initial layout where Sunday header kadang tidak muncul sampai navigasi.
            // Paksa recalculation setelah render dan saat resize.
            setTimeout(() => calendar.updateSize(), 0);
            window.addEventListener('resize', () => calendar.updateSize());
        });
    </script>
</x-filament-panels::page>
