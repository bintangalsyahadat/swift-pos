<?php

namespace App\Filament\Resources\InventoryAdjustments;

use App\Filament\Resources\InventoryAdjustments\Pages\CreateInventoryAdjustment;
use App\Filament\Resources\InventoryAdjustments\Pages\EditInventoryAdjustment;
use App\Filament\Resources\InventoryAdjustments\Pages\ListInventoryAdjustments;
use App\Filament\Resources\InventoryAdjustments\Pages\ViewInventoryAdjustment;
use App\Filament\Resources\InventoryAdjustments\RelationManagers\InventoryAdjustmentDetailRelationManager;
use App\Filament\Resources\InventoryAdjustments\Schemas\InventoryAdjustmentForm;
use App\Filament\Resources\InventoryAdjustments\Schemas\InventoryAdjustmentInfolist;
use App\Filament\Resources\InventoryAdjustments\Tables\InventoryAdjustmentsTable;
use App\Models\InventoryAdjustment;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InventoryAdjustmentResource extends Resource
{
    protected static ?string $model = InventoryAdjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ClipboardDocumentList;
    protected static string|UnitEnum|null $navigationGroup = 'Product Management';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationLabel = 'Inventory Adjustment';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InventoryAdjustmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventoryAdjustmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventoryAdjustmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InventoryAdjustmentDetailRelationManager::class,
        ];
    }

    public static function canEdit($record): bool
    {
        return $record->status === 'draft';
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListInventoryAdjustments::route('/'),
            'create' => CreateInventoryAdjustment::route('/create'),
            'view'   => ViewInventoryAdjustment::route('/{record}'),
            'edit'   => EditInventoryAdjustment::route('/{record}/edit'),
        ];
    }
}
