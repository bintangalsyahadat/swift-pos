<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\StockMoves\StockMoveResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('stock_moves')
                ->label('Lihat Pergerakan Stok')
                ->icon('heroicon-o-arrows-right-left')
                ->color('gray')
                ->url(fn() => StockMoveResource::getUrl('by-product') . '?product_id=' . $this->record->id),
        ];
    }
}
