<?php

namespace Modules\Worship\Filament\Clusters\Worship\Resources\Services\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;
use Modules\People\Models\Individual;
use Modules\Worship\Filament\Clusters\Worship\Resources\Services\ServiceResource;
use Modules\Worship\Models\Service;
use Modules\Worship\Models\Setitem;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public $indiv, $notifyLabel, $services;

    public function getHeading(): string
    {
        return date('j F Y', strtotime($this->record->servicedate)) . " (" . $this->record->servicetime .")";
    }

    protected function getHeaderActions(): array
    {
        $this->indiv = Individual::find(setting('church_secretary'));
        if ($this->indiv){
            $this->notifyLabel=$this->indiv->firstname;
        } else {
            $this->notifyLabel="Office";
        }
        return [
            Action::make('Copy service')
                ->disabled(function () {
                    $services=setting('services');
                    $existingservices=Service::where('servicedate',$this->record->servicedate)->get();
                    foreach($existingservices as $es){
                        $searchndx=array_search($es->servicetime,$services);
                        if ($searchndx){
                            dd($searchndx);
                            unset($services[$searchndx]);
                        }
                    }
                    if (!count($services)){
                        return true;
                    } else {
                        foreach ($services as $ss){
                            $this->services[$ss]=$ss;
                        }
                    }
                })
                ->schema([
                    Select::make('service')->label('Create duplicate service at this time')
                        ->options($this->services)
                ])
                ->action(function (array $data) {
                    self::copyService($data);
                }),
            Action::make('Notify ' . $this->notifyLabel)->label('Notify ' . $this->notifyLabel)->action(function () {
                if ($this->indiv){
                    $email=$this->indiv->email;
                    $fname=" " . $this->indiv->firstname . "!";
                } else {
                    $email=setting('email.church_email');
                    $fname="!";
                }
                $subject = 'New service: ' . $this->record->servicetime . " " . $this->record->servicedate;
                $body = "Hi" . $fname . "<br><br>Just to let you know that a new service has been added to the database.<br><br>It can be accessed <a href=\"" . url('/') . "/admin/worship/services/" . $this->record->id . "/edit\">here</a><br><br>Thank you!";
                Mail::html($body, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                    $message->from(setting('email.church_email'),setting('general.church_name'));
                });
                Notification::make('Email sent')->title('Email sent to ' . $email)->send();
            }),
            Action::make('Order of service')->url(fn (): string => route('reports.service', ['id' => $this->record])),
            DeleteAction::make(),
        ];
    }

    private function copyService($data){
        //$data['service']
        $set=Service::with(['setitems' => function($q) { $q->orderBy('sortorder', 'asc'); }])->where('id',$this->record->id)->first();
        $newset = Service::create([
            'servicedate'=>$set->servicedate,
            'servicetime'=>$data['service'],
            'servicetitle'=>$set->servicedate . ' (' . $data['service'] . ')',
            'reading'=>$set->reading,
            'series_id'=>$set->series_id
        ]);
        foreach ($set->setitems as $item){
            $newitem=new Setitem([
                'service_id' => $item->service_id,
                'content_id' => $item->content_id,
                'content_type' => $item->content_type,
                'sort_order' => $item->sort_order,
                'title' => $item->title,
                'subtitle' => $item->subtitle
            ]);
            $newset->setitems()->save($newitem);
        }
        Notification::make('Service created')->title('Duplicate set has been created at ' . $data['service'])->send();
    }
}
