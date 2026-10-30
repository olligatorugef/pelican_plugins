@php
    $t = fn (string $k) => __('system-status-monitor::messages.' . $k);
    $data = array_merge([
        'cpu' => 0,
        'cpu_cores' => 0,
        'cpu_model' => 'Unknown',
        'memory' => ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
        'swap' => ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
        'disk' => ['used' => 'N/A', 'total' => 'N/A', 'free' => 'N/A', 'percent' => 0],
        'load' => ['1min' => 0, '5min' => 0, '15min' => 0],
        'uptime' => 'Unknown',
        'last_reboot' => 'Unknown',
        'processes' => ['total' => 0],
        'hostname' => 'Unknown',
        'os' => 'Unknown',
        'php_version' => PHP_VERSION,
        'container' => false,
    ], $data ?? []);

    $grid3 = 'display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;';
@endphp

<x-filament-panels::page>
    <div wire:poll.10s="refreshData" style="display: flex; flex-direction: column; gap: 1.5rem;">

        @if(isset($data['error']))
            <x-filament::section>
                <x-slot name="heading">{{ $t('status.unavailable') }}</x-slot>
                <p class="text-sm text-red-600">{{ $data['error'] }}</p>
            </x-filament::section>
        @else
            {{-- ========== Panel server ========== --}}
            <x-filament::section>
                <x-slot name="heading">{{ $t('titles.panel_server') }} — {{ $data['hostname'] }}</x-slot>
                <x-slot name="description">
                    {{ $data['os'] }}@if($data['container']) • Docker @endif • {{ $t('status.online') }} — {{ date('d/m/Y H:i:s') }}
                </x-slot>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    {{-- CPU --}}
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="{{ $grid3 }}">
                            @include('system-status-monitor::partials.stat', ['label' => $t('labels.cpu_model'), 'value' => $data['cpu_model']])
                            @include('system-status-monitor::partials.stat', ['label' => $t('labels.cores'), 'value' => $data['cpu_cores']])
                            @include('system-status-monitor::partials.stat', ['label' => $t('titles.load_average'), 'value' => $data['load']['1min'] . ' / ' . $data['load']['5min'] . ' / ' . $data['load']['15min']])
                        </div>
                        @include('system-status-monitor::partials.meter', ['label' => $t('titles.cpu_usage'), 'value' => $data['cpu']])
                    </div>

                    {{-- Mémoire --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                        @include('system-status-monitor::partials.meter', [
                            'label' => $t('titles.memory_usage'),
                            'value' => $data['memory']['percent'],
                            'detail' => $data['memory']['used'] . ' / ' . $data['memory']['total'],
                        ])
                        @include('system-status-monitor::partials.meter', [
                            'label' => $t('labels.swap_memory'),
                            'value' => $data['swap']['percent'],
                            'detail' => $data['swap']['used'] . ' / ' . $data['swap']['total'],
                            'warn' => 50,
                        ])
                        @include('system-status-monitor::partials.meter', [
                            'label' => $t('titles.disk_usage'),
                            'value' => $data['disk']['percent'],
                            'detail' => $data['disk']['used'] . ' / ' . $data['disk']['total'] . ' (' . $t('labels.free') . ': ' . ($data['disk']['free'] ?? 'N/A') . ')',
                        ])
                    </div>

                    {{-- Système --}}
                    <div style="{{ $grid3 }}">
                        @include('system-status-monitor::partials.stat', ['label' => $t('labels.php_version'), 'value' => $data['php_version']])
                        @include('system-status-monitor::partials.stat', ['label' => $t('labels.processes'), 'value' => ($data['processes']['total'] ?? 'N/A') . (isset($data['processes']['running']) ? ' (' . $data['processes']['running'] . ' ' . strtolower($t('labels.running')) . ')' : '')])
                        @include('system-status-monitor::partials.stat', ['label' => $t('titles.uptime'), 'value' => $data['uptime']])
                        @include('system-status-monitor::partials.stat', ['label' => $t('labels.last_reboot'), 'value' => $data['last_reboot']])
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- ========== Nodes ========== --}}
        @if($canViewNodes)
            <x-filament::section>
                <x-slot name="heading">{{ $t('titles.nodes') }}</x-slot>
                <x-slot name="description">{{ $t('titles.nodes_description') }}</x-slot>

                @if($this->nodesError)
                    <p class="text-sm text-red-600">{{ $this->nodesError }}</p>
                @elseif(count($nodes) === 0)
                    <p class="text-sm" style="opacity: .65;">{{ $t('labels.no_nodes') }}</p>
                @else
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                        @foreach($nodes as $node)
                            @include('system-status-monitor::partials.node-card', ['node' => $node])
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
