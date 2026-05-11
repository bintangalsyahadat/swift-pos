<?php

namespace App\Filament\Resources\PosSessions;

use App\Filament\Resources\PosSessions\Pages\ListPosSessions;
use App\Filament\Resources\PosSessions\Pages\ViewPosSession;
use App\Filament\Resources\PosSessions\Tables\PosSessionsTable;
use App\Models\PosSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PosSessionResource extends Resource
{
    protected static ?string $model = PosSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Point of Sale';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Session History';

    protected static ?string $modelLabel = 'POS Session';

    public static function table(Table $table): Table
    {
        return PosSessionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosSessions::route('/'),
            'view'  => ViewPosSession::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
