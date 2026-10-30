@php
    $value = max(0, min(100, (float) ($value ?? 0)));
    $warn = $warn ?? 60;
    $crit = $crit ?? 80;
    $color = $value > $crit ? 'rgb(220, 38, 38)' : ($value > $warn ? 'rgb(245, 158, 11)' : 'rgb(5, 150, 105)');
@endphp
<div style="display: flex; flex-direction: column; gap: 0.35rem;">
    <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem;">
        <span class="text-sm font-medium">{{ $label }}</span>
        <span class="text-sm font-bold">{{ rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') }}%</span>
    </div>
    <div
        role="progressbar"
        aria-valuenow="{{ $value }}"
        aria-valuemin="0"
        aria-valuemax="100"
        style="position: relative; width: 100%; height: 0.725rem; border-radius: 9999px; overflow: hidden; background-color: color-mix(in srgb, {{ $color }} 15%, transparent);"
    >
        <div style="height: 100%; width: {{ $value }}%; border-radius: 9999px; background-color: {{ $color }}; transition: width .3s ease-in-out;"></div>
    </div>
    @if(!empty($detail))
        <span class="text-xs" style="opacity: .65;">{{ $detail }}</span>
    @endif
</div>
