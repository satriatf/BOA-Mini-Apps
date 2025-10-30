@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget" style="grid-column: span 2 / span 2;">
    <x-filament::section>
        <x-filament-panels::avatar.user
            size="lg"
            :user="$user"
            loading="lazy"
        />

        <div class="fi-account-widget-main">
            <h2 class="fi-account-widget-heading">
                {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
            </h2>

            <p class="fi-account-widget-user-name">
                {{ filament()->getUserName($user) }}
            </p>

            {{-- Added: show user level under name --}}
            @if (filled($user?->level))
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $user->level }}
                </p>
            @endif
        </div>

        {{-- Sign out button removed intentionally --}}
    </x-filament::section>
</x-filament-widgets::widget>
