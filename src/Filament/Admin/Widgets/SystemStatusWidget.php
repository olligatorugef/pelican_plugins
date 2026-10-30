<?php

namespace Xolli\SystemStatusMonitor\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Xolli\SystemStatusMonitor\Filament\Admin\Pages\SystemStatus;
use Xolli\SystemStatusMonitor\Services\NodeStatsService;
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

class SystemStatusWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected string $view = 'system-status-monitor::widgets.system-status';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $data = ['panel' => null, 'nodes' => [], 'error' => null];

        try {
            $info = SystemInfoService::getSystemInfo();
            $data['panel'] = [
                'cpu' => $info['cpu'],
                'memory' => $info['memory'],
                'disk' => $info['disk'],
                'load' => $info['load'],
                'uptime' => $info['uptime'],
            ];
        } catch (\Throwable) {
            $data['error'] = 'Unable to retrieve system information';
        }

        if (SystemStatus::canViewNodes()) {
            try {
                $data['nodes'] = NodeStatsService::all();
            } catch (\Throwable) {
                $data['nodes'] = [];
            }
        }

        return $data;
    }
}
