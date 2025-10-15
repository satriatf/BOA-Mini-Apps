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

    {{-- Filter Project Type --}}
    <div class="mb-4 flex items-center gap-4 p-3 bg-gray-50 rounded-lg border">
        <span class="text-sm font-medium text-gray-700">Filter by Task:</span>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="filter-project" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" checked>
            <label for="filter-project" class="text-sm text-gray-700">Project</label>
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" id="filter-non-project" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500" checked>
            <label for="filter-non-project" class="text-sm text-gray-700">Non-Project</label>
        </div>
    </div>


    <div id="timeline-root" class="gantt-container"></div>

    <link rel="stylesheet" href="{{ asset('gantt/gantt.css') }}">
    <script src="{{ asset('gantt/gantt.js') }}"></script>
    
    <script>
        window.GANTT_DATA = @json($this->getRows());
        window.GANTT_YEAR = {{ (int) $year }};
    </script>
</x-filament-panels::page>
