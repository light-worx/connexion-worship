<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Songs\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Modules\Worship\Models\Service;
use Modules\Worship\Models\Setitem;
use Modules\Worship\Models\Song;

class SongForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Add New Song')->columnSpanFull()->tabs([
                    Tab::make('Main')->columns(2)->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(191),
                        TextInput::make('author')
                            ->maxLength(191),
                        Select::make('musictype')
                            ->label('Type')
                            ->default('contemporary')
                            ->options([
                                'archive' => 'Archive',
                                'contemporary' => 'Contemporary',
                                'hymn' => 'Hymn',
                            ])
                            ->default('contemporary')
                            ->required(),    
                        TextInput::make('firstline')
                            ->required()
                            ->label('First line')
                            ->maxLength(255),
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
                                            ->default('song')
                                            ->readonly()
                                            ->required(),
                                        TextInput::make('slug')
                                            ->required(),
                                    ])
                            ]),
                        TextEntry::make('Services')
                            ->key('servicePlaceholder')
                            ->label(function (Song $record = null): string {
                                if ($record){
                                    return 'Last used: ' . $record->lastused;
                                } else {
                                    return 'Last used: ';
                                }
                            })
                            ->hintActions(self::getServices()),
                        PdfViewerField::make('file')
                            ->hiddenOn('create')
                            ->label('')
                            ->minHeight('80svh')
                            ->fileUrl(fn (Song $record) => url('/') . '/admin/reports/song/' . $record->id)
                            ->columnSpanFull(),
                    ]),
                    Tab::make('Details')->columns(2)->schema([
                        TextInput::make('tempo')
                            ->maxLength(191),
                        TextInput::make('copyright')
                            ->maxLength(191),
                        Select::make('key')
                            ->options([
                                'A' => 'A',
                                'A#/Bb' => 'A#/Bb',
                                'B' => 'B',
                                'C' => 'C',
                                'C#/Db' => 'C#/Db',
                                'D' => 'D',
                                'D#/Eb' => 'D#/Eb',
                                'E' => 'E',
                                'F' => 'F',
                                'F#/Gb' => 'F#/Gb',
                                'G' => 'G',
                                'G#/Ab' => 'G#/Ab'
                            ]),
                        TextInput::make('verseorder')
                            ->maxLength(191)
                            ->label('Verse order'),
                        TextInput::make('tune')
                            ->maxLength(191),
                        TextInput::make('bible')->label('Bible reference')
                            ->maxLength(191),
                        Textarea::make('lyrics')
                            ->label('Lyrics ({} for sections, [] for chords)')
                            ->required()
                            ->rows(20)
                            ->columnSpanFull(),
                        TextEntry::make('openlp')
                            ->state(function (Get $get){
                                    $lyrics=$get('lyrics');
                                    $lyrics=preg_replace('/\[[^\]]*\]/', '', $lyrics);
                                    $lyrics=str_replace('{V','---[Verse:',$lyrics);
                                    $lyrics=str_replace('{C','---[Chorus:',$lyrics);
                                    $lyrics=str_replace('{P','---[Pre-Chorus:',$lyrics);
                                    $lyrics=str_replace('{B','---[Bridge:',$lyrics);
                                    $lyrics=str_replace('{T','---[Tag:',$lyrics);
                                    $lyrics=str_replace('}',']---',$lyrics);
                                    $lyrics=nl2br($lyrics);
                                    return new HtmlString($lyrics);
                                }
                            )
                            ->columnSpanFull(),
                    ]),
                    Tab::make('Media')->schema([     
                        TextInput::make('audio')
                            ->suffixAction(MediaAction::make('playAudio')
                                ->icon('heroicon-m-musical-note')
                                ->media(fn (Get $get) => $get('audio'))
                        ),
                        TextInput::make('video')
                            ->suffixAction(MediaAction::make('showVideo')
                                ->icon('heroicon-m-video-camera')
                                ->media(function (Get $get){
                                    return "https://youtube.com/watch?v=" . $get('video');
                                })
                        ),
                        FileUpload::make('music')
                            ->hiddenOn('create')
                            ->label(function (Song $record){
                                if ($record->music<>""){
                                    $url=Storage::disk('google')->url($record->music);
                                    $url=str_replace('uc?id=','file/d/',$url);
                                    $url=str_replace('&export=media','/view',$url);    
                                    return new HtmlString("<a target='_blank' href='" . $url . "'>Click here to open music</a>");
                                } else {
                                    return "Music";
                                }
                                return $url;
                            })
                            ->directory('songs')
                            ->downloadable()
                            ->disk('google'),
                    ]),
                    Tab::make('History')->schema([
                        TextEntry::make('history')->label('')
                        ->state(function (Song $record) {
                            if ($record){
                                $allplays=Service::whereHas('setitems', 
                                    function($q) use ($record) { 
                                        $q->where('setitemable_id',$record->id)
                                        ->where('setitemable_type','song'); 
                                    })
                                    ->where('servicedate','<',date('Y-m-d'))->orderBy('servicedate','DESC')->get();
                                $history=array();
                                foreach ($allplays as $play){
                                    $history[$play->servicetime][]=date('Y-m-d',strtotime($play->servicedate));
                                }
                                ksort($history);
                                $period=date('Y-m-d',strtotime('4 months ago'));
                                $histarray=array();
                                foreach ($history as $stime=>$hist){
                                    asort($hist);
                                    $histarray[$stime]['latest']=$hist[0];
                                    $histarray[$stime]['recent']=0;
                                    $histarray[$stime]['total']=0;
                                    foreach ($hist as $hh){
                                        if ($hh>$period){
                                            $histarray[$stime]['recent']++;
                                        }
                                        $histarray[$stime]['total']++;
                                    }
                                }
                                $historytext="";
                                foreach ($histarray as $service=>$val){
                                    $historytext.="<b>" . $service . "</b>: Sung ";
                                    if ($val['total']==1) {
                                        $historytext.= "once in total on " . $val['latest'];
                                    } else {
                                        $historytext.= $val['total'] . " times in total (";
                                        if ($val['recent'] ==1 ){
                                            $historytext.= "once in the last four months, on " . $val['latest'] . ")<br>";
                                        } elseif ($val['recent'] > 1) {
                                            $historytext.= $val['recent'] . " times in the last four months) and most recently on " . $val['latest'] . "<br>";
                                        }  elseif ($val['recent'] == 1) {
                                            $historytext.= $val['recent'] . " time in the last four months) and most recently on " . $val['latest'] . "<br>";
                                        } else {
                                            $historytext= substr($historytext,0,-1) . "and most recently on " . $val['latest'] . "<br>";
                                        }
                                    }
                                }
                                return new HtmlString($historytext);
                            } else {
                                return " ";
                            }
                        })
                    ]),
                ]),
            ]);
    }

    public static function getServices (){
        $serviceActions=[];
        $songid = request()->route()->parameter('record');
        $services=Service::with('setitems')->where('servicedate','>=',date('Y-m-d'))->get();
        foreach ($services as $service){
            $serviceActions[]=Action::make('Add' . $service->servicedate . $service->servicetime)
            ->label($service->servicedate . ' (' . $service->servicetime . ')')->button()
            ->action(function ($record) use ($service) {
                self::addToService($service->id,$record->id,count($service->setitems));
                return redirect()->route('filament.admin.worship.resources.songs.edit', ['record' => $record->id]);
            })
            ->icon('heroicon-o-plus')
            ->hidden(function () use ($service,$songid){
                $check=Setitem::where('service_id',$service->id)->where('setitemable_id',$songid)->where('setitemable_type','song')->first();
                if (!$check){
                    return false;
                } else {
                    return true;
                }
            });
        }
        return $serviceActions;
    }

    public static function addToService($service,$song,$order){
        Setitem::create([
            'service_id'=>$service,
            'setitemable_type'=>'song',
            'setitemable_id'=>$song,
            'sortorder'=>$order
        ]);
    }
}
