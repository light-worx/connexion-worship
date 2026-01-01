<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(['title','lyrics','author']),
                TextColumn::make('lastused')
                    ->label('Last used'),
                IconColumn::make('musictype')->label('Type')
                    ->icon(fn (string $state): string => match ($state) {
                        'archive' => 'heroicon-o-archive-box-x-mark',
                        'hymn' => 'heroicon-o-building-library',
                        'contemporary' => 'heroicon-o-musical-note',
                    }),
                IconColumn::make('music')
                    ->boolean(),
                TextColumn::make('key')
                    ->label('Key'),
                TextColumn::make('tags.name')
                    ->badge()
                    ->forceSearchCaseInsensitive(true)
                    ->searchable()
            ])
            ->defaultSort('title','ASC')
            ->filters([
                SelectFilter::make('musictype')->label('')
                    ->options([
                        'archive' => 'Archive',
                        'contemporary' => 'Contemporary',
                        'hymn' => 'Hymn'
                    ]),
                Filter::make('hide_archive')
                    ->query(fn (Builder $query): Builder => $query->where('musictype', '<>', 'archive'))
                    ->default()
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                ]),
            ]);
    }
}
