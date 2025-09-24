<x-filament-panels::page>
    {{-- Filter Tahun --}}
    <form method="GET" class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Year</label>
        <select name="year" class="fi-input block rounded-md border-gray-300 text-sm">
            @for ($y = now()->year - 5; $y <= now()->year + 2; $y++)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
        </select>
        <x-filament::button type="submit" color="warning">Apply</x-filament::button>
    </form>

    {{-- Legend warna --}}
    <div class="mb-4 flex items-center gap-4 text-sm">
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#3b82f6;display:inline-block;border-radius:2px"></span> Project
        </span>
        <span class="inline-flex items-center gap-2">
            <span style="width:12px;height:12px;background:#f59e0b;display:inline-block;border-radius:2px"></span> Non-Project (MTC)
        </span>
    </div>

    <div id="gantt-root" class="gantt-container"></div>

    <link rel="stylesheet" href="{{ asset('gantt/gantt.css') }}">
    <script src="{{ asset('gantt/gantt.js') }}"></script>
    
    <script>
        window.GANTT_DATA = @json($this->getRows());
        window.GANTT_YEAR = {{ (int) $year }};
    </script>
</x-filament-panels::page>
