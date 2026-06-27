<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic info')->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->required()
                    ->maxLength(2000)
                    ->columnSpanFull(),
                Select::make('vendor_id')
                    ->relationship('vendor', 'store_name')
                    ->searchable()
                    ->required(),
                Select::make('status')->options([
                    'draft' => 'Draft', 'active' => 'Active', 'inactive' => 'Inactive',
                ])->default('draft')->required(),
            ])->columns(2),

            Section::make('Pricing & stock')->schema([
                TextInput::make('price')->numeric()->required()->prefix('EGP'),
                TextInput::make('stock')->numeric()->minValue(0)->default(0)->required(),
            ])->columns(2),

            Section::make('Tags')->schema([
                TagsInput::make('tags')->placeholder('Add a tag'),
            ]),
        ]);
    }
}
