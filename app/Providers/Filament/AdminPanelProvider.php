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
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
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
        $adminEmail = 'adminBOA@adira.co.id';
        $user = null;
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
        } catch (\Throwable $e) {
            $user = null;
        }

        $groups = [
            NavigationGroup::make()->label('Tasks'),
            NavigationGroup::make()->label('Calendar'),
        ];

        if ($user && $user->email === $adminEmail) {
            $groups[] = NavigationGroup::make()->label('Master');
        }

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
                        ->modalHeading('Change Password')
                        ->form([
                            TextInput::make('current_password')
                                ->label('Current Password')
                                ->password()
                                ->revealable()
                                ->required(),
                            TextInput::make('password')
                                ->label('New Password')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(8),
                            TextInput::make('password_confirmation')
                                ->label('Confirm New Password')
                                ->password()
                                ->revealable()
                                ->required(),
                        ])
                        ->action(function (array $data) use ($user) {
                            // Validate current password
                            if (! Hash::check($data['current_password'], $user->password)) {
                                Notification::make()
                                    ->title('Current password is incorrect.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($data['password'] !== $data['password_confirmation']) {
                                Notification::make()
                                    ->title('New password and confirmation do not match.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $user->password = Hash::make($data['password']);
                            $user->save();

                            Notification::make()
                                ->title('Password changed successfully.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => (bool) $user);
                },
                // Override logout to show a confirmation modal in English before logging out.
                'logout' => function (): Action {
                    return Action::make('logout')
                        ->label('Sign out')
                        // 'heroicon-o-logout' is not present in the bundled heroicons set; use a v2 name instead
                        ->icon('heroicon-o-arrow-right-on-rectangle')
                        ->modalHeading('Confirm Sign Out')
                        ->modalSubheading('Are you sure you want to sign out?')
                        ->requiresConfirmation()
                        ->action(function () {
                            $request = request();
                            \Illuminate\Support\Facades\Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();

                            // Redirect to Filament login URL (use helper to avoid relying on a named route)
                            $loginUrl = filament()->getLoginUrl();

                            return redirect($loginUrl ?? '/');
                        });
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
