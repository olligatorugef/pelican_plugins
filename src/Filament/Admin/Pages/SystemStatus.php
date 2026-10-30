<?php

namespace Xolli\SystemStatusMonitor\Filament\Admin\Pages;

use Filament\Pages\Page;
use Xolli\SystemStatusMonitor\Services\NodeStatsService;
use Xolli\SystemStatusMonitor\Services\SystemInfoService;

class SystemStatus extends Page
{
    protected string $view = 'system-status-monitor::pages.system-status';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'system-status';

    /** @var array<string, mixed> */
    public array $data = [];

    /** @var array<int, array<string, mixed>> */
    public array $nodes = [];

    public string $nodesError = '';

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        try {
            $this->data = SystemInfoService::getSystemInfo();
        } catch (\Throwable $e) {
            $this->data = ['error' => 'Unable to retrieve system information: ' . $e->getMessage()];
        }

        $this->nodesError = '';
        $this->nodes = [];

        if (static::canViewNodes()) {
            try {
                $this->nodes = NodeStatsService::all();
            } catch (\Throwable $e) {
                $this->nodesError = $e->getMessage();
            }
        }
    }

    public static function canViewNodes(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->can('viewList node');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationLabel(): string
    {
        return __('system-status-monitor::messages.navigation_label');
    }

    public function getTitle(): string
    {
        return __('system-status-monitor::messages.titles.system_info');
    }
}
