<x-filament-widgets::widget>
    @php
        $cpu = $cpu ?? 0;
        $memory = $memory ?? ['percent' => 0];
        $disk = $disk ?? ['percent' => 0];
        $t = __('system-status-monitor::messages');
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            {{ $t['titles']['widget_title'] ?? 'System Status' }}
        </x-slot>

        @if(isset($error))
            <div class="text-red-600 text-sm font-semibold">{{ $error }}</div>
        @else
            <div class="space-y-4">
                <!-- CPU Metric -->
                @php
                    $cpuStatus = $cpu > 80 ? 'danger' : ($cpu > 60 ? 'warning' : 'success');
                    $cpuColor = $cpuStatus === 'danger' ? 'rgb(220, 38, 38)' : ($cpuStatus === 'warning' ? 'rgb(245, 158, 11)' : 'rgb(5, 150, 105)');
                    $cpuLightBg = "color-mix(in srgb, {$cpuColor} 15%, transparent)";
                @endphp
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">CPU</span>
                        <span class="text-sm font-bold">{{ $cpu }}%</span>
                    </div>
                    <div
                        class="relative rounded-full overflow-hidden w-full"
                        style="height: 0.725rem; background-color: {{ $cpuLightBg }};"
                        role="progressbar"
                        aria-valuenow="{{ $cpu }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-300 ease-in-out"
                            style="width: {{ $cpu }}%; background-color: {{ $cpuColor }};"
                        ></div>
                    </div>
                </div>

                <!-- Memory Metric -->
                @php
                    $memStatus = $memory['percent'] > 80 ? 'danger' : ($memory['percent'] > 60 ? 'warning' : 'success');
                    $memColor = $memStatus === 'danger' ? 'rgb(220, 38, 38)' : ($memStatus === 'warning' ? 'rgb(245, 158, 11)' : 'rgb(5, 150, 105)');
                    $memLightBg = "color-mix(in srgb, {$memColor} 15%, transparent)";
                @endphp
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Memory</span>
                        <span class="text-sm font-bold">{{ $memory['percent'] }}%</span>
                    </div>
                    <div
                        class="relative rounded-full overflow-hidden w-full"
                        style="height: 0.725rem; background-color: {{ $memLightBg }};"
                        role="progressbar"
                        aria-valuenow="{{ $memory['percent'] }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-300 ease-in-out"
                            style="width: {{ $memory['percent'] }}%; background-color: {{ $memColor }};"
                        ></div>
                    </div>
                </div>

                <!-- Disk Metric -->
                @php
                    $diskStatus = $disk['percent'] > 80 ? 'danger' : ($disk['percent'] > 60 ? 'warning' : 'success');
                    $diskColor = $diskStatus === 'danger' ? 'rgb(220, 38, 38)' : ($diskStatus === 'warning' ? 'rgb(245, 158, 11)' : 'rgb(5, 150, 105)');
                    $diskLightBg = "color-mix(in srgb, {$diskColor} 15%, transparent)";
                @endphp
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Disk</span>
                        <span class="text-sm font-bold">{{ $disk['percent'] }}%</span>
                    </div>
                    <div
                        class="relative rounded-full overflow-hidden w-full"
                        style="height: 0.725rem; background-color: {{ $diskLightBg }};"
                        role="progressbar"
                        aria-valuenow="{{ $disk['percent'] }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div
                            class="h-full rounded-full transition-all duration-300 ease-in-out"
                            style="width: {{ $disk['percent'] }}%; background-color: {{ $diskColor }};"
                        ></div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>