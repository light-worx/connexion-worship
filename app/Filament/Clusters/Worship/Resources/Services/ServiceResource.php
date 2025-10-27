<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages\CreateService;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages\EditService;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages\ListServices;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas\ServiceForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\Tables\ServicesTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;
use Modules\Worship\Models\Service;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'servicedate';

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }
}
