<x-filament-panels::page>
    {{-- Header with year navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <x-filament::button wire:click="previousYear" icon="heroicon-o-chevron-left" size="sm" />

            <h1 class="text-2xl font-bold">
                Service plans – {{ $year }}
            </h1>

            <x-filament::button wire:click="nextYear" icon="heroicon-o-chevron-right" size="sm" />
        </div>
    </div>

    @php
        /**
         * Build a single chronological list of planner days
         * (Sundays + midweek services), grouped by month.
         */
        $daysByMonth = collect($this->getPlannerDays())
            ->map(fn ($day, $key) => array_merge($day, ['key' => $key]))
            ->groupBy(fn ($day) => \Carbon\Carbon::parse($day['key'])->month);
    @endphp

    {{-- Year grid: grouped by month --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($daysByMonth as $month => $days)
            <x-filament::section>
                <x-slot name="heading">
                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                </x-slot>

                <div class="grid grid-cols-2 gap-3">
                    @foreach ($days as $day)
                        @php
                            $key  = $day['key'];
                            $date = \Carbon\Carbon::parse($key);
                            $plan = $this->plans[$key] ?? null;
                        @endphp

                        <div
                            class="cursor-pointer rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md
                                   dark:border-gray-700 dark:bg-gray-800"
                            wire:click="$set('selectedDate', '{{ $key }}'); $wire.mountAction('editPlan')"
                        >
                            {{-- Date + type --}}
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span>{{ $date->format('j M') }}</span>

                                @if ($day['type'] === 'special')
                                    <span
                                        class="inline-flex items-center gap-1 rounded bg-amber-100 px-2 py-0.5 text-xs text-amber-800
                                               dark:bg-amber-900 dark:text-amber-200"
                                    >
                                        {{ $day['label'] }}
                                    </span>
                                @endif
                            </div>

                            @if ($plan)
                                {{-- Series image --}}
                                @if (!empty($plan['series']['image']))
                                    <img
                                        src="{{ asset('storage/' . $plan['series']['image']) }}"
                                        alt=""
                                        class="my-2 h-20 w-full rounded object-cover"
                                    >
                                @endif

                                {{-- Series title --}}
                                <div
                                    class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100"
                                    style="border-left: 4px solid {{ $plan['series']['colour'] ?? '#0d9488' }}; padding-left: 0.5rem"
                                >
                                    {{ $plan['series']['series'] ?? 'No series' }}
                                </div>

                                {{-- Details --}}
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400"> 
                                    @if ($plan['details']) 
                                        <i>{{ $plan['details'] }}</i><br> 
                                    @endif 
                                    @if ($plan['person_id']) 
                                        <strong>Preacher:</strong> {{ $plan['person']['firstname'] }} {{ $plan['person']['surname'] }} 
                                    @endif 
                                    @if ($plan['reading']) 
                                        | <strong>Reading:</strong> {{ $plan['reading'] }} 
                                    @endif 
                                </div>

                                {{-- Song / liturgy ideas --}}
                                @if (!empty($plan['setitems'])) 
                                    <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-400"> 
                                        <strong>Song / liturgy ideas: </strong> 
                                        @foreach ($plan['setitems'] as $item) 
                                            @if ($item['content_type'] === 'song') 
                                                <span>🎵 {{ $item['song']['title'] ?? '' }}</span> 
                                            @elseif ($item['content_type'] === 'prayer') 
                                                <span>📖 {{ $item['prayer']['title'] ?? '' }}</span> 
                                            @endif 
                                        @endforeach 
                                    </div> 
                                @endif

                                {{-- Service created badge --}}
                                @if (($plan['services_count'] ?? 0) > 0)
                                    <a
                                        href="{{ route(
                                            'filament.admin.worship.resources.services.index',
                                            ['tableFilters[servicedate][value]' => $key]
                                        ) }}"
                                        class="mt-2 inline-flex items-center gap-1 text-xs text-success-600 hover:underline
                                               dark:text-success-400"
                                    >
                                        <x-filament::icon icon="heroicon-m-check-circle" class="h-4 w-4" />
                                        <span>
                                            {{ $plan['services_count'] === 1
                                                ? 'Service created'
                                                : $plan['services_count'] . ' services created'
                                            }}
                                        </span>
                                    </a>
                                @endif
                            @else
                                <div class="mt-2 text-sm italic text-gray-400">
                                    + Assign series
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>
