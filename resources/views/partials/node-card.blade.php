@php $t = fn (string $k) => __('system-status-monitor::messages.' . $k); @endphp
<div style="padding: 1rem; border-radius: 0.75rem; border: 1px solid color-mix(in srgb, currentColor 12%, transparent); display: flex; flex-direction: column; gap: 0.75rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
        <div style="min-width: 0;">
            <p class="text-base font-bold" style="word-break: break-word;">{{ $node['name'] }}</p>
            <p class="text-xs" style="opacity: .65; word-break: break-all;">{{ $node['fqdn'] }}</p>
        </div>
        <span
            class="text-xs font-semibold"
            style="white-space: nowrap; padding: 0.15rem 0.6rem; border-radius: 9999px; color: {{ $node['online'] ? 'rgb(5, 150, 105)' : 'rgb(220, 38, 38)' }}; background-color: color-mix(in srgb, {{ $node['online'] ? 'rgb(5, 150, 105)' : 'rgb(220, 38, 38)' }} 15%, transparent);"
        >
            {{ $node['online'] ? $t('status.node_online') : $t('status.node_offline') }}
        </span>
    </div>

    @if($node['online'])
        @include('system-status-monitor::partials.meter', [
            'label' => 'CPU',
            'value' => $node['cpu'],
            'detail' => $node['cpu_cores'] > 0 ? $node['cpu_cores'] . ' ' . $t('labels.cores') : null,
        ])
        @include('system-status-monitor::partials.meter', [
            'label' => $t('labels.memory'),
            'value' => $node['memory']['percent'],
            'detail' => $node['memory']['used'] . ' / ' . $node['memory']['total'],
        ])
        @if($node['swap']['percent'] > 0 || $node['swap']['total'] !== '0 B')
            @include('system-status-monitor::partials.meter', [
                'label' => 'Swap',
                'value' => $node['swap']['percent'],
                'detail' => $node['swap']['used'] . ' / ' . $node['swap']['total'],
                'warn' => 50,
                'crit' => 80,
            ])
        @endif
        @include('system-status-monitor::partials.meter', [
            'label' => $t('labels.disk'),
            'value' => $node['disk']['percent'],
            'detail' => $node['disk']['used'] . ' / ' . $node['disk']['total'],
        ])

        <div class="text-xs" style="opacity: .75; display: flex; flex-wrap: wrap; gap: 0.25rem 1rem;">
            <span>{{ $t('titles.load_average') }}: {{ $node['load']['1min'] }} / {{ $node['load']['5min'] }} / {{ $node['load']['15min'] }}</span>
            @if($node['version'])<span>Wings {{ $node['version'] }}</span>@endif
            @if($node['os'])<span>{{ $node['os'] }}</span>@endif
        </div>
    @else
        <p class="text-sm" style="color: rgb(220, 38, 38);">
            {{ $node['error'] ?: $t('status.node_unreachable') }}
        </p>
    @endif
</div>
