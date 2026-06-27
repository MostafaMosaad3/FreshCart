<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Exceptions\IllegalTransitionException;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('total')
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state): string => $state->label())
                    ->color(fn (OrderStatus $state): string => match ($state) {
                        OrderStatus::Pending => 'gray',
                        OrderStatus::Paid => 'warning',
                        OrderStatus::Shipped => 'info',
                        OrderStatus::Delivered => 'success',
                        OrderStatus::Cancelled => 'danger',
                    }),
                TextColumn::make('tracking_number')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('placed_at')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())
                        ->mapWithKeys(fn (OrderStatus $s) => [$s->value => $s->label()])
                        ->all()),
            ])
            ->recordActions([
                // SHIP — only when paid; opens a tracking-number modal.
                Action::make('ship')
                    ->label('Ship')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid)
                    ->schema([
                        TextInput::make('tracking_number')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Ship this order?')
                    ->modalDescription('This triggers the OrderShipped event and notifies the customer.')
                    ->action(function (Order $record, array $data): void {
                        try {
                            $record->markAsShipped($data['tracking_number']);
                            Notification::make()->title('Order shipped')->success()->send();
                        } catch (IllegalTransitionException $e) {
                            Notification::make()->title('Cannot ship')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // DELIVER — only when shipped; one-click confirm.
                Action::make('deliver')
                    ->label('Deliver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Shipped)
                    ->requiresConfirmation()
                    ->action(function (Order $record): void {
                        try {
                            $record->markAsDelivered();
                            Notification::make()->title('Order delivered')->success()->send();
                        } catch (IllegalTransitionException $e) {
                            Notification::make()->title('Cannot deliver')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // CANCEL — pending/paid; asks for a reason; restocks + triggers refund.
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => in_array(
                        $record->status,
                        [OrderStatus::Pending, OrderStatus::Paid],
                        true,
                    ))
                    ->schema([
                        Textarea::make('reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->requiresConfirmation()
                    ->action(function (Order $record, array $data): void {
                        try {
                            $record->cancel($data['reason']);
                            Notification::make()->title('Order cancelled')->success()->send();
                        } catch (IllegalTransitionException $e) {
                            Notification::make()->title('Cannot cancel')->body($e->getMessage())->danger()->send();
                        }
                    }),

                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
