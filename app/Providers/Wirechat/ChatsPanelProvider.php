<?php

namespace App\Providers\Wirechat;

use App\Models\User;
use Filament\Support\Colors\Color;
use Wirechat\Wirechat\Http\Resources\WirechatUserResource;
use Wirechat\Wirechat\Panel;
use Wirechat\Wirechat\PanelProvider;
use Wirechat\Wirechat\Support\Enums\EmojiPickerPosition;

class ChatsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('chats')
            ->path('chats')
            ->middleware(['web', 'auth'])
            ->emojiPicker(position: EmojiPickerPosition::Docked)
            ->webPushNotifications()
            ->fileAttachments()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->createChatAction()
            ->searchUsersUsing(function (string $needle) {
                return WirechatUserResource::collection(
                    User::query()
                        ->whereHas('supplier', function ($query) {
                            $query->active();
                        })
                        ->where('name', 'like', "%{$needle}%")
                        ->limit(10)
                        ->get()
                );
            })
            ->maxUploads(5)
            ->redirectToHomeAction()
            ->default();
    }
}
