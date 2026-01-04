<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas;

use App\Models\Person;
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
use Modules\Worship\Models\Prayer;
use Modules\Worship\Models\Service;
use Modules\Worship\Models\Setitem;
use Modules\Worship\Models\Song;
use Illuminate\Support\Str;
use Modules\Worship\Models\ServiceElementType;

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
                            ->reorderableWithDragAndDrop()
                            ->addActionLabel('Add new set item')
                            ->table([
                                TableColumn::make('title')->hiddenHeaderLabel(),
                                TableColumn::make('note')->hiddenHeaderLabel(),
                            ])
                            ->schema([
                                TextEntry::make('title')
                                    ->hiddenLabel()
                                    ->state(function (Get $get) {
                                        $item = Setitem::with('elementType')->find($get('id'));
                                        if (!$item) return null;
                                        $contentTitle = null;
                                        if ($item->content_id && $item->elementType?->content_kind) {
                                            switch ($item->elementType->content_kind) {
                                                case 'song':
                                                    $contentTitle = $item->song?->title;
                                                    break;
                                                case 'prayer':
                                                    $contentTitle = $item->prayer?->title;
                                                    break;
                                                case 'reading':
                                                    $contentTitle = $item->reading?->reference;
                                                    break;
                                                case 'preacher':
                                                    $contentTitle = $item->preacher?->firstname . ' ' . $item->preacher?->surname;
                                                    break;
                                            }
                                        }

                                        return $contentTitle ?? $item->elementType?->label ?? $item->note;
                                    }),
                                TextEntry::make('note')->hiddenLabel(),
                                Hidden::make('id'),
                            ])
                            // ------------------- ADD ITEM -------------------
                            ->addAction(
                                fn(Action $action) => $action
                                    ->schema([
                                        Select::make('element_type_id')
                                            ->label('Type')
                                            ->options(ServiceElementType::orderBy('label')->pluck('label', 'id')->toArray())
                                            ->reactive()
                                            ->afterStateUpdated(fn(Set $set) => $set('content_id', null)),

                                        Select::make('content_id')
                                            ->label('Select content')
                                            ->searchable()
                                            ->options(function (Get $get) {
                                                $type = ServiceElementType::find($get('element_type_id'));
                                                if (!$type || !$type->expects_content) return [];

                                                return match ($type->content_kind) {
                                                    'song' => Song::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'prayer' => Prayer::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'reading' => BibleReading::pluck('reference', 'id')->toArray(),
                                                    'preacher' => Person::orderBy('firstname')->orderBy('surname')->pluck('firstname', 'id')->toArray(),
                                                    default => [],
                                                };
                                            })
                                            ->visible(fn(Get $get) => ServiceElementType::find($get('element_type_id'))->expects_content ?? false),

                                        TextInput::make('note')->label('Optional note'),
                                    ])
                                    ->after(function (array $data, Get $get, Repeater $component) {
                                        $serviceId = $get('id');
                                        $sort = count($component->getState());

                                        $setitem = Setitem::create([
                                            'service_id' => $serviceId,
                                            'element_type_id' => $data['element_type_id'],
                                            'content_id' => $data['content_id'] ?? null,
                                            'note' => $data['note'] ?? null,
                                            'sortorder' => $sort,
                                        ]);

                                        $component->state(
                                            collect($component->getState())
                                                ->put("record-{$setitem->id}", [
                                                    'id' => $setitem->id,
                                                    'element_type_id' => $setitem->element_type_id,
                                                    'content_id' => $setitem->content_id,
                                                    'note' => $setitem->note,
                                                    'title' => $setitem->content_id 
                                                        ? ($setitem->{$setitem->elementType->content_kind}?->title ?? $setitem->note) 
                                                        : ($setitem->elementType?->label ?? $setitem->note),
                                                    'sortorder' => $setitem->sortorder,
                                                ])
                                                ->toArray()
                                        );
                                    })
                            )
                            // ------------------- DELETE ITEM -------------------
                            ->deleteAction(
                                fn(Action $action) => $action
                                    ->after(function (array $arguments) {
                                        $id = str_replace('record-', '', $arguments['item']);
                                        Setitem::find($id)?->delete();
                                    })
                            )
                            // ------------------- EXTRA ITEM ACTIONS (VIEW / EDIT) -------------------
                            ->extraItemActions([
                                // View linked content if it exists
                                Action::make('viewItem')
                                    ->icon('heroicon-o-link')
                                    ->visible(function (array $arguments, Repeater $component) {
                                        $itemData = $component->getRawItemState($arguments['item']);
                                        $elementTypeId = $itemData['element_type_id'] ?? null;
                                        if (!$elementTypeId || empty($itemData['content_id'])) return false;
                                        $elementType = ServiceElementType::find($elementTypeId);
                                        return in_array($elementType?->content_kind ?? '', ['song', 'prayer']);
                                    })
                                    ->url(function (array $arguments, Repeater $component) {
                                        $itemData = $component->getRawItemState($arguments['item']);
                                        $elementTypeId = $itemData['element_type_id'] ?? null;
                                        if (!$elementTypeId || empty($itemData['content_id'])) return null;
                                        $elementType = ServiceElementType::find($elementTypeId);
                                        return match ($elementType?->content_kind) {
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
                                            'element_type_id' => $setitem->element_type_id,
                                            'content_id' => $setitem->content_id,
                                            'note' => $setitem->note,
                                        ];
                                    })
                                    ->schema([
                                        Select::make('element_type_id')
                                            ->label('Type')
                                            ->options(ServiceElementType::orderBy('label')->pluck('label', 'id')->toArray())
                                            ->reactive()
                                            ->afterStateUpdated(fn(Set $set) => $set('content_id', null)),

                                        Select::make('content_id')
                                            ->label('Select content')
                                            ->searchable()
                                            ->options(function (Get $get) {
                                                $type = ServiceElementType::find($get('element_type_id'));
                                                if (!$type || !$type->expects_content) return [];
                                                return match ($type->content_kind) {
                                                    'song' => Song::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'prayer' => Prayer::orderBy('title')->pluck('title', 'id')->toArray(),
                                                    'reading' => BibleReading::pluck('reference', 'id')->toArray(),
                                                    'preacher' => Person::orderBy('firstname')->orderBy('surname')->pluck('firstname', 'id')->toArray(),
                                                    default => [],
                                                };
                                            })
                                            ->visible(fn(Get $get) => ServiceElementType::find($get('element_type_id'))->expects_content ?? false),

                                        TextInput::make('note')->label('Optional note'),
                                    ])
                                    ->action(function (array $data, callable $set) {
                                        if (! isset($data['id'])) {
                                            return;
                                        }

                                        // Persist to DB
                                        Setitem::whereKey($data['id'])->update([
                                            'note' => $data['note'] ?? null,
                                        ]);

                                        // Update repeater state so UI refreshes
                                        $set('note', $data['note'] ?? null);
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
