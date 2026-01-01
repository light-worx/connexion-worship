<?php

namespace Modules\Worship\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Worship\Models\Song;

class WorshipController extends Controller
{
    public function song($id)
    {
        $song = Song::find($id);
        $this->pdf->AddPage('P');
        $this->pdf->SetTitle($song->title);
        $this->pdf->SetAutoPageBreak(true, 0);
        $this->pdf->SetFont('Courier', 'B', 14);
        $this->pdf->text(20, 16, $song->title);
        $this->pdf->SetFont('Courier', 'I', 10);
        $this->pdf->text(20, 22, $song->author);
        $this->pdf->SetFont('Courier', '', 10);
        $this->pdf->text(185, 16, 'Key: ' . $song->key);
        $this->pdf->text(190, 22, $song->tempo);
        $this->pdf->line(20, 26, 200, 26);
        $x=20;
        $lines=explode(PHP_EOL, $song->lyrics);
        $y=34;
        $vo=explode(" ",$song->verseorder);
        foreach ($lines as $line) {
            $line=$this->convert_smart_quotes($line);
            if (strpos($line, '}')) {
                $line=str_replace('{', '', $line);
                $line=str_replace('}', '', $line);
                $this->pdf->SetFont('Courier', 'B', 12);
                $this->pdf->SetTextColor(160, 160, 160);
                $y=$y+3.5;
                $shortline = substr($line, 0, 2);
                $this->pdf->text(13, $y, $shortline);
                $shortline=trim($shortline);
                $y=$y-3.5;
                $vos="";
                foreach ($vo as $kk=>$vv){
                    if ($vv==$shortline){
                        $vos.=1+$kk . " ";
                    }
                }
                if ($vos){
                    $vos=substr($vos,0,-1);
                }
                if (strlen($vos)>6){
                    if (substr($vos,7,1)==" "){
                        $this->pdf->text(170, $y+7, substr($vos,0,7));
                        $this->pdf->text(170, $y+12, substr(trim($vos," "),7));
                    } else {
                        $this->pdf->text(170, $y+7, substr($vos,0,6));
                        $this->pdf->text(170, $y+12, substr(trim($vos," "),6));
                    }
                } else {
                    $this->pdf->text(170, $y+7, $vos);
                }
                $this->pdf->SetTextColor(0, 0, 0);
            } else {
                $this->pdf->SetFont('Courier', '', 12);
                if (strpos($line, ']')) {
                    $y=$y+3.5;
                }
                $x=20;
                $addme=$x;
                $chordline="";
                $minlen=0;
                for ($i=0; $i<strlen($line); $i++) {
                    if ($line[$i]=='[') {
                        $chordsub=substr($line, $i);
                        $chor=substr($chordsub, 1, -1+strpos($chordsub, ']'));
                        $minlen=$this->pdf->GetStringWidth($chor);
                        $chordline.=$chor;
                        $this->pdf->SetFont('Courier', '', 12);
                        $i=$i+strlen($chor)+1;
                    } else {
                        $this->pdf->text($x, $y, $line[$i]);
                        if ($minlen ==0){
                            $chordline.=" ";
                        } else {
                            $minlen=$minlen-$this->pdf->GetStringWidth(" ");
                            if ($minlen < 0){
                                $minlen=0;
                            }
                        }
                        $x=$x+$this->pdf->GetStringWidth($line[$i]);
                    }
                }
                $this->pdf->SetFont('Courier', 'B', 12);
                $this->pdf->text(20, $y-3.5, $chordline);
                $this->pdf->SetFont('Courier', '', 12);
            }
            $y=$y+3.5;
        }
        
        // Chord list
        $this->pdf->SetTextColor(0,0,0);
        $y=26;
        $chords = $this->_getChords($song->lyrics);
        if (is_array($chords)){
            foreach ($chords as $chord) {
                $this->pdf->SetFont('Courier', '', 7);
                $dbchord = Chord::where('chord',$chord)->get();
                $x1=190;
                if (count($dbchord)) {
                    $this->pdf->setxy(180,$y);
                    $this->pdf->SetFont('Courier', 'B', 10);
                    $this->pdf->cell(30,5,$chord,0,0,'C');
                    if ($dbchord[0]->fret==0){
                        $this->pdf->line(190,$y+5,200,$y+5);
                        $f=0;
                    } else {
                        $f=1;
                        $this->pdf->text(202,$y+8,$dbchord[0]->fret);
                    }
                    for ($i=6;$i>0;$i--){
                        $svar="s" . $i;
                        if ($dbchord[0]->{$svar}=="x"){
                            $this->pdf->SetDrawColor(175,175,175);
                            $this->pdf->line($x1,$y+5,$x1,$y+17);
                        } else {
                            $this->pdf->SetDrawColor(0,0,0);
                            $this->pdf->line($x1,$y+5,$x1,$y+17);
                        }
                        $this->pdf->SetDrawColor(0,0,0);
                        $x1=$x1+2;
                        if ($i<6){
                            $this->pdf->line(190,2+$y+$i*3,200,2+$y+$i*3);
                        }
                    }
                    $x=188.5;
                    $cdata=array(
                        "s6"=>$dbchord[0]->s6,
                        "s5"=>$dbchord[0]->s5,
                        "s4"=>$dbchord[0]->s4,
                        "s3"=>$dbchord[0]->s3,
                        "s2"=>$dbchord[0]->s2,
                        "s1"=>$dbchord[0]->s1
                    );
                    foreach ($cdata as $cd){
                        if ($cd !== 'x'){
                            $cd = $cd - $dbchord[0]->fret + $f;
                            $this->pdf->SetFont('Courier', 'B', 14);
                            if ($cd > 0){
                                $this->pdf->SetFont('Courier', 'B', 20);
                                $circle=url('/') . "/church/images/circle.png";
                                $this->pdf->Image($circle,$x+0.5,$y+2.5+3*$cd,2,2);
                                $this->pdf->SetFont('Courier', 'B', 14);
                            }
                            $this->pdf->SetFont('Courier', '', 7);
                        }
                        $x=$x+2;
                    }
                } else {
                    $this->pdf->SetTextColor(125,125,125);
                    $this->pdf->setxy(180,$y);
                    $this->pdf->SetFont('Courier', 'B', 10);
                    $this->pdf->cell(30,5,$chord,0,0,'C');            
                    $this->pdf->SetTextColor(0,0,0);
                    $this->pdf->SetDrawColor(125,125,125);
                    for ($i=1;$i<7;$i++){
                        $this->pdf->line($x1,$y+5,$x1,$y+17);
                        $x1=$x1+2;
                        if ($i<6){
                            $this->pdf->line(190,2+$y+$i*3,200,2+$y+$i*3);
                        }
                    }
                    $this->pdf->SetFillColor(0,0,0);
                }
                $y=$y+18;
            }
        }
        $filename=Str::slug($song->title, "-");
        $this->pdf->Output('I',$filename);
        exit;
    }
}
