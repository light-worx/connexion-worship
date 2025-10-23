<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SongsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('author')
                    ->searchable(),
                TextColumn::make('copyright')
                    ->searchable(),
                TextColumn::make('key')
                    ->searchable(),
                TextColumn::make('tempo')
                    ->searchable(),
                TextColumn::make('audio')
                    ->searchable(),
                TextColumn::make('video')
                    ->searchable(),
                TextColumn::make('music')
                    ->searchable(),
                TextColumn::make('musictype')
                    ->searchable(),
                TextColumn::make('bible')
                    ->searchable(),
                TextColumn::make('firstline')
                    ->searchable(),
                TextColumn::make('verseorder')
                    ->searchable(),
                TextColumn::make('tune')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
