<x-filament-panels::page>
    {{-- Header with year navigation --}}
    <div class="flex items-center justify-between">
        <div class="flex">
            <x-filament::button wire:click="previousYear" icon="heroicon-o-chevron-left" size="sm" />&nbsp;&nbsp;
            <h1 class="text-2xl font-bold">Service plans – {{ $year }}</h1>&nbsp;&nbsp;
            <x-filament::button wire:click="nextYear" icon="heroicon-o-chevron-right" size="sm" />
        </div>
    </div>

    {{-- Year grid: 12 months --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach ($this->getSundaysByMonth() as $month => $sundays)
            <x-filament::section>
                <x-slot name="heading">
                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                </x-slot>

                {{-- Sundays as individual cards in a row --}}
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($sundays as $date)
                        @php
                            $key = $date->toDateString();
                            $plan = $this->plans[$key] ?? null; 
                        @endphp
                        
                        <div
                            class="cursor-pointer rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                            wire:click="$set('selectedDate', '{{ $key }}'); $wire.mountAction('editPlan')"
                        >
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $date->format('j M') }}
                            </div>
                            
                            @if ($plan) {{-- Check if a plan exists first --}}
                                @if (!empty($plan['series']['image']))
                                    <img src="{{ asset('storage/' . $plan['series']['image']) }}"
                                        alt=""
                                        class="my-2 h-20 w-full rounded object-cover">
                                @endif
                                <div class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100"
                                    style="border-left: 4px solid {{ $plan['series']['colour'] ?? '#0d9488' }}; padding-left: 0.5rem">
                                    {{ $plan['series']['series'] ?? 'No Series' }}
                                </div>
                                @if ($plan['details'])
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $plan['details'] }}
                                    </div>
                                @endif
                                @if ($plan['reading'])
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        <strong>Bible Reading:</strong> {{ $plan['reading'] }}
                                    </div>
                                @endif
                                @if ($plan['person_id'])
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                        {{ $plan['person']['firstname'] }} {{ $plan['person']['surname'] }}
                                    </div>
                                @endif
                            @else
                                <div class="mt-2 text-sm italic text-gray-400">+ Assign series</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @endforeach
    </div>
</x-filament-panels::page>