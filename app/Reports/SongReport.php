<?php

namespace Modules\Worship\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Modules\Worship\Models\Chord;
use Modules\Worship\Models\Song;

class SongReport extends BaseReport
{
    protected $song;

    public function __construct()
    {
        parent::__construct();
        $this->config['default_font']['family'] = 'Courier';
        $this->config['default_font']['size'] = 11;
        $this->config['page']['margins']['left'] = 15;
        $this->config['page']['margins']['right'] = 10;
        $this->config['page']['margins']['top'] = 20;
        $this->config['footer']['enabled'] = false;
    }

    public static function routes(): void
    {
        Route::get('/admin/worship/reports/songs/{song}', function ($songId) {
            $song = Song::findOrFail($songId);
            return (new static())->setSong($song)->handle();
        })->name('reports.song');
    }

    public function setSong($song): static
    {
        $this->song = $song;
        return $this;
    }

    public function Header(): void
    {
        if (!$this->config['header']['enabled']) {
            return;
        }
        $this->SetY($this->config['page']['margins']['top'] - 5);
        if ($this->reportTitle) {

            $this->SetFont('Courier', '', 10);
            $this->SetXY(171,14);
            $this->Cell(30, 0, 'Key: ' . $this->song->key, 0, 0, 'R');
            $this->SetXY(171,19);
            $this->Cell(30, 0, $this->song->tempo, 0, 0, 'R');
            $this->SetFont('Courier', 'B', 14);
            $this->Text(15, 16, $this->reportTitle);
            $this->SetFont('Courier', 'I', 10);
            $this->Text(15, 20, $this->song->author);
            $this->SetFont('Courier', '', 10);
        }
        $this->SetDrawColor(0,0,0);
        $this->SetXY(15,22);
        $this->Line(
            $this->config['page']['margins']['left'],
            $this->GetY(),
            $this->GetPageWidth() - $this->config['page']['margins']['right'],
            $this->GetY()
        );
        $this->Ln(5);

        // Song lyrics
        $lines=explode(PHP_EOL, $this->song->lyrics);
        $y=30;
        $vo=explode(" ",$this->song->verseorder);
        foreach ($lines as $line) {
            $line=$this->convert_smart_quotes($line);
            if (strpos($line, '}')) {
                $line=str_replace('{', '', $line);
                $line=str_replace('}', '', $line);
                $this->SetFont('Courier', 'B', 12);
                $this->SetTextColor(160, 160, 160);
                $y=$y+3.5;
                $shortline = substr($line, 0, 2);
                $this->text(13, $y, $shortline);
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
                        $this->text(170, $y+7, substr($vos,0,7));
                        $this->text(170, $y+12, substr(trim($vos," "),7));
                    } else {
                        $this->text(170, $y+7, substr($vos,0,6));
                        $this->text(170, $y+12, substr(trim($vos," "),6));
                    }
                } else {
                    $this->text(170, $y+7, $vos);
                }
                $this->SetTextColor(0, 0, 0);
            } else {
                $this->SetFont('Courier', '', 12);
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
                        $minlen=$this->GetStringWidth($chor);
                        $chordline.=$chor;
                        $this->SetFont('Courier', '', 12);
                        $i=$i+strlen($chor)+1;
                    } else {
                        $this->text($x, $y, $line[$i]);
                        if ($minlen ==0){
                            $chordline.=" ";
                        } else {
                            $minlen=$minlen-$this->GetStringWidth(" ");
                            if ($minlen < 0){
                                $minlen=0;
                            }
                        }
                        $x=$x+$this->GetStringWidth($line[$i]);
                    }
                }
                $this->SetFont('Courier', 'B', 12);
                $this->text(20, $y-3.5, $chordline);
                $this->SetFont('Courier', '', 12);
            }
            $y=$y+3.5;
        }

        // Chord list
        $this->SetTextColor(0,0,0);
        $y=26;
        $chords = $this->_getChords($this->song->lyrics);
        if (is_array($chords)){
            foreach ($chords as $chord) {
                $this->SetFont('Courier', '', 7);
                $dbchord = Chord::where('chord',$chord)->get();
                $x1=190;
                if (count($dbchord)) {
                    $this->setxy(180,$y);
                    $this->SetFont('Courier', 'B', 10);
                    $this->cell(30,5,$chord,0,0,'C');
                    if ($dbchord[0]->fret==0){
                        $this->line(190,$y+5,200,$y+5);
                        $f=0;
                    } else {
                        $f=1;
                        $this->text(202,$y+8,$dbchord[0]->fret);
                    }
                    for ($i=6;$i>0;$i--){
                        $svar="s" . $i;
                        if ($dbchord[0]->{$svar}=="x"){
                            $this->SetDrawColor(175,175,175);
                            $this->line($x1,$y+5,$x1,$y+17);
                        } else {
                            $this->SetDrawColor(0,0,0);
                            $this->line($x1,$y+5,$x1,$y+17);
                        }
                        $this->SetDrawColor(0,0,0);
                        $x1=$x1+2;
                        if ($i<6){
                            $this->line(190,2+$y+$i*3,200,2+$y+$i*3);
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
                            $this->SetFont('Courier', 'B', 14);
                            if ($cd > 0){
                                $this->SetFont('Courier', 'B', 20);
                                $circle=url('/') . "/images/circle.png";
                                $this->Image($circle,$x+0.5,$y+2.5+3*$cd,2,2);
                                $this->SetFont('Courier', 'B', 14);
                            }
                            $this->SetFont('Courier', '', 7);
                        }
                        $x=$x+2;
                    }
                } else {
                    $this->SetTextColor(125,125,125);
                    $this->setxy(180,$y);
                    $this->SetFont('Courier', 'B', 10);
                    $this->cell(30,5,$chord,0,0,'C');            
                    $this->SetTextColor(0,0,0);
                    $this->SetDrawColor(125,125,125);
                    for ($i=1;$i<7;$i++){
                        $this->line($x1,$y+5,$x1,$y+17);
                        $x1=$x1+2;
                        if ($i<6){
                            $this->line(190,2+$y+$i*3,200,2+$y+$i*3);
                        }
                    }
                    $this->SetFillColor(0,0,0);
                }
                $y=$y+18;
            }
        }
    }

    public function generate(): void
    {
        $this->setReportTitle($this->song->title);
        $this->AddPage();
    }

    protected function getFilename(): string
    {
        return 'song-report-' . now()->format('Y-m-d') . '.pdf';
    }

    private function _getChords($lyrics)
    {
        preg_match_all("/\[([^\]]*)\]/", $lyrics, $matches);
        $chords=array_unique($matches[1]);
        asort($chords);
        if (count($chords)) {
            return $chords;
        } else {
            return "";
        }
    }
}