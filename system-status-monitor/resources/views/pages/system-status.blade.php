<x-filament-panels::page>
    @php
        // La variable $data est passée via getViewData() de la Page
        // Assurer que $data existe et a les bonnes valeurs
        if (!isset($data) || !is_array($data)) {
            $data = [];
        }
        
        $translations = __('system-status-monitor::messages') ?? [];
        
        // Créer un array de traductions sécurisé
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
                'memory_usage' => 'Memory Usage',
                'disk_usage' => 'Disk Usage',
                'load_average' => 'Load Average'
            ],
            'labels' => $translations['labels'] ?? [
                'os' => 'OS',
                'php_version' => 'PHP Version',
                'cpu_model' => 'CPU Model',
                'memory' => 'Memory'
            ]
        ];
        
        // Assurer que toutes les clés critiques existent
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

    <div class="space-y-10 max-w-6xl">
        @if(isset($data['error']))
            <div class="text-red-600 font-semibold">
                <p>⚠️ {{ $t['status']['unavailable'] ?? 'Unavailable' }}</p>
                <p>{{ $data['error'] }}</p>
            </div>
        @else
        <!-- En-tête principal -->
        <div class="p-8 text-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2 text-white">{{ config('app.name') }}</h1>
                    <p class="text-gray-300 text-lg">{{ $data['hostname'] ?? 'System' }} • {{ $data['os'] }}</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-white">🟢 {{ $t['status']['online'] ?? 'Online' }}</p>
                    <p class="text-gray-300 text-sm mt-1">{{ date('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>

        <!-- Section CPU -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-2xl">💻 {{ $t['titles']['cpu_details'] }}</span>
            </x-slot>

            <div class="space-y-8">
                <!-- Infos processeur -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-5">
                        <p class="text-sm font-semibold text-gray-300">{{ $t['labels']['cpu_model'] }}</p>
                        <p class="text-lg font-bold mt-3 text-white">{{ $data['cpu_model'] ?? 'Unknown' }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-sm font-semibold text-gray-300">{{ $t['labels']['cores'] }}</p>
                        <p class="text-lg font-bold mt-3 text-white">{{ $data['cpu_cores'] ?? 'N/A' }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-sm font-semibold text-gray-300">{{ $t['titles']['cpu_usage'] }}</p>
                        <p class="text-lg font-bold mt-3" style="color: {{ $data['cpu'] > 80 ? '#dc2626' : ($data['cpu'] > 60 ? '#f59e0b' : '#059669') }}">
                            {{ $data['cpu'] }}%
                        </p>
                    </div>
                </div>

                <!-- Barre CPU -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-gray-300">{{ $t['titles']['cpu_usage'] }}</span>
                        <span class="text-3xl font-bold" style="color: {{ $data['cpu'] > 80 ? '#dc2626' : ($data['cpu'] > 60 ? '#f59e0b' : '#059669') }}">
                            {{ $data['cpu'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-6">
                        <div class="h-6 rounded-full transition-all flex items-center justify-center" style="background-color: {{ $data['cpu'] > 80 ? '#dc2626' : ($data['cpu'] > 60 ? '#f59e0b' : '#059669') }}; width: {{ $data['cpu'] }}%">
                            @if($data['cpu'] > 20)
                                <span class="text-xs font-bold text-white">{{ $data['cpu'] }}%</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3 font-medium">
                        {{ $data['cpu'] > 80 ? '🚨 ' . $t['status']['critical'] : ($data['cpu'] > 60 ? '⚠️ ' . $t['status']['warning'] : '✅ ' . $t['status']['excellent']) }}
                    </p>
                </div>

                <!-- Charge système -->
                <div class="p-6">
                    <h3 class="font-bold text-gray-300 mb-6">{{ $t['titles']['load_average'] }}</h3>
                    <div class="grid grid-cols-3 gap-6">
                        <div class="p-5 text-center">
                            <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['one_min'] }}</p>
                            <p class="text-2xl font-bold text-white">{{ $data['load']['1min'] }}</p>
                        </div>
                        <div class="p-5 text-center">
                            <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['five_min'] }}</p>
                            <p class="text-2xl font-bold text-white">{{ $data['load']['5min'] }}</p>
                        </div>
                        <div class="p-5 text-center">
                            <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['fifteen_min'] }}</p>
                            <p class="text-2xl font-bold text-white">{{ $data['load']['15min'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <!-- Section Mémoire -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-2xl">🧠 {{ $t['titles']['memory_usage'] }}</span>
            </x-slot>

            <div class="space-y-8">
                <!-- RAM physique -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-gray-300">{{ $t['labels']['memory'] }}</span>
                        <span class="text-xs px-3 py-1 rounded-full font-semibold text-gray-300">
                            {{ $data['memory']['used'] }} / {{ $data['memory']['total'] }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-6 mb-3">
                        <div class="bg-green-600 h-6 rounded-full transition-all flex items-center justify-center" style="width: {{ $data['memory']['percent'] }}%">
                            @if($data['memory']['percent'] > 20)
                                <span class="text-xs font-bold text-white">{{ $data['memory']['percent'] }}%</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 font-medium">
                        {{ $data['memory']['percent'] > 80 ? '🚨 ' . $t['status']['critical'] : ($data['memory']['percent'] > 60 ? '⚠️ ' . $t['status']['warning'] : '✅ ' . $t['status']['good']) }}
                    </p>
                </div>

                <!-- Swap -->
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-gray-300">{{ $t['labels']['swap_memory'] }}</span>
                        <span class="text-xs px-3 py-1 rounded-full font-semibold text-gray-300">
                            {{ $data['swap']['used'] }} / {{ $data['swap']['total'] }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-6 mb-3">
                        <div class="bg-orange-500 h-6 rounded-full transition-all flex items-center justify-center" style="width: {{ $data['swap']['percent'] }}%">
                            @if($data['swap']['percent'] > 20)
                                <span class="text-xs font-bold text-white">{{ $data['swap']['percent'] }}%</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 font-medium">
                        {{ $data['swap']['percent'] > 50 ? '⚠️ Swap actif' : '✅ Swap inactif' }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <!-- Section Disque -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-2xl">💾 {{ $t['titles']['disk_usage'] }}</span>
            </x-slot>

            <div class="p-6">
                <!-- Infos -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="p-5">
                        <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['used'] }}</p>
                        <p class="text-xl font-bold text-white">{{ $data['disk']['used'] }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['total'] }}</p>
                        <p class="text-xl font-bold text-white">{{ $data['disk']['total'] }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-xs text-gray-300 font-semibold mb-2">{{ $t['labels']['free'] }}</p>
                        <p class="text-xl font-bold text-white">{{ $data['disk']['free'] }}</p>
                    </div>
                </div>

                <!-- Barre -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-white">{{ $t['titles']['disk_usage'] }}</span>
                        <span class="text-3xl font-bold" style="color: {{ $data['disk']['percent'] > 80 ? '#dc2626' : ($data['disk']['percent'] > 60 ? '#f59e0b' : '#059669') }}">
                            {{ $data['disk']['percent'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-300 rounded-full h-6">
                        <div class="h-6 rounded-full transition-all flex items-center justify-center" style="background-color: {{ $data['disk']['percent'] > 80 ? '#dc2626' : ($data['disk']['percent'] > 60 ? '#f59e0b' : '#059669') }}; width: {{ $data['disk']['percent'] }}%">
                            @if($data['disk']['percent'] > 20)
                                <span class="text-xs font-bold text-white">{{ $data['disk']['percent'] }}%</span>
                            @endif
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-400 font-medium">
                    {{ $data['disk']['percent'] > 80 ? '🚨 ' . $t['status']['critical'] : ($data['disk']['percent'] > 60 ? '⚠️ ' . $t['status']['warning'] : '✅ ' . $t['status']['good']) }}
                </p>
            </div>
        </x-filament::section>

        <!-- Section Système -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-2xl">🖥️ {{ $t['titles']['system_info'] }}</span>
            </x-slot>

            <div class="space-y-6 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Système d'exploitation -->
                    <div class="p-5 flex justify-between items-center">
                        <span class="text-gray-300 font-semibold">{{ $t['labels']['os'] }}</span>
                        <span class="px-4 py-2 rounded font-bold text-white">{{ $data['os'] }}</span>
                    </div>
                    <!-- PHP Version -->
                    <div class="p-5 flex justify-between items-center">
                        <span class="text-gray-300 font-semibold">{{ $t['labels']['php_version'] }}</span>
                        <span class="px-4 py-2 rounded font-bold text-white">{{ $data['php_version'] }}</span>
                    </div>

                    <!-- Processus -->
                    <div class="p-5 flex justify-between items-center">
                        <span class="text-gray-300 font-semibold">{{ $t['labels']['processes'] }}</span>
                        <span class="px-4 py-2 rounded font-bold text-white">{{ $data['processes']['total'] ?? 'N/A' }}</span>
                    </div>
                    <!-- Uptime -->
                    <div class="p-5 flex justify-between items-center">
                        <span class="text-gray-300 font-semibold">{{ $t['titles']['uptime'] }}</span>
                        <span class="px-3 py-2 rounded font-bold text-white text-right max-w-xs">{{ $data['uptime'] }}</span>
                    </div>
                </div>

                <!-- Dernier redémarrage -->
                <div class="p-6">
                    <p class="text-sm font-semibold mb-2 text-gray-300">{{ $t['labels']['last_reboot'] }}</p>
                    <p class="text-lg font-bold text-white">{{ $data['last_reboot'] }}</p>
                </div>
            </div>
        </x-filament::section>

        <!-- Pied de page -->
        <div class="text-center text-sm text-gray-400 p-4 rounded-lg font-medium">
            <p>💡 Données en temps réel • {{ date('H:i:s') }}</p>
        </div>
        @endif
    </div>
</x-filament-panels::page>
