<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontFamily;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNot('role', UserRole::Admin)->whereNot('id', Auth::id()))
            ->columns([
                // User identity with avatar placeholder
                TextColumn::make('name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user-circle')
                    ->iconColor('primary')
                    ->weight('semibold')
                    ->description(fn ($record) => $record->role->value ?? 'No role assigned')
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->fontFamily(FontFamily::Mono)
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope')
                    ->iconColor('gray')
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->copyMessageDuration(1500)
                    ->tooltip(fn ($state) => $state)
                    ->limit(30)
                    ->toggleable()
                    ->visibleFrom('md'),

                // Combined verification and status column
                TextColumn::make('email_verified_at')
                    ->label('Verification')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('Unverified')
                    ->badge()
                    ->color(fn ($state): string => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state ? $state : 'Unverified')
                    ->icon(fn ($state) => $state ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-triangle')
                    ->description(fn ($record) => $record->email_verified_at
                        ? $record->email_verified_at->diffForHumans()
                        : 'Email not verified')
                    ->tooltip(fn ($record) => $record->email_verified_at
                        ? 'Verified on '.$record->email_verified_at->format('F j, Y')
                        : 'User needs to verify their email')
                    ->toggleable()
                    ->visibleFrom('lg'),

                TextColumn::make('role')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state) => $state ? 'Active account' : 'Inactive account')
                    ->alignCenter()
                    ->toggleable(),

                // Activity summary column
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->since()
                    ->description(fn ($record) => 'Updated '.$record->updated_at->diffForHumans())
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray')
                    ->tooltip(fn ($record) => 'Created: '.$record->created_at->format('F j, Y \a\t g:i A').
                        "\nUpdated: ".$record->updated_at->format('F j, Y \a\t g:i A'))
                    ->toggleable()
                    ->visibleFrom('xl'),

                TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->options(\App\Enums\UserRole::class)
                    ->multiple()
                    ->preload()
                    ->label('Filter by Role')
                    ->placeholder('All roles')
                    ->indicator('Role'),

                TernaryFilter::make('is_active')
                    ->label('Account Status')
                    ->placeholder('All accounts')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->indicator('Status'),

                TernaryFilter::make('email_verified_at')
                    ->label('Email Verification')
                    ->placeholder('All users')
                    ->trueLabel('Verified only')
                    ->falseLabel('Unverified only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                    )
                    ->indicator('Verification'),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Joined from')
                            ->placeholder('Select date'),
                        DatePicker::make('created_until')
                            ->label('Joined until')
                            ->placeholder('Select date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = Indicator::make('Joined from '.Carbon::parse($data['created_from'])->toFormattedDateString())
                                ->removeField('created_from');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = Indicator::make('Joined until '.Carbon::parse($data['created_until'])->toFormattedDateString())
                                ->removeField('created_until');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::Dropdown)
            ->persistFiltersInSession()
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filters')
                    ->icon('heroicon-m-funnel')
                    ->color('gray')
                    ->badge(fn ($state) => count(array_filter($state ?? [])) ?: null)
            )
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->icon('heroicon-m-eye')
                        ->color('info'),
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square')
                        ->color('warning'),
                    Action::make('toggle_status')
                        ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn ($record) => $record->is_active ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                        ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record) => $record->is_active ? 'Deactivate Account' : 'Activate Account')
                        ->modalDescription(fn ($record) => $record->is_active
                            ? 'Are you sure you want to deactivate this account? The user will not be able to log in.'
                            : 'Are you sure you want to activate this account? The user will be able to log in.')
                        ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active]))
                        ->successNotificationTitle(fn ($record) => 'Account '.($record->is_active ? 'activated' : 'deactivated').' successfully'),
                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation(),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size(Size::Small)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->successNotificationTitle('Users activated successfully'),

                    BulkAction::make('deactivate')
                        ->label('Deactivate selected')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->successNotificationTitle('Users deactivated successfully'),

                    // ExportBulkAction::make()
                    //     ->label('Export selected')
                    //     ->icon('heroicon-m-arrow-down-tray')
                    //     ->color('info')
                    //     ->exporter(), // You'll need to create this
                    DeleteBulkAction::make()
                        ->icon('heroicon-m-trash')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ])
                    ->label('Bulk actions')
                    ->icon('heroicon-m-chevron-down'),
            ])
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Get started by creating your first user account.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Create first user')
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->extremePaginationLinks()
            ->deferLoading(); // Auto-refresh every 30 seconds
    }
}
