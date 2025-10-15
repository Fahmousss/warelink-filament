<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->maxLength(20)
                    ->dehydrated(false)
                    ->disabled()
                    ->visible(fn ($record) => $record === null)
                    ->placeholder('Auto-generated')
                    ->prefixIcon('heroicon-m-hashtag')
                    ->autocomplete('off')
                    ->helperText('Unique identifier for this product. Will be generated automatically'),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('city'),
                TextInput::make('country'),
                TextInput::make('tax_number'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
