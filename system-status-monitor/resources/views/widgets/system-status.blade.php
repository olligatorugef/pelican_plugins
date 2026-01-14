<div>
    @php
        $translations = __('system-status-monitor::messages') ?? [];
        
        $t = [
            'titles' => $translations['titles'] ?? [],
            'status' => $translations['status'] ?? []
        ];
        
        $cpu = $cpu ?? 0;
        $memory = $memory ?? ['percent' => 0];
        $disk = $disk ?? ['percent' => 0];
    @endphp

    <h3 class="text-lg font-bold mb-3">⚡ System Status</h3>

    @if(isset($error))
        <div class="text-red-600 text-sm font-semibold">{{ $error }}</div>
    @else
        <div class="space-y-2">
            <!-- CPU -->
            <div class="flex justify-between items-center text-sm">
                <span class="font-semibold">💻 CPU:</span>
                <span class="font-bold">{{ $cpu }}%</span>
            </div>

            <!-- Mémoire -->
            <div class="flex justify-between items-center text-sm">
                <span class="font-semibold">🧠 Memory:</span>
                <span class="font-bold">{{ $memory['percent'] }}%</span>
            </div>

            <!-- Disque -->
            <div class="flex justify-between items-center text-sm">
                <span class="font-semibold">💾 Disk:</span>
                <span class="font-bold">{{ $disk['percent'] }}%</span>
            </div>
        </div>
    @endif
</div>
