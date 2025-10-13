<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextEntry::make('unverified_warning')
                    ->hiddenLabel()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-amber-900 dark:text-amber-100">Email Not Verified</h3>
                                    <p class="text-sm text-amber-800 dark:text-amber-200">This user has not verified their email address yet.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && is_null($record->email_verified_at))
                    ->columnSpanFull(),

                TextEntry::make('inactive_warning')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->state(new HtmlString('
                            <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-red-900 dark:text-red-100">Account Inactive</h3>
                                    <p class="text-sm text-red-800 dark:text-red-200">This account is currently disabled and cannot access the system.</p>
                                </div>
                            </div>
                        '))
                    ->visible(fn ($record) => $record && ! $record->is_active),

                // User Information Section
                Section::make('User Information')
                    ->icon('heroicon-o-user-circle')
                    ->description('Basic account details and contact information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->icon('heroicon-m-user')
                                    ->iconColor('primary')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('email')
                                    ->label('Email address')
                                    ->icon('heroicon-m-envelope')
                                    ->iconColor('gray')
                                    ->copyable()
                                    ->fontFamily(FontFamily::Mono)
                                    ->copyMessage('Email copied!')
                                    ->copyMessageDuration(1500)
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('role')
                                    ->badge()
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                IconEntry::make('is_active')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('danger')
                                    ->tooltip(fn ($state) => $state ? 'Account is active' : 'Account is inactive')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('email_verified_at')
                                    ->label('Email verified')
                                    ->dateTime('M d, Y H:i')
                                    ->placeholder('Not verified')
                                    ->icon('heroicon-m-check-badge')
                                    ->iconColor(fn ($state) => $state ? 'success' : 'gray')
                                    ->color(fn ($state) => $state ? 'success' : 'gray')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state ? $state : 'Not verified')
                                    ->columnSpan([
                                        'sm' => 3,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                // Activity Timeline Section
                Section::make('Activity Timeline')
                    ->icon('heroicon-o-clock')
                    ->description('Account creation and modification history')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Account created')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-plus-circle')
                                    ->iconColor('success')
                                    ->placeholder('-')
                                    ->since()
                                    ->tooltip(fn ($state) => $state?->format('F j, Y \a\t g:i A'))
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                TextEntry::make('updated_at')
                                    ->label('Last modified')
                                    ->dateTime('M d, Y H:i')
                                    ->icon('heroicon-m-arrow-path')
                                    ->iconColor('gray')
                                    ->placeholder('-')
                                    ->since()
                                    ->tooltip(fn ($state) => $state?->format('F j, Y \a\t g:i A'))
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),
            ]);
    }
}
