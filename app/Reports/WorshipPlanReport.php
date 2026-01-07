<?php

namespace Modules\Worship\Reports;

use Illuminate\Support\Facades\Route;
use Lightworx\FilamentReports\Reports\BaseReport;
use Modules\Worship\Models\Prayer;
use Modules\Worship\Models\Series;
use Modules\Worship\Models\Service;
use Modules\Worship\Models\ServicePlan;
use Modules\Worship\Models\Song;

class WorshipPlanReport extends BaseReport
{
    protected $plans;

    public function __construct()
    {
        parent::__construct();
    }

    public static function routes(): void
    {
        Route::get('/admin/worship/reports/worshipplan/{id}', function ($planId) {
            $plans = ServicePlan::with(['series', 'person', 'setitems.song', 'setitems.prayer'])->whereYear('date', $planId)->get()
                ->keyBy(fn ($plan) => $plan->date->toDateString())
                ->toArray();
            return (new static())->setPlans($plans)->handle();
        })->name('reports.worshipplan');
    }

    public function setPlans($plans): static
    {
        $this->plans = $plans;
        dd($plans);
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
        $this->setReportTitle("Worship plan for ");
        $this->AddPage('P');
        $filename = $this->getFilename();
        $this->Output('I',$filename);
        exit;
    }

    protected function getFilename(): string
    {
        return 'worshipplan-report-' . now()->format('Y-m-d') . '.pdf';
    }
}