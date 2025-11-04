<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Navigation\NavigationGroup;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\YearlyTasksChart;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Team Management') 
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->darkMode(false)
            ->colors([
                'primary' => Color::Amber,
            ])
            // Remove the topbar global search
            ->globalSearch(false)
            ->discoverResources(in: app_path(path: 'Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            // Tentukan urutan grup navigasi di sidebar
            ->navigationGroups([
                NavigationGroup::make()->label('Tasks'),
                NavigationGroup::make()->label('Calendar'),
                NavigationGroup::make()->label('Master'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                YearlyTasksChart::class,
            ])
            // Customize user menu: replace Profile with Change Password (go to Employees edit), keep Sign out
            ->userMenuItems([
                'profile' => function (): Action {
                    /** @var \App\Models\User $user */
                    $user = \Illuminate\Support\Facades\Auth::user();

                    return Action::make('changePassword')
                        ->label('Change Password')
                        ->icon('heroicon-o-key')
                        ->url(UserResource::getUrl('edit', ['record' => $user]))
                        ->visible(fn () => (bool) $user);
                },
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
