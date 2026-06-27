<?php

namespace App\Filament\Resources\Vendors\Tables;

use App\Models\Vendor;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_verified')
                    ->boolean(),
                TextColumn::make('rating_avg')
                    ->label('Rating')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('reviews_count')
                    ->label('Reviews')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_verified'),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-shield-check')
                    ->color('success')
                    ->visible(fn (Vendor $record): bool => ! $record->is_verified)
                    ->requiresConfirmation()
                    ->action(function (Vendor $record): void {
                        $record->update(['is_verified' => true]);
                        Notification::make()->title('Vendor verified')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
