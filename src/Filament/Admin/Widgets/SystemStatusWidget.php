<?php

namespace Xolli\SystemStatusMonitor\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

class SystemStatusWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'system-status-monitor::widgets.system-status';

    public array $data = [];

    public function mount(): void
    {
        try {
            $info = SystemInfoService::getSystemInfo();
            $this->data = [
                'cpu' => $info['cpu']['usage'] ?? 0,
                'memory' => $info['memory'] ?? ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
                'disk' => $info['disk'] ?? ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
            ];
        } catch (\Exception $e) {
            $this->data = [
                'cpu' => 0,
                'memory' => ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
                'disk' => ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0],
            ];
        }
    }
}
