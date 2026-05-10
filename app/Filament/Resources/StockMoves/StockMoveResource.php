<?php

namespace App\Filament\Resources\StockMoves;

use App\Filament\Resources\StockMoves\Pages\ListStockMoves;
use App\Filament\Resources\StockMoves\Pages\ListProductStockMoves;
use App\Filament\Resources\StockMoves\Tables\StockMovesTable;
use App\Models\StockMove;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMoveResource extends Resource
{
    protected static ?string $model = StockMove::class;

    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $recordTitleAttribute = 'reference';

    public static function table(Table $table): Table
    {
        return StockMovesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListStockMoves::route('/'),
            'by-product' => ListProductStockMoves::route('/product'),
        ];
    }
}
