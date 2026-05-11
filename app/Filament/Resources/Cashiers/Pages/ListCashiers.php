<?php

namespace App\Filament\Resources\Cashiers\Pages;

use App\Filament\Pages\PosTerminal;
use App\Filament\Resources\Cashiers\CashierResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCashiers extends ListRecords
{
    protected static string $resource = CashierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
