<?php

namespace App\Filament\Resources\StockMoves\Pages;

use App\Filament\Resources\StockMoves\StockMoveResource;
use App\Models\Product;
use App\Models\StockMove;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProductStockMoves extends ListRecords
{
    protected static string $resource = StockMoveResource::class;

    public ?int $productId = null;

    public function mount(): void
    {
        $this->productId = (int) request()->query('product_id') ?: null;
        parent::mount();
    }

    protected function getTableQuery(): Builder
    {
        $query = StockMove::query()->with(['product', 'user']);

        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        return $query;
    }

    public function getHeading(): string
    {
        if ($this->productId && $product = Product::find($this->productId)) {
            return 'Stock Move: ' . $product->name;
        }

        return 'Stock Move';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
