<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 3; $y <= now()->year + 5; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit">Apply</x-filament::button>
    </form>

    {{-- Legend warna (konsisten dgn Monthly) --}}
    <div class="mb-2 flex items-center gap-4 text-sm">
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#3b82f6;display:inline-block;border-radius:2px"></span> Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Non-Project (MTC)
        </span>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.19/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/multimonth@6.1.19/index.global.min.js"></script>

    <div id="calendar" class="rounded-lg border bg-white dark:bg-gray-900 p-2"></div>

    <style>
        #calendar { width: 100%; }
        .fc .fc-toolbar-title { font-weight: 700; }
        .fc-theme-standard .fc-scrollgrid,
        .fc-theme-standard td,
        .fc-theme-standard th { border-color: rgba(107,114,128,.2); }

        /* overlay detail (tanpa Edit, hanya Close) */
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
        .fc-btn-gray{background:#e5e7eb;color:#111827}
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('calendar');

            // ambil warna dari theme filament (fallback biru/kuning)
            const root = getComputedStyle(document.documentElement);
            const cssVar = (name, fallback) => (root.getPropertyValue(name).trim() || fallback);
            const colorProject = cssVar('--fi-color-primary-600', '#3b82f6'); // biru
            const colorMtc     = cssVar('--fi-color-warning-600', '#f59e0b'); // kuning

            // warnai event sesuai type
            const events = @json($events).map(e => {
                const type = e.extendedProps?.type;
                if (type === 'project') {
                    e.backgroundColor = colorProject;
                    e.borderColor     = colorProject;
                    e.textColor       = '#ffffff';
                } else if (type === 'mtc') {
                    e.backgroundColor = colorMtc;
                    e.borderColor     = colorMtc;
                    e.textColor       = '#1f2937';
                }
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

                // Klik = tampilkan popup vertikal (sama seperti Monthly)
                eventClick(info) {
                    info.jsEvent.preventDefault();

                    document.querySelectorAll('.fc-detail-overlay').forEach(x => x.remove());

                    const ev   = info.event;
                    const body = ev.extendedProps?.details || '';
                    const s = ev.start ? ev.start.toLocaleDateString('id-ID') : '';
                    const e = ev.end   ? new Date(ev.end.getTime()-86400000).toLocaleDateString('id-ID') : '';

                    const overlay = document.createElement('div');
                    overlay.className = 'fc-detail-overlay';

                    const card = document.createElement('div');
                    card.className = 'fc-detail-card';

                    const title = document.createElement('div');
                    title.style.cssText = 'font-size:16px;font-weight:700;margin-bottom:8px;';
                    title.textContent = ev.title || 'Detail';

                    const when = document.createElement('div');
                    when.style.cssText = 'color:#4b5563;margin-bottom:10px;';
                    when.textContent = s + (e ? ' — ' + e : '');

                    const content = document.createElement('div');
                    content.innerHTML = body;

                    const actions = document.createElement('div');
                    actions.className = 'fc-detail-actions';

                    const close = document.createElement('button');
                    close.type = 'button';
                    close.className = 'fc-btn fc-btn-gray';
                    close.textContent = 'Close';
                    close.onclick = () => overlay.remove();
                    actions.appendChild(close);

                    card.appendChild(title);
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
        });
    </script>
</x-filament-panels::page>
