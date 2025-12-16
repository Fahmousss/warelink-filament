<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Authenticate::redirectUsing(fn (): string => Filament::getLoginUrl());
        AuthenticateSession::redirectUsing(
            fn (): string => Filament::getLoginUrl()
        );
        AuthenticationException::redirectUsing(
            fn (): string => Filament::getLoginUrl()
        );
    }
}
