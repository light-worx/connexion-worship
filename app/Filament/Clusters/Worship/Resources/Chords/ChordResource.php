<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Chords;

use Modules\Worship\Models\Chord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages\CreateChord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages\EditChord;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Pages\ListChords;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Schemas\ChordForm;
use Modules\Worship\Filament\Clusters\Worship\Resources\Chords\Tables\ChordsTable;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;

class ChordResource extends Resource
{
    protected static ?string $model = Chord::class;

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $cluster = WorshipCluster::class;

    protected static ?string $recordTitleAttribute = 'chord';

    public static function form(Schema $schema): Schema
    {
        return ChordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChordsTable::configure($table);
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
            'index' => ListChords::route('/'),
            'create' => CreateChord::route('/create'),
            'edit' => EditChord::route('/{record}/edit'),
        ];
    }
}
