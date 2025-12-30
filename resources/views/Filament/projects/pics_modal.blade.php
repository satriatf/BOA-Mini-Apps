{{-- Clean design --}}
@if($project && $project->projectPics()->count() > 0)
    <div class="space-y-6" id="pics-container">
        @foreach($project->projectPics as $pic)
            <div class="bg-white border-2 border-gray-300 rounded-lg p-6 hover:bg-gray-50 transition-colors shadow-md" data-pic-id="{{ $pic->id }}">
                <!-- Header with Delete Button -->
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <h3 style="font-weight: 500; color: #111827; font-size: 16px; margin: 0;">{{ $pic->user->employee_name ?? 'N/A' }}</h3>
                    <button type="button" 
                        onclick="if(confirm('Apakah Anda yakin ingin menghapus PIC ini?')) { 
                            var form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/project-pics/{{ $project->sk_project }}/delete/{{ $pic->id }}';
                            form.style.display = 'none';
                            
                            var csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = document.querySelector('meta[name=&quot;csrf-token&quot;]')?.getAttribute('content') || '';
                            form.appendChild(csrfInput);
                            
                            var methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'DELETE';
                            form.appendChild(methodInput);
                            
                            document.body.appendChild(form);
                            form.submit();
                        }"
                        style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 28px !important; height: 28px !important; background-color: #ef4444 !important; color: white !important; border-radius: 6px !important; text-decoration: none !important; transition: all 0.2s !important; flex-shrink: 0 !important; border: none; cursor: pointer;"
                        onmouseover="this.style.backgroundColor='#dc2626'"
                        onmouseout="this.style.backgroundColor='#ef4444'">
                        <svg style="width: 16px !important; height: 16px !important; display: block !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
                
                <!-- Details -->
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex">
                        <span class="w-20 text-gray-500">Period:</span>
                        <span>{{ $pic->start_date?->format('d M Y') }} - {{ $pic->end_date?->format('d M Y') }}</span>
                    </div>
                    @if($pic->has_overtime)
                        <div class="flex">
                            <span class="w-20 text-blue-600">Overtime:</span>
                            <span class="text-blue-600">{{ $pic->overtime_start_date?->format('d M Y') }} - {{ $pic->overtime_end_date?->format('d M Y') }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-gray-100">
                        <span class="font-medium">Total: {{ $pic->total_days ?? 0 }} days</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @else
    <div class="text-center py-8 text-gray-500">
        No PICs assigned
    </div>
@endif