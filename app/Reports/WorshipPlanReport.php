<?php

namespace Modules\Worship\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Modules\Worship\Models\ServicePlan;

class WorshipPlanReport extends BaseReport
{
    protected int $year;
    protected $plans;

    public function __construct()
    {
        parent::__construct();
    }

    public static function routes(): void
    {
        Route::get('/admin/worship/reports/worshipplan/{year}', function (int $year) {
            $plans = ServicePlan::with([
                    'series',
                    'person',
                    'midweekservices',
                    'setitems.song',
                    'setitems.prayer',
                ])
                ->whereYear('date', $year)
                ->orderBy('date')
                ->get();

            return (new static())
                ->setYear($year)
                ->setPlans($plans)
                ->handle();
        })->name('reports.worshipplan');
    }

    public function setYear($year): static
    {
        $this->year = $year;
        return $this;
    }

    public function setPlans($plans): static
    {
        $this->plans = $plans;
        return $this;
    }

    public function Header(): void
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, "Worship Plan: {$this->year}", 0, 1, 'C');
        $this->Ln(2);
    }

    public function Footer(): void
    {
        $this->SetY(-10);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 8, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }


    public function generate(): void
    {
        $this->setReportTitle("Worship Plan " . $this->year);
        $this->AddPage('L');
        $this->SetFont('Arial', '', 9);

        $this->renderTableHeader();

        foreach ($this->plans as $plan) {
            $this->renderRow($plan);
        }

        $this->Output('I', $this->getFilename());
        exit;
    }

    protected function renderTableHeader(): void
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(240);

        $this->Cell(22, 6, 'Date', 1, 0, 'L', true);
        $this->Cell(60, 6, 'Series', 1, 0, 'L', true);
        $this->Cell(45, 6, 'Preacher', 1, 0, 'L', true);
        $this->Cell(140, 6, 'Bible reading', 1, 1, 'L', true);

        $this->SetFont('Arial', '', 9);
    }

    protected function renderRow($plan): void
    {
        if ($this->GetY() > 185) {
            $this->AddPage('L');
            $this->renderTableHeader();
        }

        $label = $plan->midweekService?->name
            ? $plan->date->format('j M') . ' — ' . $plan->midweekService->name
            : $plan->date->format('j M');

        $this->Cell(22, 5, $label, 1);
        $this->Cell(60, 5, $plan->series->series ?? '', 1);
        $this->Cell(45, 5, $plan->person->fullname ?? '', 1);
        $this->Cell(140, 5, $plan->reading ?? '', 1);

        $this->Ln();
    }

    protected function getFilename(): string
    {
        return 'worshipplan-report-' . now()->format('Y-m-d') . '.pdf';
    }
}