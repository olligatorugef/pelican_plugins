<?php

namespace Xolli\SystemStatusMonitor\Services;

use App\Models\Node;

class NodeStatsService
{
    /**
     * Statistics of every node, as returned by Wings (cached by Pelican for a few seconds).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $nodes = [];

        foreach (Node::query()->orderBy('name')->get() as $node) {
            $nodes[] = self::forNode($node);
        }

        return $nodes;
    }

    /**
     * @return array<string, mixed>
     */
    public static function forNode(Node $node): array
    {
        try {
            $stats = $node->statistics();
        } catch (\Throwable) {
            $stats = [];
        }

        $memTotal = (int) ($stats['memory_total'] ?? 0);
        $memUsed = (int) ($stats['memory_used'] ?? 0);
        $swapTotal = (int) ($stats['swap_total'] ?? 0);
        $swapUsed = (int) ($stats['swap_used'] ?? 0);
        $diskTotal = (int) ($stats['disk_total'] ?? 0);
        $diskUsed = (int) ($stats['disk_used'] ?? 0);

        // Pelican returns zeros when Wings is unreachable
        $online = $memTotal > 0;

        $info = [];
        if ($online) {
            try {
                $info = $node->systemInformation();
            } catch (\Throwable) {
                $info = [];
            }
        }

        return [
            'id' => $node->id,
            'name' => $node->name,
            'fqdn' => $node->fqdn,
            'online' => $online,
            'cpu' => round(max(0, min(100, (float) ($stats['cpu_percent'] ?? 0))), 2),
            'cpu_cores' => (int) ($info['cpu_count'] ?? 0),
            'memory' => self::block($memUsed, $memTotal),
            'swap' => self::block($swapUsed, $swapTotal),
            'disk' => self::block($diskUsed, $diskTotal),
            'load' => [
                '1min' => round((float) ($stats['load_average1'] ?? 0), 2),
                '5min' => round((float) ($stats['load_average5'] ?? 0), 2),
                '15min' => round((float) ($stats['load_average15'] ?? 0), 2),
            ],
            'version' => $info['version'] ?? null,
            'os' => $info['os'] ?? ($info['os_type'] ?? null),
            'kernel' => $info['kernel_version'] ?? null,
            'error' => $info['exception'] ?? null,
        ];
    }

    /**
     * @return array{used: string, total: string, free: string, percent: float}
     */
    private static function block(int $used, int $total): array
    {
        $used = max(0, $used);

        return [
            'used' => SystemInfoService::formatBytes($used),
            'total' => SystemInfoService::formatBytes($total),
            'free' => SystemInfoService::formatBytes(max(0, $total - $used)),
            'percent' => $total > 0 ? round(max(0, min(100, ($used / $total) * 100)), 2) : 0.0,
        ];
    }
}
