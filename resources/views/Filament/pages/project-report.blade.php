<x-filament-panels::page>
    <style>
        /* Force inherit global Filament fonts */
        .report-dashboard, .fi-section, .fi-card { font-family: inherit !important; }
        
        .chart-main-row { display: flex; align-items: flex-start; gap: 40px; width: 100%; padding: 20px 0; }
        .chart-visual-container { position: relative; width: 380px; height: 380px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .chart-visual-container canvas { position: relative; z-index: 1; }
        
        .chart-center-overlay {
            position: absolute; display: flex; flex-direction: column;
            align-items: center; justify-content: center; pointer-events: none;
            background: transparent; border-radius: 50%; width: 50%; height: 50%;
            z-index: 10;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            top: 50%; left: 50%; transform: translate(-50%, -50%);
        }
        .pct-main { font-size: 2.5rem; font-weight: 900; color: #0f172a; line-height: 0.9; transition: all 0.3s ease; }
        .lbl-sub { font-size: 0.8rem; font-weight: 800; color: #94a3b8; margin-top: 8px; text-align: center; max-width: 140px; text-transform: uppercase; letter-spacing: 1.5px; }

        .legend-list { flex: 1; display: flex; flex-direction: column; gap: 4px; }
        .legend-item { 
            display: flex; align-items: center; justify-content: space-between; 
            padding: 10px 16px; border-radius: 10px; cursor: pointer; transition: all 0.2s;
            border-bottom: 1px solid transparent;
        }
        .legend-item:hover { background: #f8fafc; }
        .legend-item.active-item { background: #eff6ff; box-shadow: 0 2px 8px -2px rgba(59, 130, 246, 0.1); }
        
        .legend-info { display: flex; align-items: center; gap: 14px; }
        .legend-color { width: 12px; height: 12px; border-radius: 4px; flex-shrink: 0; }
        .legend-label { font-size: 0.9rem; font-weight: 700; color: #475569; }
        .legend-value { font-size: 1rem; font-weight: 800; color: #1e293b; }

        /* JIRA Premium Tooltip */
        #jira-custom-tooltip {
            position: absolute; background: white; border-radius: 8px; 
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0; padding: 14px; z-index: 9999; 
            pointer-events: none; opacity: 0; transition: opacity 0.2s; 
            min-width: 180px;
        }
        #jira-custom-tooltip h4 { font-size: 1rem; font-weight: 800; margin: 0 0 4px 0; color: #0f172a; }
        #jira-custom-tooltip p { font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 0.8rem; }
        .premium-table th { padding: 12px 16px; font-weight: 700; color: #64748b; border-bottom: 1px solid #e2e8f0; text-align: left; background: #f8fafc; text-transform: uppercase; }
        .premium-table td { padding: 12px 16px; border-bottom: 1px solid #f8fafc; color: #1e293b; }
        .progress-bar-bg { background: #f1f5f9; border-radius: 999px; height: 7px; width: 100%; overflow: hidden; position: relative; }
        .progress-bar-fill { background: #3b82f6; height: 100%; border-radius: 999px; transition: width 0.8s ease; }
    </style>

    <div class="report-dashboard" style="display: flex; flex-direction: column; gap: 24px;">
        
        {{-- FILTER SECTION --}}
        <x-filament::section>
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 0.875rem; font-weight: 800; color: #111827; text-transform: uppercase;">Report Filter</h2>
                <form method="GET" style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; background: #fff; border: 2px solid #e5e7eb; border-radius: 0.5rem; padding: 0.4rem 0.75rem;">
                        <select name="startMonth" id="startMonth" onchange="validateDates()" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == $startMonth)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('M') }}</option>
                            @endforeach
                        </select>
                        <select name="startYear" id="startYear" onchange="validateDates()" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @for($y = 2023; $y <= 2030; $y++)
                                <option value="{{ $y }}" @selected($y == $startYear)>{{ $y }}</option>
                            @endfor
                        </select>
                        <span style="color: #cbd5e1; margin: 0 0.75rem; font-weight: 900;">—</span>
                        <select name="endMonth" id="endMonth" onchange="validateDates()" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" @selected($m == $endMonth)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('M') }}</option>
                            @endforeach
                        </select>
                        <select name="endYear" id="endYear" onchange="validateDates()" style="background: transparent; border: none; font-size: 0.8rem; font-weight: 700; color: #111827; outline: none;">
                            @for($y = 2023; $y <= 2030; $y++)
                                <option value="{{ $y }}" @selected($y == $endYear)>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <script>
                        function validateDates() {
                            const sm = parseInt(document.getElementById('startMonth').value);
                            const sy = parseInt(document.getElementById('startYear').value);
                            const em = parseInt(document.getElementById('endMonth').value);
                            const ey = parseInt(document.getElementById('endYear').value);

                            const startVal = sy * 100 + sm;
                            const endVal = ey * 100 + em;

                            if (endVal < startVal) {
                                document.getElementById('endMonth').value = sm;
                                document.getElementById('endYear').value = sy;
                            }
                        }
                    </script>
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-funnel" size="sm" style="font-weight: 800; text-transform: uppercase;">
                        Apply
                    </x-filament::button>
                </form>
            </div>
        </x-filament::section>

        <div style="display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 24px;" class="lg:grid-cols-2">
            
            {{-- WORKLOAD CHART (Jira-Style Enhanced) --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                        <span style="font-size: 0.875rem; font-weight: 800; color: #111827; text-transform: uppercase;">Project Workload</span>
                    </div>
                </x-slot>

                <div id="workload-component" x-data="workloadChartComponent" class="w-full">
                    
                    <div class="mb-4">
                        <h3 style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">Application Name</h3>
                        <p style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Total Issues: <span style="font-weight: 900; color: #0f172a;">{{ $reportData['workload']['total'] }}</span></p>
                    </div>

                    <div class="chart-main-row flex-col xl:flex-row items-center xl:items-start">
                        <div class="chart-visual-container">
                            <canvas id="workloadJiraDonut" style="width: 100%; height: 100%;"></canvas>
                            <div class="chart-center-overlay" :style="hoverIndex !== -1 ? 'transform: translate(-50%, -50%) scale(1.05);' : 'transform: translate(-50%, -50%);'">
                                <div class="pct-main" x-text="currentPct" :style="hoverIndex !== -1 ? 'color: ' + currentColor + '; font-size: 3rem;' : ''"></div>
                                <div class="lbl-sub" x-text="currentLabel" :style="hoverIndex !== -1 ? 'color: ' + currentColor : ''"></div>
                            </div>
                        </div>

                        <div class="legend-list w-full">
                            @foreach($reportData['workload']['labels'] as $idx => $label)
                                @php 
                                    $pct = $reportData['workload']['total'] > 0 ? round(($reportData['workload']['data'][$idx] / $reportData['workload']['total']) * 100) : 0; 
                                    $color = $reportData['workload']['colors'][$idx] ?? '#cccccc';
                                @endphp
                                <div class="legend-item" 
                                     :class="{ 'active-item': activeIndex === {{ $idx }} || hoverIndex === {{ $idx }} }"
                                     @mouseenter="setHover({{ $idx }})" 
                                     @mouseleave="setHover(-1)"
                                     @click="activeIndex = (activeIndex === {{ $idx }} ? -1 : {{ $idx }})">
                                    <div class="legend-info">
                                        <div class="legend-color" style="background: {{ $color }}"></div>
                                        <span class="legend-label">{{ $label }}</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        {{-- <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8;">{{ $pct }}%</span> --}}
                                        <span class="legend-value">{{ $reportData['workload']['data'][$idx] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-filament::section>

            <div style="display: flex; flex-direction: column; gap: 24px;">
                {{-- TABLES... --}}
                {{-- NON-PROJECT WORKLOAD CHART --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            <span style="font-size: 0.875rem; font-weight: 800; color: #111827; text-transform: uppercase;">Non-Project Workload</span>
                        </div>
                    </x-slot>
    
                    <div id="nonproject-workload-component" 
                         x-data="nonProjectWorkloadChartComponent" 
                        @chart-hover="hoverIndex = $event.detail.index"
                        @chart-click="activeIndex = (activeIndex === $event.detail.index ? -1 : $event.detail.index)"
                        class="w-full">
                        
                        <div class="mb-4">
                            <h3 style="font-size: 1.5rem; font-weight: 800; color: #0f172a;">Application Name</h3>
                            <p style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #f1f5f9; padding-bottom: 10px;">Total Issues: <span style="font-weight: 900; color: #0f172a;">{{ $reportData['nonProjectWorkload']['total'] }}</span></p>
                        </div>
    
                        <div class="chart-main-row flex-col xl:flex-row items-center xl:items-start">
                            <div class="chart-visual-container">
                                <canvas id="nonprojectWorkloadJiraDonut" style="width: 100%; height: 100%;"></canvas>
                                <div class="chart-center-overlay" :style="hoverIndex !== -1 ? 'transform: translate(-50%, -50%) scale(1.05);' : 'transform: translate(-50%, -50%);'">
                                    <div class="pct-main" x-text="currentPct" :style="hoverIndex !== -1 ? 'color: ' + currentColor + '; font-size: 3rem;' : ''"></div>
                                    <div class="lbl-sub" x-text="currentLabel" :style="hoverIndex !== -1 ? 'color: ' + currentColor : ''"></div>
                                </div>
                            </div>
    
                            <div class="legend-list w-full">
                                @foreach($reportData['nonProjectWorkload']['labels'] as $idx => $label)
                                    @php 
                                        $pct = $reportData['nonProjectWorkload']['total'] > 0 ? round(($reportData['nonProjectWorkload']['data'][$idx] / $reportData['nonProjectWorkload']['total']) * 100) : 0; 
                                        $color = $reportData['nonProjectWorkload']['colors'][$idx] ?? '#cccccc';
                                    @endphp
                                    <div class="legend-item" 
                                         :class="{ 'active-item': activeIndex === {{ $idx }} || hoverIndex === {{ $idx }} }"
                                         @mouseenter="setHover({{ $idx }})" 
                                         @mouseleave="setHover(-1)"
                                         @click="activeIndex = (activeIndex === {{ $idx }} ? -1 : {{ $idx }})">
                                        <div class="legend-info">
                                            <div class="legend-color" style="background: {{ $color }}"></div>
                                            <span class="legend-label">{{ $label }}</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span class="legend-value">{{ $reportData['nonProjectWorkload']['data'][$idx] }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-filament::section>

                {{-- ACTIVITY PERCENTAGE --}}

            </div>
        </div>
    </div>

    {{-- TOOLTIP ELEMENT --}}
    <div id="jira-custom-tooltip">
        <h4 id="tt-name">Application</h4>
        <p id="tt-detail">0 Issues (0%)</p>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('workloadChartComponent', () => ({
                activeIndex: -1, 
                hoverIndex: -1,
                labels: @json($reportData['workload']['labels']),
                data: @json($reportData['workload']['data']),
                total: {{ $reportData['workload']['total'] }},
                colors: @json($reportData['workload']['colors']),
                get currentLabel() { 
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    return idx !== -1 ? this.labels[idx] : 'Project';
                },
                get currentPct() {
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    if (idx === -1) return this.total;
                    if (this.total === 0) return '0%';
                    return Math.round((this.data[idx] / this.total) * 100) + '%';
                },
                get currentColor() {
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    if (idx !== -1) return this.colors[idx % this.colors.length];
                    return '#eff6ff'; // Default border color when nothing selected, mostly ignored by logic above but good fallback
                },
                setHover(idx) {
                    this.hoverIndex = idx;
                    if (window.workloadChart) {
                        if (idx !== -1) {
                            window.workloadChart.setActiveElements([{ datasetIndex: 0, index: idx }]);
                        } else {
                            window.workloadChart.setActiveElements([]);
                        }
                        window.workloadChart.update();
                    }
                }
            }));
            
            Alpine.data('nonProjectWorkloadChartComponent', () => ({
                activeIndex: -1, 
                hoverIndex: -1,
                labels: @json($reportData['nonProjectWorkload']['labels']),
                data: @json($reportData['nonProjectWorkload']['data']),
                total: {{ $reportData['nonProjectWorkload']['total'] }},
                colors: @json($reportData['nonProjectWorkload']['colors']),
                get currentLabel() { 
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    return idx !== -1 ? this.labels[idx] : 'Non Project';
                },
                get currentPct() {
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    if (idx === -1) return this.total;
                    if (this.total === 0) return '0%';
                    return Math.round((this.data[idx] / this.total) * 100) + '%';
                },
                get currentColor() {
                    let idx = this.hoverIndex !== -1 ? this.hoverIndex : this.activeIndex;
                    if (idx !== -1) return this.colors[idx % this.colors.length];
                    return '#eff6ff';
                },
                setHover(idx) {
                    this.hoverIndex = idx;
                    if (window.nonprojectWorkloadChart) {
                        if (idx !== -1) {
                            window.nonprojectWorkloadChart.setActiveElements([{ datasetIndex: 0, index: idx }]);
                        } else {
                            window.nonprojectWorkloadChart.setActiveElements([]);
                        }
                        window.nonprojectWorkloadChart.update();
                    }
                }
            }));
        });
    </script>
    @once
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Helper to ensure Chart is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }

            const initChart = (ctxId, labels, dataValues, colors, chartVarName) => {
                const ctx = document.getElementById(ctxId);
                if (!ctx) return;

                window[chartVarName] = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: dataValues,
                            backgroundColor: colors,
                            hoverBackgroundColor: colors,
                            borderWidth: 0,
                            hoverOffset: 20,
                            spacing: 2
                        }]
                    },
                    options: {
                        cutout: '60%', // Increased cutout slightly
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 800,
                            easing: 'easeOutQuart'
                        },
                        plugins: { 
                            legend: { display: false }, 
                            tooltip: { 
                                enabled: false,
                                external: function(context) {
                                    const { chart, tooltip } = context;
                                    const ttEl = document.getElementById('jira-custom-tooltip');
                                    const ttName = document.getElementById('tt-name');
                                    const ttDetail = document.getElementById('tt-detail');

                                    if (!ttEl) return;
                                    
                                    if (tooltip.opacity === 0) {
                                        ttEl.style.opacity = '0';
                                        return;
                                    }

                                    const idx = tooltip.dataPoints[0].dataIndex;
                                    const label = labels[idx];
                                    const value = dataValues[idx];
                                    const total = dataValues.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((value / total) * 100) : 0;

                                    ttName.innerText = label;
                                    ttDetail.innerText = `${value} Issues (${pct}%)`;

                                    const pos = chart.canvas.getBoundingClientRect();
                                    ttEl.style.opacity = '1';
                                    ttEl.style.left = (pos.left + window.pageXOffset + tooltip.caretX + 20) + 'px';
                                    ttEl.style.top = (pos.top + window.pageYOffset + tooltip.caretY - 100) + 'px';
                                }
                            } 
                        },
                        onHover: (event, elements) => {
                            const componentId = ctxId === 'workloadJiraDonut' ? 'workload-component' : 'nonproject-workload-component';
                            const alpineEl = document.getElementById(componentId);
                            if (alpineEl && window.Alpine) {
                                const data = window.Alpine.$data(alpineEl);
                                if (elements.length > 0) {
                                    data.hoverIndex = elements[0].index;
                                    event.native.target.style.cursor = 'pointer';
                                } else {
                                    data.hoverIndex = -1;
                                    event.native.target.style.cursor = 'default';
                                }
                            }
                        },
                        onClick: (event, elements) => {
                            const componentId = ctxId === 'workloadJiraDonut' ? 'workload-component' : 'nonproject-workload-component';
                            const alpineEl = document.getElementById(componentId);
                            if (alpineEl && window.Alpine && elements.length > 0) {
                                const data = window.Alpine.$data(alpineEl);
                                const idx = elements[0].index;
                                data.activeIndex = (data.activeIndex === idx ? -1 : idx);
                            }
                        }
                    }
                });
            };

            // Initialize both charts
            initChart(
                'workloadJiraDonut', 
                @json($reportData['workload']['labels']), 
                @json($reportData['workload']['data']), 
                @json($reportData['workload']['colors']),
                'workloadChart'
            );

            initChart(
                'nonprojectWorkloadJiraDonut', 
                @json($reportData['nonProjectWorkload']['labels']), 
                @json($reportData['nonProjectWorkload']['data']), 
                @json($reportData['nonProjectWorkload']['colors']),
                'nonprojectWorkloadChart'
            );
        });
    </script>
    @endonce
</x-filament-panels::page>
