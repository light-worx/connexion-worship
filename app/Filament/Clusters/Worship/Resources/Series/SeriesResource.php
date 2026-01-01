<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Series;

use Modules\Worship\Models\Series;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\Pages\CreateSeries;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\Pages\EditSeries;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\Pages\ListSeries;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\Schemas\SeriesForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\Series\Tables\SeriesTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;

class SeriesResource extends Resource
{
    protected static ?string $model = Series::class;

    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'series';

    public static function form(Schema $schema): Schema
    {
        return SeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeriesTable::configure($table);
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
            'index' => ListSeries::route('/'),
            'create' => CreateSeries::route('/create'),
            'edit' => EditSeries::route('/{record}/edit'),
        ];
    }
}
