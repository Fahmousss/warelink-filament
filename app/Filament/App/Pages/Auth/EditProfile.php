<?php

namespace App\Filament\App\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class EditProfile extends BaseEditProfile
{
    protected string $view = 'filament.app.pages.auth.edit-profile';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Personal Information Section
                Section::make('Personal Information')
                    ->description('Update your personal details and contact information.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                $this->getNameFormComponent()
                                    ->placeholder('Enter your full name')
                                    ->helperText('This is how others will see your name.')
                                    ->maxLength(255)
                                    ->autocomplete('name'),

                                $this->getEmailFormComponent()
                                    ->placeholder('your.email@example.com')
                                    ->helperText('We\'ll never share your email with anyone.')
                                    ->unique(ignoreRecord: true)
                                    ->autocomplete('email')
                                    ->validationMessages([
                                        'unique' => 'This email is already registered.',
                                    ]),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->columnSpanFull(),

                // Security Section
                Section::make('Security Settings')
                    ->description('Manage your password and account security.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([

                        Grid::make(1)
                            ->schema([
                                Group::make()
                                    ->schema([
                                        $this->getPasswordFormComponent()
                                            ->label('New Password')
                                            ->revealable()
                                            ->prefixIcon('heroicon-m-lock-closed')
                                            ->placeholder('Enter new password')
                                            ->autocomplete('new-password')
                                            ->helperText('Leave blank to keep current password.')
                                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->minLength(8)
                                            ->maxLength(255)
                                            ->rule('regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/')
                                            ->validationMessages([
                                                'regex' => 'Password must contain uppercase, lowercase, and numbers.',
                                            ]),
                                    ])
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                Group::make()
                                    ->schema([
                                        $this->getPasswordConfirmationFormComponent()
                                            ->label('Confirm New Password')
                                            ->revealable()
                                            ->prefixIcon('heroicon-m-lock-closed')
                                            ->placeholder('Re-enter new password')
                                            ->autocomplete('new-password')
                                            ->helperText('Must match the new password.')
                                            ->dehydrated(false),
                                    ])
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),

                                Action::make('save')
                                    ->label('Save Changes')
                                    ->button()
                                    ->color('primary')
                                    ->size('md')
                                    ->action('save'),
                            ]),
                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->collapsed()
                    ->columnSpanFull(),

            ]);
    }
}
