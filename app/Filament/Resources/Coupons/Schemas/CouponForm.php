<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coupon')->schema([
                TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Select::make('strategy')
                    ->options(collect(array_keys(config('discounts.strategies', [])))
                        ->mapWithKeys(fn (string $key) => [$key => $key])
                        ->all())   // strategy keys from the W6D1 Strategy pattern config
                    ->required(),
                KeyValue::make('config')
                    ->label('Strategy config')
                    ->keyLabel('Option')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
                TextInput::make('description')->maxLength(255),
            ])->columns(2),

            Section::make('Limits')->schema([
                TextInput::make('min_subtotal')->numeric()->prefix('EGP'),
                TextInput::make('max_uses')->numeric()->minValue(0),
                TextInput::make('max_per_user')->numeric()->minValue(0),
                Toggle::make('first_order_only'),
            ])->columns(2),

            Section::make('Validity')->schema([
                Toggle::make('is_active')->default(true),
                DateTimePicker::make('starts_at'),
                DateTimePicker::make('expires_at'),
            ])->columns(2),
        ]);
    }
}
