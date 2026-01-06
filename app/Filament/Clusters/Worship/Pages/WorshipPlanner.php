<?php

namespace Modules\Worship\Filament\Clusters\Worship\Pages;

use Filament\Pages\Page;
use Carbon\Carbon;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Worship\Filament\Clusters\Worship\WorshipCluster;
use Modules\Worship\Models\ServicePlan;
use Modules\Worship\Models\Series;

class WorshipPlanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected string $view = 'worship::worship-planner';
    protected static ?string $cluster = WorshipCluster::class;
    protected static ?int $navigationSort = 5;

    public bool $isEditorOpen = false;
    public ?string $selectedDate = null;
    public ?string $theme = null;
    public ?array $plans = null;
    public int $year;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->loadPlans();
    }

    protected function getActions(): array
    {
        return [
            $this->editPlanAction(),
        ];
    }

    public function previousYear(): void
    {
        $this->year--;
        $this->loadPlans();
    }

    public function nextYear(): void
    {
        $this->year++;
        $this->loadPlans();
    }

    public function openEditor(string $date): void
    {
        $this->selectedDate = $date;

        $plan = ServicePlan::where('date', $date)->first();

        $this->form->fill([
            'selectedSeriesId' => $plan?->series_id,
            'theme' => $plan?->theme,
        ]);

        $this->isEditorOpen = true;
    }


    public function savePlan(): void
    {
        $data = $this->form->getState();

        ServicePlan::updateOrCreate(
            ['date' => $this->selectedDate],
            [
                'series_id' => $data['selectedSeriesId'] ?? null,
                'theme' => $data['theme'] ?? null,
            ]
        );

        $this->loadPlans();

        $this->isEditorOpen = false;
        $this->loadPlans();
    }


    public function getSundaysByMonth(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($this->year, $month, 1);
            $end   = $start->copy()->endOfMonth();

            $sundays = [];

            // calculate first Sunday
            $diff = (7 - $start->dayOfWeek) % 7;
            $date = $start->copy()->addDays($diff);

            while ($date->lte($end)) {
                $sundays[] = $date->copy();
                $date->addWeek();
            }

            $months[$month] = $sundays;
        }

        return $months;
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('selectedSeriesId')
                ->label('Sermon Series')
                ->options(
                    Series::pluck('series', 'id')
                )
                ->searchable()
                ->placeholder('Select a series'),

            TextInput::make('theme')
                ->label('Theme')
                ->maxLength(255),
        ];
    }

    protected function loadPlans(): void
    {
        $this->plans = ServicePlan::with('series')
            ->whereYear('date', $this->year)
            ->get()
            ->keyBy(fn ($plan) => $plan->date->toDateString())
            ->toArray();
    }

    public function editPlanAction(): Action
    {
        return Action::make('editPlan')
            ->label('Edit Service Plan')
            ->slideOver()
            ->modalHeading(fn () => "Edit Service Plan — {$this->selectedDate}")
            ->schema([
                Select::make('series_id')
                    ->label('Sermon Series')
                    ->options(
                        Series::pluck('series', 'id')
                    )
                    ->searchable()
                    ->placeholder('Select a series'),

                TextInput::make('theme')
                    ->label('Theme')
                    ->maxLength(255),
            ])
            ->mountUsing(function (Schema $form) {
                $plan = ServicePlan::where('date', $this->selectedDate)->first();

                $form->fill([
                    'series_id' => $plan?->series_id,
                    'theme' => $plan?->theme,
                ]);
            })
            ->action(function (array $data) {
                ServicePlan::updateOrCreate(
                    ['date' => $this->selectedDate],
                    [
                        'series_id' => $data['series_id'] ?? null,
                        'theme' => $data['theme'] ?? null,
                    ]
                );

                $this->loadPlans(); 

                Notification::make()
                ->title('Plan updated successfully')
                ->success()
                ->send();
            });
    }

}