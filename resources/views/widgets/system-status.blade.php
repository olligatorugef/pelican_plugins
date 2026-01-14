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
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">CPU</span>
                        <span class="text-sm font-bold">{{ $cpu }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div 
                            class="h-1.5 rounded-full transition-all"
                            style="width: {{ $cpu }}%; background-color: {{ $cpu > 80 ? '#dc2626' : ($cpu > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                </div>

                <!-- Memory Metric -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Memory</span>
                        <span class="text-sm font-bold">{{ $memory['percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div 
                            class="h-1.5 rounded-full transition-all"
                            style="width: {{ $memory['percent'] }}%; background-color: {{ $memory['percent'] > 80 ? '#dc2626' : ($memory['percent'] > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                </div>

                <!-- Disk Metric -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">Disk</span>
                        <span class="text-sm font-bold">{{ $disk['percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                        <div 
                            class="h-1.5 rounded-full transition-all"
                            style="width: {{ $disk['percent'] }}%; background-color: {{ $disk['percent'] > 80 ? '#dc2626' : ($disk['percent'] > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>