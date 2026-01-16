<?php

namespace Xolli\SystemStatusMonitor\Filament\Admin\Widgets;

use Filament\Widgets\Widget;
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

class SystemStatusWidget extends Widget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected string $view = 'system-status-monitor::widgets.system-status';

    protected function getViewData(): array
    {
        try {
            $systemInfo = SystemInfoService::getSystemInfo();

            return [
                'cpu' => $systemInfo['cpu'],
                'memory' => $systemInfo['memory'],
                'disk' => $systemInfo['disk'],
                'load' => $systemInfo['load'],
                'uptime' => $systemInfo['uptime'],
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Unable to retrieve system information',
            ];
        }
    }
}
