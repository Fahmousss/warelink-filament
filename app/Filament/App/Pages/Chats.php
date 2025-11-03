<?php

namespace App\Filament\App\Pages;

use App\Enums\UserRole;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class Chats extends Page
{
    protected string $view = 'filament.app.pages.chats';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string|Htmlable
    {
        return '';
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->role === UserRole::Checker || Auth::user()?->role === UserRole::Admin;
    }
}
