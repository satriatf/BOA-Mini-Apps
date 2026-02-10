<x-filament-panels::page>
    <style>
        /* Force inherit global Filament fonts */
        .report-dashboard, .fi-section, .fi-card { font-family: inherit !important; }
        
        .chart-main-row { display: flex; align-items: flex-start; gap: 40px; width: 100%; padding: 20px 0; }
        .chart-visual-container { position: relative; width: 380px; height: 380px; flex-shrink: 0; }
        
        .chart-center-overlay {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            align-items: center; justify-content: center; pointer-events: none;
            background: white; border-radius: 50%; width: 58%; height: 58%; margin: auto;
            z-index: 5; box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        }
        .pct-main { font-size: 4rem; font-weight: 800; color: #2563eb; line-height: 1; transition: all 0.3s ease; }
        .lbl-sub { font-size: 0.9rem; font-weight: 700; color: #64748b; margin-top: 6px; text-align: center; max-width: 150px; text-transform: uppercase; letter-spacing: 0.5px; }

        .legend-list { flex: 1; display: flex; flex-direction: column; gap: 0; margin-top: 5px; }
        .legend-item { 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 12px 16px; border-radius: 8px; cursor: pointer; transition: all 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }
        .legend-item:hover { background: #f8fafc; }
        .legend-item.active { background: #eff6ff; border-left: 4px solid #3b82f6; }
        
        .legend-info { display: flex; align-items: center; gap: 14px; }
        .legend-color { width: 14px; height: 14px; border-radius: 3px; flex-shrink: 0; }
        .legend-label { font-size: 0.9rem; font-weight: 600; color: #475569; }
        .legend-value { font-size: 0.95rem; font-weight: 800; color: #1e293b; }

        /* JIRA Premium Tooltip */
        #jira-custom-tooltip {
            position: absolute; background: white; border-radius: 8px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0; padding: 16px; z-index: 9999; 
            pointer-events: none; opacity: 0; transition: opacity 0.2s; 
            min-width: 220px;
        }
        #jira-custom-tooltip h4 { font-size: 1.15rem; font-weight: 800; margin: 0 0 8px 0; color: #0f172a; }
        #jira-custom-tooltip p { font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-bottom: 12px; }
        #jira-custom-tooltip a { font-size: 0.7rem; color: #2563eb; text-decoration: none; font-weight: 700; display: flex; align-items: center; gap: 4px; pointer-events: auto; }

        /* Tables & UI Components */
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.8rem; }
        .premium-table th { padding: 12px 16px; font-weight: 700; color: #64748b; border-bottom: 1px solid #e2e8f0; text-align: left; background: #f8fafc; text-transform: uppercase; }
        .premium-table td { padding: 12px 16px; border-bottom: 1px solid #f8fafc; color: #1e293b; }
        .premium-table tr:hover td { background: #eff6ff/40; }
        
        .progress-bar-bg { background: #f1f5f9; border-radius: 999px; height: 7px; width: 100%; overflow: hidden; position: relative; }
        .progress-bar-fill { background: #3b82f6; height: 100%; border-radius: 999px; transition: width 0.8s ease; }
        
        select.fi-select-input { font-weight: 700 !important; cursor: pointer; }
    </style>

    <div class="report-dashboard space-y-6">
        
        {{-- FILTER SECTION --}}
        <x-filament::section>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 0.875rem; font-weight: 800; color: #111827; text-transform: uppercase;">Report Filter</h2>
                <form method="GET" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; background: #fff; border: 2px solid #e5e7eb; border-radius: 0.5rem; padding: 0.4rem 0.75rem;">
                        <select name="startMonth" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == ($startMonth ?? 1))>{{ \Carbon\Carbon::create(2000, $m, 1)->format('M') }}</option>
                            @endforeach
                        </select>
                        <select name="startYear" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @for($y = now()->year - 3; $y <= now()->year + 5; $y++)
                                <option value="{{ $y }}" @selected($y == ($startYear ?? now()->year))>{{ $y }}</option>
                            @endfor
                        </select>
                        <span style="color: #cbd5e1; margin: 0 0.75rem; font-weight: 900;">—</span>
                        <select name="endMonth" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == ($endMonth ?? now()->month))>{{ \Carbon\Carbon::create(2000, $m, 1)->format('M') }}</option>
                            @endforeach
                        </select>
                        <select name="endYear" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @for($y = now()->year - 3; $y <= now()->year + 5; $y++)
                                <option value="{{ $y }}" @selected($y == ($endYear ?? now()->year))>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-funnel" size="sm" style="font-weight: 800; text-transform: uppercase;">
                        Apply
                    </x-filament::button>
                </form>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            {{-- WORKLOAD CHART (Jira-Style Enhanced) --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Project Status Distribution</span>
                        <div style="display: flex; gap: 8px;">
                            <x-heroicon-o-arrows-pointing-out style="width: 14px; height: 14px; color: #94a3b8;" />
                            <x-heroicon-o-arrow-path style="width: 14px; height: 14px; color: #94a3b8;" />
                        </div>
                    </div>
                </x-slot>

                <div x-data="{ 
                    activeIndex: -1, 
                    hoverIndex: -1,
                    labels: @json($reportData['workload']['labels']),
                    data: @json($reportData['workload']['data']),
                    total: {{ $reportData['workload']['total'] }},
                    get currentLabel() { 
                        let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                        return idx !== -1 ? this.labels[idx] : 'Total Tickets';
                    },
                    get currentPct() {
                        let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                        if (idx === -1) return this.total;
                        return Math.round((this.data[idx] / this.total) * 100) + '%';
                    }
                }" class="w-full">
                    
                    <div class="mb-4">
                        <h3 style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">Project Status</h3>
                        <p style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Total Projects: {{ $reportData['workload']['total'] }}</p>
                    </div>

                    <div class="chart-main-row flex-col xl:flex-row items-center xl:items-start">
                        <div class="chart-visual-container">
                            <canvas id="workloadJiraDonut"></canvas>
                            <div class="chart-center-overlay">
                                <div class="pct-main" x-text="currentPct"></div>
                                <div class="lbl-sub" x-text="currentLabel"></div>
                            </div>
                        </div>

                        <div class="legend-list w-full">
                            @php $colors = ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6', '#f43f5e', '#a855f7']; @endphp
                            @foreach($reportData['workload']['labels'] as $idx => $label)
                                @php $pct = $reportData['workload']['total'] > 0 ? round(($reportData['workload']['data'][$idx] / $reportData['workload']['total']) * 100) : 0; @endphp
                                <div class="legend-item" 
                                     :class="{ 'active': activeIndex === {{ $idx }} || hoverIndex === {{ $idx }} }"
                                     @mouseenter="hoverIndex = {{ $idx }}" 
                                     @mouseleave="hoverIndex = -1"
                                     @click="activeIndex = (activeIndex === {{ $idx }} ? -1 : {{ $idx }})">
                                    <div class="legend-info">
                                        <div class="legend-color" style="background: {{ $colors[$idx % count($colors)] }}"></div>
                                        <span class="legend-label">{{ $label }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8;">{{ $pct }}%</span>
                                        <span class="legend-value">{{ $reportData['workload']['data'][$idx] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <div style="display: flex; flex-direction: column; gap: 24px;">
                {{-- PROBLEMS TABLE --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase;">Application Problems Monthly</span>
                    </x-slot>
                    <table class="premium-table">
                        <thead>
                            <tr><th>App Name</th><th>Issues</th><th>T:</th></tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['problems'] as $app => $count)
                                <tr>
                                    <td style="font-weight: 600;">{{ $app }}</td>
                                    <td style="color: #3b82f6; font-weight: 800;">{{ $count }}</td>
                                    <td style="background: #eff6ff; color: #2563eb; font-weight: 900;">{{ $count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if(count($reportData['problems']) > 0)
                        <tfoot>
                            <tr style="background: #f8fafc; font-weight: 800;">
                                <td style="border-top: 2px solid #e2e8f0;">Total Unique:</td>
                                <td style="border-top: 2px solid #e2e8f0; color: #3b82f6;">{{ array_sum($reportData['problems']) }}</td>
                                <td style="border-top: 2px solid #e2e8f0; color: #2563eb; background: #eff6ff;">{{ array_sum($reportData['problems']) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </x-filament::section>

                {{-- ACTIVITY PERCENTAGE --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase;">Activity Percentage Monthly</span>
                    </x-slot>
                    <table class="premium-table">
                        <thead>
                            <tr><th>Activity</th><th style="text-align: center;">Count</th><th>Share</th></tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['activities'] as $type => $count)
                                @php $pct = $reportData['totalActivity'] > 0 ? round(($count / $reportData['totalActivity']) * 100) : 0; @endphp
                                <tr>
                                    <td style="color: #3b82f6; font-weight: 800; text-transform: uppercase;">{{ $type }}</td>
                                    <td style="font-weight: 700; text-align: center;">{{ $count }}</td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: {{ $pct }}%"></div></div>
                                            <span style="font-size: 10px; font-weight: 900; color: #64748b; min-width: 30px; text-align: right;">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-filament::section>
            </div>
        </div>
    </div>

    {{-- TOOLTIP ELEMENT --}}
    <div id="jira-custom-tooltip">
        <h4 id="tt-name">Application</h4>
        <p>Tickets: <span id="tt-detail" style="color: #0f172a;">0 (0%)</span></p>
        <a href="#">
            View in Issue navigator
            <x-heroicon-s-arrow-top-right-on-square style="width: 12px; height: 12px;" />
        </a>
    </div>

    @once
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('workloadJiraDonut');
            const ttEl = document.getElementById('jira-custom-tooltip');
            const ttName = document.getElementById('tt-name');
            const ttDetail = document.getElementById('tt-detail');

            if (!ctx) return;

            const labels = @json($reportData['workload']['labels']);
            const dataValues = @json($reportData['workload']['data']);
            const colors = ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899', '#6366f1', '#14b8a6', '#f43f5e', '#a855f7'];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: dataValues,
                        backgroundColor: colors,
                        hoverBackgroundColor: colors.map(c => c + 'ee'),
                        borderWidth: 0,
                        hoverOffset: 25,
                        spacing: 2
                    }]
                },
                options: {
                    cutout: '72%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false }, 
                        tooltip: { 
                            enabled: false,
                            external: function(context) {
                                const { chart, tooltip } = context;
                                
                                if (tooltip.opacity === 0) {
                                    ttEl.style.opacity = '0';
                                    return;
                                }

                                const idx = tooltip.dataPoints[0].dataIndex;
                                const label = labels[idx];
                                const value = dataValues[idx];
                                const total = dataValues.reduce((a, b) => a + b, 0);
                                const pct = Math.round((value / total) * 100);

                                ttName.innerText = label;
                                ttDetail.innerText = `${value} Projects (${pct}%)`;

                                const pos = chart.canvas.getBoundingClientRect();
                                ttEl.style.opacity = '1';
                                ttEl.style.left = (pos.left + window.pageXOffset + tooltip.caretX + 20) + 'px';
                                ttEl.style.top = (pos.top + window.pageYOffset + tooltip.caretY - 100) + 'px';
                            }
                        } 
                    },
                    onHover: (event, elements) => {
                        const alpine = document.querySelector('[x-data]').__x.$data;
                        if (elements.length > 0) {
                            alpine.hoverIndex = elements[0].index;
                            event.native.target.style.cursor = 'pointer';
                        } else {
                            alpine.hoverIndex = -1;
                            event.native.target.style.cursor = 'default';
                        }
                    },
                    onClick: (event, elements) => {
                        const alpine = document.querySelector('[x-data]').__x.$data;
                        if (elements.length > 0) {
                            const idx = elements[0].index;
                            alpine.activeIndex = (alpine.activeIndex === idx ? -1 : idx);
                        }
                    }
                }
            });
        });
    </script>
    @endonce
</x-filament-panels::page>
