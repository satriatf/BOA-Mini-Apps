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

    {{-- Legend warna --}}
    <div class="mb-2 flex items-center gap-4 text-sm">
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#3b82f6;display:inline-block;border-radius:2px"></span> Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Non-Project (MTC)
        </span>
    </div>

    {{-- Kalender --}}
    <div id="calendar" class="bg-white rounded-lg shadow p-4"></div>

    @php
        $events   = $this->getEvents();
        $initYear = $year ?? now()->year;
    @endphp

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <style>
        /* Kontainer */
        #calendar { width: 100%; }

        /* Rapikan event (hilangkan border bawaan) */
        .fc .fc-daygrid-event { border: 0; }

        /* Judul toolbar */
        .fc .fc-toolbar-title { font-weight: 700; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                firstDay: 1,
                weekends: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                initialDate: '{{ $initYear }}-01-01',
                height: 'auto',
                contentHeight: 'auto',
                expandRows: true,
                dayMaxEvents: true,
                events: @json($events),

                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    document.querySelectorAll('.fc-detail-overlay').forEach(x => x.remove());

                    const ev   = info.event;
                    const body = ev.extendedProps.details || '';

                    const overlay = document.createElement('div');
                    overlay.className = 'fc-detail-overlay';
                    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:9999;display:flex;align-items:center;justify-content:center;';

                    const card = document.createElement('div');
                    card.style.cssText = 'background:#fff;border-radius:12px;box-shadow:0 20px 40px rgba(0,0,0,.2);width:min(860px,92vw);max-height:80vh;overflow:auto;padding:18px 20px;color:#111827;font-size:14px;line-height:1.5;';

                    const title = document.createElement('div');
                    title.style.cssText = 'font-size:16px;font-weight:700;margin-bottom:8px;';
                    title.textContent = ev.title || 'Detail';

                    const when = document.createElement('div');
                    when.style.cssText = 'color:#4b5563;margin-bottom:10px;';
                    const s = ev.start ? ev.start.toLocaleDateString() : '';
                    const e = ev.end   ? new Date(ev.end.getTime()-86400000).toLocaleDateString() : '';
                    when.textContent = s + (e ? ' — ' + e : '');

                    const content = document.createElement('div');
                    content.innerHTML = body;

                    const actions = document.createElement('div');
                    actions.style.cssText = 'margin-top:14px;display:flex;gap:10px;justify-content:flex-end;';

                    if (ev.url) {
                        const edit = document.createElement('a');
                        edit.href = ev.url;
                        edit.target = '_blank';
                        edit.textContent = 'Edit';
                        edit.style.cssText = 'background:#2563eb;color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;';
                        actions.appendChild(edit);
                    }

                    const close = document.createElement('button');
                    close.type='button';
                    close.textContent='Close';
                    close.style.cssText='background:#e5e7eb;padding:8px 12px;border-radius:8px;';
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
                }
            });

            // Render setelah layout stabil
            requestAnimationFrame(() => setTimeout(() => {
                calendar.render();
                calendar.updateSize();
            }, 80));

            window.addEventListener('resize', () => calendar.updateSize());
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(() => calendar.updateSize());
            }
            const ro = new ResizeObserver(() => calendar.updateSize());
            ro.observe(calendarEl.parentElement);
            document.addEventListener('livewire:navigated', () => calendar.updateSize());
            document.addEventListener('filament-theme-changed', () => calendar.updateSize());
        });
    </script>
</x-filament-panels::page>
