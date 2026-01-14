<x-filament-panels::page>
    @php
        if (!isset($data) || !is_array($data)) {
            $data = [];
        }
        
        $translations = __('system-status-monitor::messages') ?? [];
        
        $t = [
            'status' => $translations['status'] ?? [
                'excellent' => 'Excellent',
                'good' => 'Good',
                'warning' => 'Warning',
                'critical' => 'Critical',
                'online' => 'Online',
                'unavailable' => 'Unavailable'
            ],
            'titles' => $translations['titles'] ?? [
                'cpu_usage' => 'CPU Usage',
                'cpu_details' => 'CPU Details',
                'memory_usage' => 'Memory Usage',
                'disk_usage' => 'Disk Usage',
                'load_average' => 'Load Average',
                'system_info' => 'System Information',
                'uptime' => 'Uptime',
                'performance' => 'Performance',
                'process_info' => 'Process Information',
            ],
            'labels' => $translations['labels'] ?? [
                'os' => 'Operating System',
                'php_version' => 'PHP Version',
                'cores' => 'Cores',
                'threads' => 'Threads',
                'cpu_model' => 'CPU Model',
                'swap_memory' => 'Virtual Memory',
                'processes' => 'Processes',
                'running' => 'Running',
                'idle' => 'Idle',
                'last_reboot' => 'Last Reboot',
                'mounted_points' => 'Mounted Points',
                'inode_usage' => 'Inode Usage',
                'memory' => 'Memory',
                'used' => 'Used',
                'total' => 'Total',
                'free' => 'Free',
                'one_min' => '1 minute',
                'five_min' => '5 minutes',
                'fifteen_min' => '15 minutes',
            ]
        ];
        
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
            'php_version' => phpversion(),
        ], $data);
    @endphp

    @if(isset($data['error']))
        <x-filament::section>
            <x-slot name="heading">{{ $t['status']['unavailable'] ?? 'Unavailable' }}</x-slot>
            <p class="text-sm text-red-600">{{ $data['error'] }}</p>
        </x-filament::section>
    @else
        <!-- En-tête principal -->
        <div class="p-8 text-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2 text-white">{{ config('app.name') }}</h1>
                    <p class="text-gray-300 text-lg">{{ $data['hostname'] ?? 'System' }} • {{ $data['os'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-white">{{ $t['status']['online'] ?? 'Online' }}</p>
                    <p class="text-gray-300 text-sm mt-1">{{ date('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- CPU Section -->
        <x-filament::section>
            <x-slot name="heading">{{ $t['titles']['cpu_details'] }}</x-slot>
            <x-slot name="description">{{ $t['titles']['cpu_description'] ?? 'Real-time CPU information' }}</x-slot>

            <div class="space-y-6">
                <!-- CPU Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['cpu_model'] }}</p>
                            <p class="text-lg font-bold">{{ $data['cpu_model'] ?? 'Unknown' }}</p>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['cores'] }}</p>
                            <p class="text-lg font-bold">{{ $data['cpu_cores'] ?? 'N/A' }}</p>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['titles']['cpu_usage'] }}</p>
                            <p class="text-lg font-bold">{{ $data['cpu'] }}%</p>
                        </div>
                    </x-filament::card>
                </div>

                <!-- CPU Usage Progress -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $t['titles']['cpu_usage'] }}</span>
                        <span class="text-sm font-bold">{{ $data['cpu'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-2 rounded-full transition-all"
                            style="width: {{ $data['cpu'] }}%; background-color: {{ $data['cpu'] > 80 ? '#dc2626' : ($data['cpu'] > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $data['cpu'] > 80 ? $t['status']['critical'] : ($data['cpu'] > 60 ? $t['status']['warning'] : $t['status']['excellent']) }}
                    </p>
                </div>

                <!-- Load Average -->
                <div class="space-y-3">
                    <h3 class="text-sm font-medium">{{ $t['titles']['load_average'] }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <x-filament::card>
                            <div class="text-center space-y-1">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $t['labels']['one_min'] }}</p>
                                <p class="text-lg font-bold">{{ $data['load']['1min'] }}</p>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center space-y-1">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $t['labels']['five_min'] }}</p>
                                <p class="text-lg font-bold">{{ $data['load']['5min'] }}</p>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center space-y-1">
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $t['labels']['fifteen_min'] }}</p>
                                <p class="text-lg font-bold">{{ $data['load']['15min'] }}</p>
                            </div>
                        </x-filament::card>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Memory Section -->
        <x-filament::section>
            <x-slot name="heading">{{ $t['titles']['memory_usage'] }}</x-slot>
            <x-slot name="description">{{ $t['titles']['memory_description'] ?? 'RAM and Virtual Memory statistics' }}</x-slot>

            <div class="space-y-6">
                <!-- Physical Memory -->
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $t['labels']['memory'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $data['memory']['used'] }} / {{ $data['memory']['total'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-2 rounded-full transition-all"
                            style="width: {{ $data['memory']['percent'] }}%; background-color: {{ $data['memory']['percent'] > 80 ? '#dc2626' : ($data['memory']['percent'] > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $data['memory']['percent'] > 80 ? $t['status']['critical'] : ($data['memory']['percent'] > 60 ? $t['status']['warning'] : $t['status']['good']) }}
                    </p>
                </div>

                <!-- Swap Memory -->
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $t['labels']['swap_memory'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $data['swap']['used'] }} / {{ $data['swap']['total'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-2 rounded-full transition-all"
                            style="width: {{ $data['swap']['percent'] }}%; background-color: {{ $data['swap']['percent'] > 50 ? '#f59e0b' : '#059669' }}"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $data['swap']['percent'] > 50 ? 'Swap active' : 'Swap inactive' }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Disk Section -->
        <x-filament::section>
            <x-slot name="heading">{{ $t['titles']['disk_usage'] }}</x-slot>
            <x-slot name="description">{{ $t['titles']['disk_description'] ?? 'Disk space information' }}</x-slot>

            <div class="space-y-6">
                <!-- Disk Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['used'] }}</p>
                            <p class="text-lg font-bold">{{ $data['disk']['used'] }}</p>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['total'] }}</p>
                            <p class="text-lg font-bold">{{ $data['disk']['total'] }}</p>
                        </div>
                    </x-filament::card>

                    <x-filament::card>
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['free'] }}</p>
                            <p class="text-lg font-bold">{{ $data['disk']['free'] }}</p>
                        </div>
                    </x-filament::card>
                </div>

                <!-- Disk Usage Progress -->
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium">{{ $t['titles']['disk_usage'] }}</span>
                        <span class="text-sm font-bold">{{ $data['disk']['percent'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div 
                            class="h-2 rounded-full transition-all"
                            style="width: {{ $data['disk']['percent'] }}%; background-color: {{ $data['disk']['percent'] > 80 ? '#dc2626' : ($data['disk']['percent'] > 60 ? '#f59e0b' : '#059669') }}"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $data['disk']['percent'] > 80 ? $t['status']['critical'] : ($data['disk']['percent'] > 60 ? $t['status']['warning'] : $t['status']['good']) }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- System Info Section -->
        <x-filament::section>
            <x-slot name="heading">{{ $t['titles']['system_info'] }}</x-slot>
            <x-slot name="description">{{ $t['titles']['system_description'] ?? 'System details and information' }}</x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['os'] }}</p>
                        <p class="text-lg font-bold">{{ $data['os'] }}</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['hostname'] ?? 'Hostname' }}</p>
                        <p class="text-lg font-bold">{{ $data['hostname'] }}</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['php_version'] }}</p>
                        <p class="text-lg font-bold">{{ $data['php_version'] }}</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['processes'] }}</p>
                        <p class="text-lg font-bold">{{ $data['processes']['total'] ?? 'N/A' }}</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['titles']['uptime'] }}</p>
                        <p class="text-lg font-bold">{{ $data['uptime'] }}</p>
                    </div>
                </x-filament::card>

                <x-filament::card>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $t['labels']['last_reboot'] }}</p>
                        <p class="text-lg font-bold">{{ $data['last_reboot'] }}</p>
                    </div>
                </x-filament::card>
            </div>
        </x-filament::section>    @endif
</x-filament-panels::page>