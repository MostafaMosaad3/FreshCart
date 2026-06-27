<?php

namespace App\Filament\Resources\Reviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Reviewer')
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->color(fn (int $state): string => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger'))
                    ->sortable(),
                TextColumn::make('comment')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('reviewable_type')
                    ->label('On')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('rating')
                    ->options([
                        1 => '1 ★',
                        2 => '2 ★',
                        3 => '3 ★',
                        4 => '4 ★',
                        5 => '5 ★',
                    ]),
            ])
            ->recordActions([
                // Moderation: capture a reason for the audit trail before removing.
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('moderation_reason')->maxLength(500),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
