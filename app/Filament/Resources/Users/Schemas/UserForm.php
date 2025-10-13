<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('User Information')
                    ->description('The basic information of the user.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('John Doe')
                                    ->autocomplete('name')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('john@example.com')
                                    ->autocomplete('email')
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'This email is already registered.',
                                    ])
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->hiddenOn(Operation::Edit)
                            ->required()
                            ->minLength(8)
                            ->maxLength(255)
                            ->placeholder('Minimum 8 characters')
                            ->autocomplete('new-password')
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText('Use a strong password with letters, numbers, and symbols.')
                            ->validationMessages([
                                'min' => 'Password must be at least 8 characters.',
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Settings')
                    ->description('User role and status.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('role')
                                    ->options(\App\Enums\UserRole::class)
                                    ->required()
                                    ->default('Supplier')
                                    ->native(false)
                                    ->searchable()
                                    ->placeholder('Select a role')
                                    ->helperText('Determines user permissions and access level.')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Inactive users cannot log in.')
                                    ->columnSpan([
                                        'sm' => 2,
                                        'md' => 1,
                                    ]),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }
}
