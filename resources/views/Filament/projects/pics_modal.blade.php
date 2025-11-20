@php
    /** @var \App\Models\Project|null $project */
    $project = $project ?? null;
    $pics = $project ? $project->projectPics()->with('user')->get() : collect();
@endphp

<div style="min-width:640px;" x-data>
    @if ($pics->isEmpty())
        <div style="padding:20px; text-align:center; color:#6b7280;">
            No PICs added for this project.
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:12px;padding:8px 8px 0 8px">
            @foreach ($pics as $p)
                <div class="pic-row"
                    style="display:flex;align-items:center;justify-content:space-between;
                            padding:14px;border-radius:12px;border:1px solid #eef0f2;
                            background:#fff;box-shadow:0 1px 3px rgba(15,23,42,0.04)">
                    <div style="display:flex;flex-direction:column">
                        <div style="font-weight:600; font-size:15px;">
                            {{ $p->user->employee_name ?? ($p->sk_user ?? '-') }}
                        </div>
                        <div style="color:#6b7280;font-size:13px;margin-top:6px;">
                            Start Date: {{ $p->start_date?->format('M j, Y') ?: '-' }}
                            &nbsp; • &nbsp;
                            End Date: {{ $p->end_date?->format('M j, Y') ?: '-' }}
                        </div>
                    </div>

                    <button type="button" aria-label="Remove PIC"
                        style="width:40px;height:40px;display:inline-flex;align-items:center;
                                justify-content:center;background:#fee2e2;border-radius:8px;
                                border:0;color:#b91c1c;cursor:pointer;"
                        x-on:click.prevent="
                                                    if (confirm('Are you sure you want to remove this PIC?')) {
                                                        $wire.deleteProjectPic({{ $p->id }});
                                                    }
                                                ">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="18"
                            height="18">
                            <path
                                d="M9 4.5h6M10.5 4.5V3.75A1.75 1.75 0 0 1 12.25 2h-.5A1.75 1.75 0 0 1 14 3.75V4.5M5 7h14" />
                            <path d="M9 7v11a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V7" />
                            <path d="M11 10v6M13 10v6" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>
