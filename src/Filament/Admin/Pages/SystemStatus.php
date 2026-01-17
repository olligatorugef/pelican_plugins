<?php

namespace Xolli\SystemStatusMonitor\Filament\Admin\Pages;

use Filament\Pages\Page;
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

class SystemStatus extends Page
{
    protected string $view = 'system-status-monitor::pages.system-status';

    protected static bool $shouldRegisterNavigation = true;

    public array $data = [];

    public function mount(): void
    {
        try {
            $this->data = SystemInfoService::getSystemInfo();
        } catch (\Exception $e) {
            $this->data = [
                'error' => 'Unable to retrieve system information: ' . $e->getMessage(),
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
            ];
        }
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationLabel(): string
    {
        return __("system-status-monitor::messages.navigation_label");
    }

    public function getTitle(): string
    {
        return __("system-status-monitor::messages.page_title");
    }
}
