<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas;

use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Modules\Website\Models\Person;
use Modules\Worship\Models\Prayer;
use Modules\Worship\Models\Service;
use Modules\Worship\Models\Setitem;
use Modules\Worship\Models\Song;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        $isCreate = $schema->getOperation() === "create";
        return $schema
            ->components([
                Tabs::make('Add New Service')->columnSpanFull()->tabs([
                    Tab::make('Order of service')->columns(2)->schema([
                        DatePicker::make('servicedate')
                            ->native(false)
                            ->displayFormat('Y-m-d')
                            ->format('Y-m-d')
                            ->label('Service date')
                            ->default(date('Y-m-d',strtotime('Sunday')))
                            ->live(onBlur: true)
                            ->required(),
                        Select::make('servicetime')
                            ->required()
                            ->label('Service time')
                            ->options(function (Get $get) use ($isCreate) {
                                $sd=substr($get('servicedate'),0,10);
                                $servicetimes = setting('services');
                                if ($get('add_extra_service_times')){
                                    $servicetimes=array_merge($servicetimes, setting('custom_service_times'));
                                }
                                if ($isCreate){
                                    $services = Service::where('servicedate',$sd)->get();
                                    foreach ($services as $service) {
                                        if (($key = array_search($service->servicetime, $servicetimes)) !== false) {
                                            unset($servicetimes[$key]);
                                        }
                                    }
                                }
                                $sarray=array();
                                foreach ($servicetimes as $st){
                                    $sarray[$st]=$st;
                                }
                                asort($sarray);
                                return $sarray;
                            })
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state){
                                $url="https://methodist.church.net.za/preacher/" . setting('services.society_id') . "/" . $state . "/" . substr($get('servicedate'),0,10);
                                $response=Http::get($url);
                                $fullname=$response->body();
                                $preacher=Person::where(DB::raw('concat(firstname," ",surname)') , '=' , $fullname)->first();
                                if ($preacher){
                                    $set('person_id',$preacher->id);  
                                }
                            })
                            ->placeholder(''),
                        TextInput::make('reading')
                            ->default(function (Get $get) {
                                $sd=substr($get('servicedate'),0,10);
                                $service = Service::where('servicedate',$sd)->first();
                                if ($service){
                                    return $service->reading;
                                }
                            })
                            ->required()
                            ->maxLength(191),
                        Select::make('series_id')
                            ->placeholder('')
                            ->relationship(name: 'series', titleAttribute: 'series')
                            ->default(function (Get $get) {
                                $sd=substr($get('servicedate'),0,10);
                                $service = Service::where('servicedate',$sd)->first();
                                if ($service) {
                                    return $service->series_id;
                                }
                            }),
                        Checkbox::make('add_extra_service_times')->label('Use non-standard service time')
                            ->live()
                            ->afterStateHydrated(function ($component, $record) {
                                if (is_null($record)){
                                    $component->state(false);
                                } else {
                                    if (in_array($record->servicetime,setting('services'))){
                                        $component->state(false);
                                    } else {
                                        $component->state(true);
                                    }
                                }
                            }),                   
                        View::make('worship::repeater-style'),     
                        Repeater::make('setitems')
                            ->relationship('setitems')
                            ->hiddenOn('create')
                            ->columnSpan(2)
                            ->label('')
                            ->live()
                            ->orderColumn('sortorder')
                            ->reorderableWithDragAndDrop()
                            ->addActionLabel('Add new set item')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('note')->hiddenHeaderLabel()
                            ])
                            ->schema([
                                TextEntry::make('note')
                                    ->hiddenLabel()
                                    ->state(function (Get $get) {
                                        return Setitem::with('setitemable')
                                            ->find($get('id'))
                                            ?->setitemable?->title ?? Setitem::find($get('id'))?->note;
                                    })
                                    ->icon(function (Get $get) {
                                        $item = Setitem::with('setitemable')->find($get('id'));
                                        if ($item->setitemable_type === 'song') {
                                            return 'heroicon-o-musical-note';
                                        } elseif ($item->setitemable_type === 'prayer') {
                                            return 'heroicon-o-book-open';
                                        } else {
                                            return 'heroicon-o-user';
                                        }
                                    })
                            ])
                            // 👇 ADD ITEM
                            ->addAction(
                                fn (Action $action) => $action
                                    ->schema([
                                        Select::make('setitemable_type')
                                            ->label('Item type')
                                            ->options([
                                                'song' => 'Song',
                                                'prayer' => 'Liturgy',
                                                'other' => 'Other',
                                            ])
                                            ->default('song')
                                            ->live()
                                            ->afterStateUpdated(fn (Set $set) => $set('setitemable_id', null)),

                                        Select::make('setitemable_id')
                                            ->label('Item')
                                            ->searchable()
                                            ->options(function (Get $get) {
                                                return match ($get('setitemable_type')) {
                                                    'song' => Song::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'prayer' => Prayer::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    default => collect(setting('worship.set_items'))->sort()->combine(setting('worship.set_items'))->toArray(),
                                                };
                                            }),

                                        TextInput::make('note'),
                                    ])
                                    ->after(function (array $data, Get $get, Repeater $component) {
                                        $serviceId = $get('id');
                                        $sort = count($component->getState());

                                        if ($data['setitemable_type'] === 'song') {
                                            $song = Song::find($data['setitemable_id']);
                                            $data['note'] = trim(($data['note'] ?? '') . ' ' . $song?->tune);
                                        }

                                        if ($data['setitemable_type'] === 'other') {
                                            $data['note'] = $data['setitemable_id'];
                                            $data['setitemable_id'] = null;
                                            $data['setitemable_type'] = null;
                                        }

                                        $setitem = Setitem::create([
                                            'service_id' => $serviceId,
                                            'setitemable_id' => $data['setitemable_id'],
                                            'setitemable_type' => $data['setitemable_type'],
                                            'note' => $data['note'],
                                            'sortorder' => $sort,
                                        ]);

                                        $component->state(
                                            collect($component->getState())
                                                ->filter(fn ($_, $k) => str_starts_with($k, 'record-'))
                                                ->put("record-{$setitem->id}", [
                                                    'id' => $setitem->id,
                                                    'service_id' => $serviceId,
                                                    'setitemable_id' => $setitem->setitemable_id,
                                                    'setitemable_type' => $setitem->setitemable_type,
                                                    'note' => $setitem->setitemable?->title ?? $setitem->note,
                                                    'sortorder' => $setitem->sortorder,
                                                ])
                                                ->toArray()
                                        );
                                    })
                            )

                            // 👇 DELETE
                            ->deleteAction(
                                fn (Action $action) => $action
                                    ->after(function (array $arguments) {
                                        $id = str_replace('record-', '', $arguments['item']);
                                        Setitem::find($id)?->delete();
                                    })
                            )

                            ->extraItemActions([
                                Action::make('viewItem')
                                    ->icon('heroicon-o-link')
                                    ->visible(function (array $arguments, Repeater $component) {
                                        $itemData = $component->getRawItemState($arguments['item']);
                                        if ($itemData['setitemable_type'] === 'song' || $itemData['setitemable_type'] === 'prayer') {
                                            return true;
                                        } else {
                                            return false;
                                        }
                                    })
                                    ->url(function (array $arguments, Repeater $component) {
                                        $state = $component->getRawItemState($arguments['item']);
                                        return match ($state['setitemable_type'] ?? null) {
                                            'song' => route('filament.admin.worship.resources.songs.edit', ['record' => $state['setitemable_id']]),
                                            'prayer' => route('filament.admin.worship.resources.prayers.edit', ['record' => $state['setitemable_id']]),
                                            default => null,
                                        };
                                    })
                                    ->openUrlInNewTab(),

                                Action::make('editSetitem')
                                    ->icon('heroicon-o-pencil-square')
                                    ->fillForm(function (array $arguments, Repeater $component) {
                                        $row=$component->getRawItemState($arguments['item']);
                                        $setitem=Setitem::find($row['id'])->toArray();
                                        return $setitem;
                                    })
                                    ->schema([
                                        TextEntry::make('title')
                                            ->hiddenLabel()
                                            ->state(function (Get $get) {
                                                return Setitem::with('setitemable')
                                                    ->find($get('id'))
                                                    ?->setitemable?->title;
                                            }),
                                        TextInput::make('note')->label('Details'),
                                        Hidden::make('id'),
                                    ])
                                    ->action(function (array $data) {
                                        Setitem::find($data['id'])?->update(['note' => $data['note']]);
                                    }),
                            ])

                    ]),
                    Tab::make('Sermon')->columns(2)->schema([
                        Select::make('person_id')
                            ->label('Preacher')
                            ->relationship(
                                name: 'person',
                                modifyQueryUsing: fn (Builder $query) => $query->orderBy('firstname')->orderBy('surname'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->firstname} {$record->surname}")
                            ->createOptionForm([
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('firstname')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('surname')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('bio')
                                    ->columnSpanFull(),
                                FileUpload::make('image')
                                    ->image(),
                                TextInput::make('role')
                                    ->required()
                                    ->maxLength(255),
                                Toggle::make('active'),
                            ]),
                        TextInput::make('sermon_title')
                            ->label('Sermon title')
                            ->maxLength(255),
                        TextInput::make('video')
                            ->suffixAction(MediaAction::make('showVideo')
                                ->icon('heroicon-m-video-camera')
                                ->media(function (Get $get){
                                    return "https://youtube.com/watch?v=" . $get('video');
                                })
                        ),
                        TextInput::make('audio')
                            ->suffixAction(MediaAction::make('playAudio')
                                ->icon('heroicon-m-musical-note')
                                ->media(fn (Get $get) => $get('audio'))
                        ),
                        Select::make('tags')
                            ->multiple()
                            ->options(fn () => \App\Models\Tag::where('type', 'service')->pluck('name', 'id'))
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                            ->required(),
                                        TextInput::make('type')
                                            ->default('service')
                                            ->readonly()
                                            ->required(),
                                        TextInput::make('slug')
                                            ->required(),
                                    ])
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $tag = \App\Models\Tag::create($data);
                                return $tag->id;
                            })
                            ->saveRelationshipsUsing(function ($component, $state, $record) {
                                $record->tags()->sync($state ?? []);
                            })
                            ->dehydrated(false)
                    ])
                ])
            ]);
    }
}
