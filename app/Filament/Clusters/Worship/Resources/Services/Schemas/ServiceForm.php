<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        Repeater::make('setitems')
                            ->live()
                            ->hiddenOn('create')
                            ->relationship('setitems')
                            ->label('')
                            ->columnSpan(2)
                            ->schema([])
                            //->view('worship::components.set-item')
                            ->reorderableWithDragAndDrop(true)
                            ->orderColumn('sortorder')
                            ->addActionLabel('Add new set item')
                            ->itemLabel(function (array $state){
                                if ($state['note']) {
                                    return $state['note'];
                                } else {
                                    if ($state['setitemable_id']){
                                        $setitem=Setitem::with('setitemable')->where('id',$state['id'])->first();
                                        return $setitem->setitemable->title;
                                    } else {
                                        //dd($state);
                                    }
                                }
                            })
                            ->deleteAction(
                                function (Action $action) {
                                    return $action
                                        ->after(function ($state, array $arguments) {
                                            $id=substr($arguments['item'],7);
                                            Setitem::find($id)->delete();
                                        });
                                })
                            ->addAction(function ($action) {
                                return $action->form([
                                    Select::make('setitemable_type')->label('Item type')
                                        ->options([
                                            'song' => 'Song',
                                            'prayer' => 'Liturgy',
                                            'other' => 'Other'
                                        ])
                                        ->default('song')
                                        ->live()
                                        ->selectablePlaceholder(false)
                                        ->afterStateUpdated(function (Set $set) {
                                            $set('setitemable_id',null);
                                        }),
                                    Select::make('setitemable_id')
                                        ->label('Item')
                                        ->searchable()
                                        ->selectablePlaceholder(false)
                                        ->options(function (Get $get) {
                                            $id=$get('setitemable_type');
                                            if ($id=='song'){
                                                return Song::orderBy('title')->get()->pluck('title', 'id')->toArray();
                                            } elseif ($id=='prayer'){
                                                return Prayer::orderBy('title')->get()->pluck('title', 'id')->toArray();
                                            } else {
                                                $dat=setting('worship.set_items');
                                                asort($dat);
                                                return array_combine($dat,$dat);
                                            }
                                    }),
                                    TextInput::make('note'),
                                    ])
                                    ->after(function ($data, Get $get, Repeater $component) {
                                        $component->state(function ($state) use ($data, $get) {
                                            $setitems = collect($state);
                                            $id = $get('id');
                                            $ndx = count($setitems);
                                            if ($data['setitemable_type']=="song"){
                                                $song = Song::find($data['setitemable_id']);
                                                if (is_null($data['note'])){
                                                    $data['note']=$song->tune;
                                                } else {
                                                    $data['note']=$data['note'] . " " . $song->tune;
                                                }
                                            } elseif ($data['setitemable_type']=="other"){
                                                $data['note']=$data['setitemable_id'];
                                                $data['setitemable_type']=null;
                                                $data['setitemable_id']=null;
                                            }
                                            $si = Setitem::create([
                                                'service_id' => $id,
                                                'setitemable_id' => $data['setitemable_id'],
                                                'setitemable_type' => $data['setitemable_type'],
                                                'note' => $data['note'],
                                                'sortorder' => $ndx
                                            ]);
                                            if (isset($si->setitemable->title)){
                                                $setitems['record-' . $si->id]=array(
                                                    'id' => $si->id,
                                                    'service_id' => $si->service_id,
                                                    'setitemable_id' => $si->setitemable_id,
                                                    'setitemable_type' => $si->setitemable_type,
                                                    'note' => $si->setitemable->title,
                                                    'sortorder' => $si->sortorder,
                                                    'extra' => null
                                                );
                                            } else {
                                                $setitems['record-' . $si->id]=array(
                                                    'id' => $si->id,
                                                    'service_id' => $si->service_id,
                                                    'setitemable_id' => $si->setitemable_id,
                                                    'setitemable_type' => $si->setitemable_type,
                                                    'note' => $data['note'],
                                                    'sortorder' => $si->sortorder,
                                                    'extra' => null
                                                );
                                            }
                                            foreach ($setitems as $i=>$setitem){
                                                if (substr($i,0,6)<>"record"){
                                                    unset($setitems[$i]);
                                                }
                                            }
                                            return $setitems->toArray();
                                        });
                                    });
                            })->extraItemActions([
                                Action::make('viewItem')
                                ->icon('heroicon-o-link')
                                ->hidden(function (array $arguments, Repeater $component) {
                                    $si=$component->getRawItemState($arguments['item']);
                                    if ($si['setitemable_type']){
                                        return false;
                                    } elseif ($si['note']=="Bible reading"){
                                        return false;
                                    } else  {
                                        return true;
                                    }
                                })
                                ->url(function (array $arguments, Repeater $component) {
                                    $si=$component->getRawItemState($arguments['item']);
                                    if (!$si['setitemable_type']){
                                        if ($si['note']=="Bible reading"){ 
                                            $service=Service::find($si['service_id']);
                                            return "http://biblegateway.com/passage/?search=" . urlencode($service->reading) . "&version=GNT";
                                        } else {
                                            return "";
                                        }
                                    } else {
                                        if ($si['setitemable_type']=="song") {
                                            return route('filament.admin.worship.resources.songs.edit',['record'=>$si['setitemable_id']]);
                                        } else {
                                            return route('filament.admin.worship.resources.prayers.edit',['record'=>$si['setitemable_id']]);
                                        }
                                    }
                                })
                                ->openUrlInNewTab(),
                                Action::make('editSetitem')
                                ->label('Edit set item')
                                ->tooltip('Edit')
                                ->icon('heroicon-o-pencil-square')
                                ->fillForm(function (array $arguments, Repeater $component) {
                                    return $component->getRawItemState($arguments['item']);
                                })
                                ->form([
                                    Placeholder::make('setitemable_type')->label('')->content(
                                        function (Get $get){
                                            $id = $get('id');
                                            $setitem=Setitem::with('setitemable')->where('id',$id)->first();
                                            if (isset($setitem->setitemable)){
                                                return $setitem->setitemable->title;
                                            }
                                        }
                                    ),
                                    TextInput::make('note')
                                        ->label('Details')
                                        ->default(function (Get $get){
                                            return $get('note');
                                        }),
                                    Hidden::make('id')
                                        ->default(function (Get $get){
                                            return $get('id');
                                        }),
                                ])
                                ->action(function (array $data, Setitem $setitem): void {
                                    $setitem=Setitem::find($data['id']);
                                    $setitem->note=$data['note'];
                                    $setitem->save();
                                })
                            ]),
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
                            ->createOptionForm([
                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                            ->required(),
                                        TextInput::make('type')
                                            ->default('sermon')
                                            ->readonly()
                                            ->required(),
                                        TextInput::make('slug')
                                            ->required(),
                                    ])
                            ]),
                    ])
                ])
            ]);
    }
}
