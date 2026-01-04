<?php

namespace Modules\Worship\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Modules\Worship\Models\Series;
use Modules\Worship\Models\Service;

class ServiceReport extends BaseReport
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
    }

    public static function routes(): void
    {
        Route::get('/admin/worship/reports/services/{id}', function ($serviceId) {
            $service = Service::with(['setitems' => function($q) { $q->orderBy('sort_order', 'asc'); }])->where('id',$serviceId)->first();
            return (new static())->setService($service)->handle();
        })->name('reports.service');
    }

    public function setService($service): static
    {
        $this->service = $service;
        return $this;
    }

    public function Header(): void
    {
        
    }

    public function Footer(): void
    {
        
    }

    public function generate(): void
    {
        $this->setReportTitle($this->service->servicedate);
        $stime =  $this->service->servicetime;
        $this->AddPage('P');
        $title=date("j F Y",strtotime($this->service->servicedate));
        $this->SetTitle($title . " - " . $stime);
        $this->SetAutoPageBreak(true, 0);
        $this->SetFont('Arial', 'B', 18);
        $song=url('/') . "/images/song.png";
        $prayer=url('/') . "/images/prayer.png";
        $this->Image(url('/') . "/images/bwidelogo.png",123,8,77,30);
        $this->rect(19,10,53,7.5,'F');
        $this->SetTextColor(255,255,255);
        if ($stime) {
            $this->text(20, 16, $stime . " service");
        }
        $this->SetTextColor(0,0,0);
        $this->SetFont('Arial', '', 14);
        $this->text(20, 23, $title);
        $this->SetFont('Arial', 'B', 14);
        $this->text(20, 32, "Order of service");
        $this->line(20, 35, 195, 35);

        if (isset($this->service->series_id)){
            $this->rect(75,18,50,16);
            $this->SetFont('Arial', 'B', 12);
            $this->text(77,23,"Sermon Series");
            $this->SetFont('Arial', '', 10);
            $series=Series::find($this->service->series_id);
            $this->text(77,28,$series->series);
            $this->text(77,32,"Week: " . 1 + (strtotime($this->service->servicedate) - strtotime($series->startingdate)) / 604800);
        }
        $items=$this->service->setitems;
        $yy=44;
        $projectarray=['Bible re','Communio','Benedict','Lords Pr'];
        foreach ($items as $item){
            //$item->extra = $this->GetExtraInfo($item);
            $this->SetFont('Arial', '', 14);
            if (!$item->setitemable_id){
                if (in_array(substr($item->note,0,8),$projectarray)){
                    $this->Image($prayer,10,$yy-4.5,8);
                }
                $this->text(20, $yy, $item->note);
                $width=$this->GetStringWidth($item->note);
                $this->SetFont('Arial', '', 10);
                $this->text(23+$width,$yy,$item->extra);
            } else {
                if ($item->setitemable_type=="song"){
                    $this->Image($song,12,$yy-4,4);
                    $this->SetFont('Arial', 'B', 14);
                } elseif ($item->setitemable_type=="prayer"){
                    $this->Image($prayer,10,$yy-4.5,8);
                }
                if ($item->note){
                    $this->text(20, $yy, $item->setitemable->title);
                    $width=$this->GetStringWidth($item->setitemable->title);
                    $this->SetFont('Arial', '', 10);
                    if (23+$width+$this->GetStringWidth($item->extra)>200){
                        $yy=$yy+5;
                        $this->text(30,$yy,$item->extra);
                    } else {
                        $this->text(23+$width,$yy,$item->extra);
                    }
                } else {
                    $this->text(20, $yy, $item->setitemable->title);
                    $width=$this->GetStringWidth($item->setitemable->title);
                    $this->SetFont('Arial', '', 10);
                    if (23+$width+$this->GetStringWidth($item->extra)>200){
                        $yy=$yy+5;
                        $this->text(30,$yy,$item->extra);
                    } else {
                        $this->text(23+$width,$yy,$item->extra);
                    }
                }
            }
            $yy=$yy+8;
        }
        $filename="OOS" . date('ymd',strtotime($this->service->servicedate)) . "_" . $stime;
        $rosters=array('Communion','Data Projector','Prayer','Society Stewards','Sound Desk','Welcome Team');
        $rosternotes=array();
        foreach ($rosters as $roster){
            //$newnote=$this->addRoster($roster,$this->service->servicetime,$this->service->servicedate);
            //if ($newnote !== $roster){
            //    $rosternotes[]=$newnote;
            //}
        }
        $yy=258;
        if (count($rosternotes)){
            $yy=$yy-5*count($rosternotes);
            $this->rect(18,$yy-6,140,5*count($rosternotes)+9);
            $this->SetFont('Arial', 'B', 12);
            $this->text(20, $yy, "Roster");
            $this->SetFont('Arial', '', 11);
            foreach ($rosternotes as $rosternote){
                $yy=$yy+5;
                $this->text(20, $yy, $rosternote);
            }
        }
        $this->rect(18,$yy+5,180,27);
        $this->SetFont('Arial', 'B', 12);
        $this->text(20,$yy+9,"Feedback or suggestions");
        $this->SetFont('Arial', '', 11);
        $this->text(75,$yy+9,"(anything we can do to improve for next time)");
        $this->Output('I',$filename);
        exit;
    }

    protected function getFilename(): string
    {
        return 'service-report-' . now()->format('Y-m-d') . '.pdf';
    }
}