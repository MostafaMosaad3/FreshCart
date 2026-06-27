<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('vendor.store_name')->label('Vendor'),
                TextColumn::make('price')->money('EGP')->sortable(),
                TextColumn::make('rating_avg')->sortable(),
                TextColumn::make('reviews_count')->counts('reviews')->label('Reviews'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success', 'draft' => 'gray', 'inactive' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive',
                ]),
                SelectFilter::make('vendor_id')->relationship('vendor', 'store_name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),   // iterates → fires observer
                ]),
            ]);
    }
}
