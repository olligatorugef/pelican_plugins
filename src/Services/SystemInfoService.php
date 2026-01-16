<?php

namespace Xolli\SystemStatusMonitor\Services;

class SystemInfoService
{
    /**
     * Get CPU usage percentage
     */
    public static function getCpuUsage(): float
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return self::getCpuUsageWindows();
        }
        return self::getCpuUsageLinux();
    }

    /**
     * Get CPU usage for Linux
     */
    private static function getCpuUsageLinux(): float
    {
        try {
            $output = shell_exec('top -b -n 1 | grep "Cpu(s)"');
            if ($output && preg_match('/(\d+\.\d+)%\s*us/', $output, $matches)) {
                return (float)$matches[1];
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }

    /**
     * Get CPU usage for Windows
     */
    private static function getCpuUsageWindows(): float
    {
        try {
            $output = shell_exec('wmic os get loadpercentage');
            if ($output && preg_match('/(\d+)/', $output, $matches)) {
                return (float)$matches[1];
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }

    /**
     * Get number of CPU cores
     */
    public static function getCpuCores(): int
    {
        $cores = shell_exec('nproc 2>/dev/null || grep -c ^processor /proc/cpuinfo 2>/dev/null || sysctl -n hw.ncpu 2>/dev/null');
        return (int)trim($cores) ?: 1;
    }

    /**
     * Get CPU model name
     */
    public static function getCpuModel(): string
    {
        try {
            if (file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                if (preg_match('/model name\s*:\s*(.+)/i', $cpuinfo, $matches)) {
                    return trim($matches[1]);
                }
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }
        return 'Unknown';
    }

    /**
     * Get memory usage
     */
    public static function getMemoryUsage(): array
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return self::getMemoryUsageWindows();
        }
        return self::getMemoryUsageLinux();
    }

    /**
     * Get memory usage for Linux
     */
    private static function getMemoryUsageLinux(): array
    {
        try {
            $output = shell_exec('free -b | grep Mem');
            if ($output && preg_match('/(\d+)\s+(\d+)/', $output, $matches)) {
                $total = (int)$matches[1] + (int)$matches[2];
                $used = (int)$matches[1];
                $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                return [
                    'used' => self::formatBytes($used),
                    'total' => self::formatBytes($total),
                    'percent' => $percent,
                ];
            }
        } catch (\Exception $e) {
            return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
        }
        return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
    }

    /**
     * Get memory usage for Windows
     */
    private static function getMemoryUsageWindows(): array
    {
        try {
            $output = shell_exec('wmic os get totalvisiblememorylength,freephysicalmemory');
            if (preg_match('/(\d+)\s+(\d+)/', $output, $matches)) {
                $total = (int)$matches[1] * 1024;
                $free = (int)$matches[2] * 1024;
                $used = $total - $free;
                $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                return [
                    'used' => self::formatBytes($used),
                    'total' => self::formatBytes($total),
                    'percent' => $percent,
                ];
            }
        } catch (\Exception $e) {
            return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
        }
        return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
    }

    /**
     * Get swap memory usage
     */
    public static function getSwapMemory(): array
    {
        try {
            $output = shell_exec('free -b | grep Swap');
            if ($output && preg_match('/(\d+)\s+(\d+)/', $output, $matches)) {
                $total = (int)$matches[1] + (int)$matches[2];
                $used = (int)$matches[1];
                $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;
                return [
                    'used' => self::formatBytes($used),
                    'total' => self::formatBytes($total),
                    'percent' => $percent,
                ];
            }
        } catch (\Exception $e) {
            return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
        }
        return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
    }

    /**
     * Get disk usage
     */
    public static function getDiskUsage(string $path = '/'): array
    {
        try {
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percent = $total > 0 ? round(($used / $total) * 100, 2) : 0;

            return [
                'used' => self::formatBytes($used),
                'total' => self::formatBytes($total),
                'free' => self::formatBytes($free),
                'percent' => $percent,
            ];
        } catch (\Exception $e) {
            return ['used' => 'N/A', 'total' => 'N/A', 'free' => 'N/A', 'percent' => 0];
        }
    }

    /**
     * Get load average
     */
    public static function getLoadAverage(): array
    {
        $load = sys_getloadavg();
        return [
            '1min' => round($load[0], 2),
            '5min' => round($load[1], 2),
            '15min' => round($load[2], 2),
        ];
    }

    /**
     * Get system uptime
     */
    public static function getUptime(): string
    {
        try {
            $uptime = (int)shell_exec('cat /proc/uptime | awk \'{print $1}\'');
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);

            $result = [];
            if ($days > 0) $result[] = "$days d";
            if ($hours > 0) $result[] = "$hours h";
            if ($minutes > 0) $result[] = "$minutes m";

            return implode(' ', $result) ?: '0 m';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get last reboot time
     */
    public static function getLastReboot(): string
    {
        try {
            $uptime = (int)shell_exec('cat /proc/uptime | awk \'{print $1}\'');
            $lastReboot = time() - $uptime;
            return date('d/m/Y H:i:s', $lastReboot);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get process count
     */
    public static function getProcessCount(): array
    {
        try {
            $total = (int)shell_exec('ps aux | wc -l');
            return [
                'total' => $total - 1,
                'running' => $total - 1,
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'running' => 0];
        }
    }

    /**
     * Get hostname
     */
    public static function getHostname(): string
    {
        return gethostname() ?: 'Unknown';
    }

    /**
     * Get system information
     */
    public static function getSystemInfo(): array
    {
        return [
            'cpu' => self::getCpuUsage(),
            'cpu_cores' => self::getCpuCores(),
            'cpu_model' => self::getCpuModel(),
            'memory' => self::getMemoryUsage(),
            'swap' => self::getSwapMemory(),
            'disk' => self::getDiskUsage(),
            'load' => self::getLoadAverage(),
            'uptime' => self::getUptime(),
            'last_reboot' => self::getLastReboot(),
            'processes' => self::getProcessCount(),
            'hostname' => self::getHostname(),
            'os' => php_uname('s'),
            'php_version' => phpversion(),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
