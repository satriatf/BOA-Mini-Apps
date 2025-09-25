<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3" id="gantt-year-form">
        <label for="gantt-year-select" class="text-sm font-medium">Year</label>
        <select id="gantt-year-select" name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 5; $y <= now()->year + 2; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit" color="warning">Apply</x-filament::button>
    </form>


    <div id="gantt-root" class="gantt-container"></div>

    <link rel="stylesheet" href="{{ asset('gantt/gantt.css') }}">
    <script src="{{ asset('gantt/gantt.js') }}"></script>
    
    <script>
        window.GANTT_DATA = @json($this->getRows());
        window.GANTT_YEAR = {{ (int) $year }};
    </script>
</x-filament-panels::page>
