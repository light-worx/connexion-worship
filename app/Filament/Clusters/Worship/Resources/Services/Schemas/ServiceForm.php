<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas;

use App\Models\Person;
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
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                            ->label('')
                            ->columnSpan(2)
                            ->live()
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->addActionLabel('Add new set item')
                            ->table([
                                TableColumn::make('title')->hiddenHeaderLabel(),
                                TableColumn::make('Extra details'),
                            ])
                            ->schema([
                                TextEntry::make('title')
                                    ->icon(function ($record){
                                        if ($record) {
                                            switch ($record->content_type) {
                                                case 'song':
                                                    return 'heroicon-o-musical-note';
                                                case 'reading':
                                                    return 'heroicon-o-book-open';
                                                case 'prayer':
                                                    return 'heroicon-o-microphone';
                                                default:
                                                    return '';
                                            }
                                        }
                                        return '';
                                    })
                                    ->state(function (Setitem $record) {
                                        if ($record) {
                                            switch ($record->content_type) {
                                                case 'song':
                                                    $song = Song::find($record->content_id);
                                                    return $song ? $song->title : 'Unknown song';
                                                case 'prayer':
                                                    $prayer = Prayer::find($record->content_id);
                                                    return $prayer ? $prayer->title : 'Unknown prayer';
                                                default:
                                                    return $record->title;
                                            }
                                        }
                                        return '';
                                    })
                                    ->hiddenLabel(),
                                TextEntry::make('subtitle')->hiddenLabel(),
                                Hidden::make('id')
                            ])
                            ->addAction(
                                fn (Action $action) => $action
                                    ->schema([
                                        Select::make('content_type')
                                            ->label('Type')
                                            ->options([
                                                'song' => 'Song',
                                                'prayer' => 'Prayer',
                                                'reading' => 'Bible reading',
                                                'sermon' => 'Sermon',
                                                'other' => 'Other'
                                            ])
                                            ->default('song')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                                if ($state === 'reading') {
                                                    $set('subtitle', $record->reading);
                                                }
                                                if ($state === 'sermon') {
                                                    $set('subtitle', 'Michael Bishop');
                                                }
                                            }),
                                        Select::make('content_id')
                                            ->label('Select content')
                                            ->searchable()
                                            ->reactive()
                                            ->options(function (Get $get) {
                                                $type = $get('content_type');
                                                return match ($type) {
                                                    'song'   => Song::orderBy('title')->pluck('title', 'id'),
                                                    'prayer' => Prayer::orderBy('title')->pluck('title', 'id'),
                                                    default  => [],
                                                };
                                            })
                                            ->visible(fn (Get $get) => $get('content_type') === 'song' || $get('content_type') === 'prayer'
                                            )
                                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                                $type = $get('content_type');
                                                if ($type === 'song' && $state) {
                                                    $song = Song::find($state);
                                                    $set('subtitle', $song?->firstline);
                                                }
                                            }),
                                        TextInput::make('title')->visible(fn (Get $get) => $get('content_type') == "other")->default(''),
                                        Hidden::make('service_id')->default(fn ($record) => $record->id),
                                        TextInput::make('subtitle')->label('Optional note')
                                    ])
                                    ->action(function (array $data, $livewire) {
                                        // Create new Setitem
                                        if (isset($data['title'])){
                                            $title = $data['title'];
                                        } elseif ($data['content_type'] == "reading"){
                                            $title = "Bible reading";
                                        } else {
                                            $title= null;
                                        }
                                        $setitem = Setitem::create([
                                            'service_id'   => $data['service_id'],
                                            'content_type' => $data['content_type'],
                                            'title' => $title,
                                            'content_id'   => $data['content_id'] ?? null,
                                            'subtitle'     => $data['subtitle'] ?? null,
                                            'sort_order'   => Setitem::where('service_id', $data['service_id'])->count(),
                                        ]);
                                        $livewire->refreshFormData(['setitems']);
                                    })
                            )
                            ->deleteAction(
                                fn(Action $action) => $action
                                    ->after(function (array $arguments) {
                                        $id = str_replace('record-', '', $arguments['item']);
                                        Setitem::find($id)?->delete();
                                    })
                            )
                            ->extraItemActions([
                                // View linked content if it exists
                                Action::make('viewItem')
                                    ->icon('heroicon-o-link')
                                    ->visible(function (array $arguments, Repeater $component) {
                                        $itemData = $component->getRawItemState($arguments['item']);
                                        $type = $itemData['content_type'] ?? null;
                                        if (!$type || empty($itemData['content_id'])) return false;
                                        return in_array($type, ['song', 'prayer']);
                                    })
                                    ->url(function (array $arguments, Repeater $component) {
                                        $itemData = $component->getRawItemState($arguments['item']);
                                        return match ($itemData['content_type']) {
                                            'song' => route('filament.admin.worship.resources.songs.edit', ['record' => $itemData['content_id']]),
                                            'prayer' => route('filament.admin.worship.resources.prayers.edit', ['record' => $itemData['content_id']]),
                                            default => null,
                                        };
                                    })
                                    ->openUrlInNewTab(),

                                // Edit note / type / content
                                Action::make('editSetitem')
                                    ->icon('heroicon-o-pencil-square')
                                    ->fillForm(function (array $arguments, Repeater $component) {
                                        $row = $component->getRawItemState($arguments['item']);
                                        $setitem = Setitem::find($row['id']);
                                        if (!$setitem) return [];
                                        return [
                                            'id' => $setitem->id,
                                            'content_type' => $setitem->content_type,
                                            'content_id' => $setitem->content_id,
                                            'subtitle' => $setitem->subtitle,
                                        ];
                                    })
                                    ->schema([
                                        Select::make('content_type')
                                            ->label('Type')
                                            ->options([
                                                'song' => 'Song',
                                                'prayer' => 'Prayer',
                                                'reading' => 'Bible reading',
                                                'sermon' => 'Sermon',
                                                'other' => 'Other'
                                            ])
                                            ->reactive()
                                            ->afterStateUpdated(fn(Set $set) => $set('content_id', null)),

                                        Select::make('content_id')
                                            ->label('Select content')
                                            ->searchable()
                                            ->reactive()
                                            ->options(function (Get $get) {
                                                $type = $get('content_type');
                                                return match ($type) {
                                                    'song' => Song::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'prayer' => Prayer::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    default => [],
                                                };
                                            })
                                            ->visible(fn (Get $get) => $get('content_type') === 'song' || $get('content_type') === 'prayer')
                                            ->afterStateUpdated(function($state, Get $get, Set $set) {
                                                $type = $get('content_type');
                                                if ($type == "song"){
                                                    $song=Song::find($state);
                                                    $set('subtitle', $song->firstline ?? '');
                                                }
                                            }),
                                        Hidden::make('id')->default(fn (Get $get) => $get('id')),
                                        TextInput::make('subtitle')->label('Optional note'),
                                    ])
                                    ->action(function (array $data, callable $set) {
                                        Setitem::whereKey($data['id'])->update([
                                            'subtitle' => $data['subtitle'] ?? null,
                                            'content_type' => $data['content_type'] ?? null,
                                            'content_id' => $data['content_id'] ?? null,
                                        ]);

                                        // Update repeater state so UI refreshes
                                        $set('subtitle', $data['subtitle'] ?? null);
                                    })

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
