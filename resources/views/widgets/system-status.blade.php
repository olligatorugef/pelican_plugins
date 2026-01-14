<x-filament-widgets::widget>
    @php
        $cpu = $cpu ?? 0;
        $memory = $memory ?? ['percent' => 0];
        $disk = $disk ?? ['percent' => 0];
        $t = __('system-status-monitor::messages');
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            ⚡ {{ $t['titles']['widget_title'] ?? 'System Status' }}
        </x-slot>

        @if(isset($error))
            <div class="text-red-600 text-sm font-semibold">{{ $error }}</div>
        @else
            <div class="space-y-4">
                <!-- CPU -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">💻 CPU</span>
                        <span class="text-lg font-bold" style="color: {{ $cpu > 80 ? '#dc2626' : ($cpu > 60 ? '#f59e0b' : '#059669') }}">{{ $cpu }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="background-color: {{ $cpu > 80 ? '#dc2626' : ($cpu > 60 ? '#f59e0b' : '#059669') }}; width: {{ $cpu }}%"></div>
                    </div>
                </div>

                <!-- Memory -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">🧠 Memory</span>
                        <span class="text-lg font-bold" style="color: {{ $memory['percent'] > 80 ? '#dc2626' : ($memory['percent'] > 60 ? '#f59e0b' : '#059669') }}">{{ $memory['percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="background-color: {{ $memory['percent'] > 80 ? '#dc2626' : ($memory['percent'] > 60 ? '#f59e0b' : '#059669') }}; width: {{ $memory['percent'] }}%"></div>
                    </div>
                </div>

                <!-- Disk -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-semibold text-gray-700">💾 Disk</span>
                        <span class="text-lg font-bold" style="color: {{ $disk['percent'] > 80 ? '#dc2626' : ($disk['percent'] > 60 ? '#f59e0b' : '#059669') }}">{{ $disk['percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="background-color: {{ $disk['percent'] > 80 ? '#dc2626' : ($disk['percent'] > 60 ? '#f59e0b' : '#059669') }}; width: {{ $disk['percent'] }}%"></div>
                    </div>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
