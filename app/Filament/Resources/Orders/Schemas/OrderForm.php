<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('idempotency_key'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('transaction_id'),
                TextInput::make('gateway'),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('delivered_at'),
                DateTimePicker::make('cancelled_at'),
                TextInput::make('tracking_number'),
                TextInput::make('cancellation_reason'),
                DateTimePicker::make('placed_at'),
                TextInput::make('shipping_address_id')
                    ->numeric(),
                TimePicker::make('refunded_at'),
                TextInput::make('refund_id'),
            ]);
    }
}
