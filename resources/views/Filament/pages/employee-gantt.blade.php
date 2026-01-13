<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3" id="timeline-year-form">
        <label for="timeline-year-select" class="text-sm font-medium">Select Year</label>
        <select id="timeline-year-select" name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 3; $y <= now()->year + 5; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit" color="warning">Apply</x-filament::button>
    </form>

    {{-- Filter by Task Type --}}
    <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200" id="timeline-filters">
        <div class="flex items-center gap-4 flex-wrap">
            <span class="text-sm font-medium text-gray-700">Filter by Task:</span>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="filter-project" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" checked>
                <span class="text-sm text-gray-700 select-none">Project</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" id="filter-non-project" class="w-4 h-4 text-amber-600 bg-gray-100 border-gray-300 rounded focus:ring-amber-500 focus:ring-2" checked>
                <span class="text-sm text-gray-700 select-none">Non-Project</span>
            </label>
        </div>
    </div>

    {{-- Legend - Simple like Monthly/Yearly --}}
    <div class="mb-4 flex items-center gap-4 text-sm">
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#3b82f6;display:inline-block;border-radius:2px"></span> Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Non-Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#8b0bd6;display:inline-block;border-radius:2px"></span> Project with Overtime
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#00ff00;display:inline-block;border-radius:2px"></span> Holiday
        </span>
    </div>

    {{-- Timeline Container - Scrollable --}}
    <div id="timeline-root" class="gantt-container-wrapper">
        <div class="gantt-container"></div>
    </div>

    <link rel="stylesheet" href="{{ asset('gantt/gantt.css') }}">
    <script src="{{ asset('gantt/gantt.js') }}"></script>
    
    <script>
        window.GANTT_DATA = @json($this->getRows());
        window.GANTT_YEAR = {{ (int) $year }};
    </script>
</x-filament-panels::page>
