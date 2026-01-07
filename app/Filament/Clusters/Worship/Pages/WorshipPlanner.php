<?php

namespace Modules\Worship\Filament\Clusters\Worship\Pages;

use App\Models\Person;
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
use Modules\Worship\Models\Prayer;
use Modules\Worship\Models\ServicePlan;
use Modules\Worship\Models\Series;
use Modules\Worship\Models\Setitem;
use Modules\Worship\Models\Song;

class WorshipPlanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;
    protected string $view = 'worship::worship-planner';
    protected static ?string $cluster = WorshipCluster::class;
    protected static ?int $navigationSort = 5;

    public bool $isEditorOpen = false;
    public ?string $selectedDate = null;
    public ?string $details = null;
    public ?string $reading = null;
    public ?string $person_id = null;
    public ?array $plans = null;
    public ?int $activePlanId = null;
    public array $newItem = ['type' => null, 'content_id' => null, 'title' => null];
    public int $year;

    public function mount(): void
    {
        $this->year = now()->year;
        $this->loadPlans();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make($this->year . ' Worship plan')->url(fn (): string => route('reports.worshipplan', ['id' => $this->year])),
        ];
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

        $plan = ServicePlan::firstOrCreate([
            'date' => $date,
        ]);
        $this->form->fill([
            'selectedSeriesId' => $plan?->series_id,
            'details' => $plan?->details,
            'reading' => $plan?->reading,
            'person_id' => $plan?->person_id
        ]);
        $this->isEditorOpen = true;
        $this->activePlanId = $plan->id;
    }


    public function savePlan(): void
    {
        $data = $this->form->getState();

        ServicePlan::updateOrCreate(
            ['date' => $this->selectedDate],
            [
                'series_id' => $data['selectedSeriesId'] ?? null,
                'details' => $data['details'] ?? null,
                'reading' => $data['reading'] ?? null,
                'person_id' => $data['person_id'] ?? null,
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

            TextInput::make('details')
                ->label('details')
                ->maxLength(255),

            TextInput::make('reading')
                ->label('Bible Reading')
                ->maxLength(255),

            Select::make('person_id')
                ->label('Preacher')
                ->options(function () {
                    $persons = Person::orderBy('firstname')->get();
                    $options = array();
                    foreach ($persons as $person) {
                        if (in_array('Preacher', $person->role)) {
                            $options[$person->id] = $person->firstname . ' ' . $person->surname;
                        }
                    }
                    return $options;
                })
                ->searchable()
                ->placeholder('Select a preacher'),
            Select::make('songs')
                ->label('Song ideas')
                ->multiple()
                ->searchable()
                ->options(
                    Song::orderBy('title')
                        ->pluck('title', 'id')
                ),

            Select::make('prayers')
                ->label('Liturgy ideas')
                ->multiple()
                ->searchable()
                ->options(
                    Prayer::orderBy('title')
                        ->pluck('title', 'id')
                ),
        ];
    }

    protected function loadPlans(): void
    {
        $this->plans = ServicePlan::with(['series', 'person', 'setitems.song', 'setitems.prayer'])
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

                TextInput::make('details')
                    ->label('Details')
                    ->maxLength(255),

                TextInput::make('reading')
                    ->label('Bible Reading')
                    ->maxLength(255),

                Select::make('person_id')
                    ->label('Preacher')
                    ->options(function () {
                        $persons = Person::orderBy('firstname')->get();
                        $options = array();
                        foreach ($persons as $person) {
                            if (in_array('Preacher', $person->role)) {
                                $options[$person->id] = $person->firstname . ' ' . $person->surname;
                            }
                        }
                        return $options;
                    })
                    ->searchable()
                    ->placeholder('Select a preacher'),
            Select::make('songs')
                ->label('Song ideas')
                ->multiple()
                ->searchable()
                ->options(
                    Song::orderBy('title')
                        ->pluck('title', 'id')
                ),

            Select::make('prayers')
                ->label('Liturgy ideas')
                ->multiple()
                ->searchable()
                ->options(
                    Prayer::orderBy('title')
                        ->pluck('title', 'id')
                ),
            ])
            ->mountUsing(function (Schema $form) {
                $plan = ServicePlan::with('setitems')
                    ->where('date', $this->selectedDate)
                    ->first();

                $form->fill([
                    'series_id' => $plan?->series_id,
                    'details'   => $plan?->details,
                    'reading'   => $plan?->reading,
                    'person_id'=> $plan?->person_id,
                    'songs'     => $plan?->songSetitems()->pluck('content_id')->toArray() ?? [],
                    'prayers' => $plan?->prayerSetitems()->pluck('content_id')->toArray() ?? [],
                ]);
            })
            ->action(function (array $data) {
                $plan = ServicePlan::updateOrCreate(
                    ['date' => $this->selectedDate],
                    [
                        'series_id' => $data['series_id'] ?? null,
                        'details' => $data['details'] ?? null,
                        'reading' => $data['reading'] ?? null,
                        'person_id' => $data['person_id'] ?? null,
                    ]
                );

                // Clear existing provisional set items
                $plan->setitems()->delete();

                $order = 1;

                foreach ($data['songs'] ?? [] as $songId) {
                    $plan->setitems()->create([
                        'content_id'   => $songId,
                        'content_type' => 'song',
                        'sort_order'   => $order++,
                    ]);
                }

                foreach ($data['prayers'] ?? [] as $prayerId) {
                    $plan->setitems()->create([
                        'content_id'   => $prayerId,
                        'content_type' => 'prayer',
                        'sort_order'   => $order++,
                    ]);
                }

                $this->loadPlans(); 

                Notification::make()
                ->title('Plan updated successfully')
                ->success()
                ->send();
            });
    }

}