@php $t = fn (string $k) => __('system-status-monitor::messages.' . $k); @endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ $t('titles.widget_title') }}</x-slot>

        <div wire:poll.10s style="display: flex; flex-direction: column; gap: 1.5rem;">
            @if($error)
                <div class="text-red-600 text-sm font-semibold">{{ $error }}</div>
            @elseif($panel)
                <div>
                    <h3 class="text-sm font-semibold" style="margin-bottom: 0.75rem;">{{ $t('titles.panel_server') }}</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                        @include('system-status-monitor::partials.meter', ['label' => 'CPU', 'value' => $panel['cpu']])
                        @include('system-status-monitor::partials.meter', [
                            'label' => $t('labels.memory'),
                            'value' => $panel['memory']['percent'],
                            'detail' => $panel['memory']['used'] . ' / ' . $panel['memory']['total'],
                        ])
                        @include('system-status-monitor::partials.meter', [
                            'label' => $t('labels.disk'),
                            'value' => $panel['disk']['percent'],
                            'detail' => $panel['disk']['used'] . ' / ' . $panel['disk']['total'],
                        ])
                    </div>
                </div>
            @endif

            @if(count($nodes))
                <div>
                    <h3 class="text-sm font-semibold" style="margin-bottom: 0.75rem;">{{ $t('titles.nodes') }}</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem;">
                        @foreach($nodes as $node)
                            @include('system-status-monitor::partials.node-card', ['node' => $node])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
