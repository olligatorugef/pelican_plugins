<?php

namespace Xolli\SystemStatusMonitor;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Xolli\SystemStatusMonitor\Filament\admin\Pages\SystemStatus;
use Xolli\SystemStatusMonitor\Filament\admin\Widgets\SystemStatusWidget;

class SystemStatusMonitorPlugin implements Plugin
{
    public function getId(): string
    {
        return 'system-status-monitor';
    }

    public function register(Panel $panel): void
    {
        // Register the page and widget with the admin panel
        if ($panel->getId() === 'admin') {
            $panel->pages([
                SystemStatus::class,
            ]);

            $panel->widgets([
                SystemStatusWidget::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        // Is run only when the panel that the plugin is being registered to is actually in-use. It is executed by a middleware class.
    }
}
