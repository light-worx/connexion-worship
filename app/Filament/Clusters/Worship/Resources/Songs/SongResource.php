<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Pages\CreateSong;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Pages\EditSong;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Pages\ListSongs;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Schemas\SongForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Tables\SongsTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;
use Modules\Worship\Models\Song;

class SongResource extends Resource
{
    protected static ?string $model = Song::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SongForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SongsTable::configure($table);
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
            'index' => ListSongs::route('/'),
            'create' => CreateSong::route('/create'),
            'edit' => EditSong::route('/{record}/edit'),
        ];
    }
}
