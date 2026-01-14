<?php

namespace Xolli\SystemStatusMonitor\Services;

class SystemInfoService
{
    /**
     * Get CPU usage percentage
     */
    public static function getCpuUsage(): float
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return self::getCpuUsageWindows();
        }

        return self::getCpuUsageLinux();
    }

    /**
     * Get CPU usage on Linux
     */
    private static function getCpuUsageLinux(): float
    {
        try {
            if (!file_exists('/proc/stat')) {
                return 0;
            }

            $stat1 = file('/proc/stat');
            usleep(100000);
            $stat2 = file('/proc/stat');

            $info1 = preg_split('/\s+/', trim($stat1[0]));
            $info2 = preg_split('/\s+/', trim($stat2[0]));

            if (count($info1) < 5 || count($info2) < 5) {
                return 0;
            }

            $dif = array();
            $dif['user'] = (int)$info2[1] - (int)$info1[1];
            $dif['nice'] = (int)$info2[2] - (int)$info1[2];
            $dif['sys'] = (int)$info2[3] - (int)$info1[3];
            $dif['idle'] = (int)$info2[4] - (int)$info1[4];

            $total = array_sum($dif);

            if ($total == 0) {
                return 0;
            }

            $cpu = round(($total - $dif['idle']) / $total * 100, 2);

            return min($cpu, 100);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get CPU usage on Windows
     */
    private static function getCpuUsageWindows(): float
    {
        try {
            $wmi = new \COM('winmgmts:');
            $cpuUsage = $wmi->ExecQuery('SELECT LoadPercentage FROM Win32_Processor');

            $cpu = 0;
            foreach ($cpuUsage as $item) {
                $cpu = $item->LoadPercentage;
            }

            return (float)($cpu ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get CPU cores count
     */
    public static function getCpuCores(): int
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $wmi = new \COM('winmgmts:');
                $cpuInfo = $wmi->ExecQuery('SELECT NumberOfCores FROM Win32_Processor');
                foreach ($cpuInfo as $item) {
                    return (int)$item->NumberOfCores;
                }
            }

            if (file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                preg_match_all('/^processor/m', $cpuinfo, $matches);
                return count($matches[0]);
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return (int)shell_exec('nproc') ?: 1;
    }

    /**
     * Get CPU model name
     */
    public static function getCpuModel(): string
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $wmi = new \COM('winmgmts:');
                $cpuInfo = $wmi->ExecQuery('SELECT Name FROM Win32_Processor');
                foreach ($cpuInfo as $item) {
                    return trim($item->Name) ?: 'Unknown';
                }
            }

            if (file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                if (preg_match('/model name\s*:\s*(.+)/i', $cpuinfo, $matches)) {
                    return trim($matches[1]);
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        return 'Unknown';
    }

    /**
     * Get memory usage
     */
    public static function getMemoryUsage(): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return self::getMemoryUsageWindows();
        }

        return self::getMemoryUsageLinux();
    }

    /**
     * Get memory usage on Linux
     */
    private static function getMemoryUsageLinux(): array
    {
        try {
            $free = shell_exec('free -b');
            if (!$free) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $free = (string)trim($free);
            $free_arr = explode("\n", $free);
            
            if (!isset($free_arr[1])) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $mem = preg_split('/\s+/', trim($free_arr[1]));
            
            if (count($mem) < 3) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            // mem[0] = "Mem:", mem[1] = total, mem[2] = used, mem[3] = free
            $memory_total = (int)$mem[1];
            $memory_used = (int)$mem[2];
            $memory_usage_percent = $memory_total > 0 ? round($memory_used / $memory_total * 100, 2) : 0;

            return [
                'used' => self::formatBytes($memory_used),
                'used_raw' => $memory_used,
                'total' => self::formatBytes($memory_total),
                'total_raw' => $memory_total,
                'percent' => min($memory_usage_percent, 100),
            ];
        } catch (\Exception $e) {
            return [
                'used' => 'N/A',
                'used_raw' => 0,
                'total' => 'N/A',
                'total_raw' => 0,
                'percent' => 0,
            ];
        }
    }

    /**
     * Get memory usage on Windows
     */
    private static function getMemoryUsageWindows(): array
    {
        try {
            $wmi = new \COM('winmgmts:');
            $memory = $wmi->ExecQuery('SELECT TotalVisibleMemorySize, FreePhysicalMemory FROM Win32_OperatingSystem');

            $mem_used = 0;
            $mem_total = 0;

            foreach ($memory as $item) {
                $mem_used = ((int)$item->TotalVisibleMemorySize - (int)$item->FreePhysicalMemory) * 1024;
                $mem_total = (int)$item->TotalVisibleMemorySize * 1024;
            }

            $percent = $mem_total > 0 ? round(($mem_used / $mem_total) * 100, 2) : 0;

            return [
                'used' => self::formatBytes($mem_used),
                'used_raw' => $mem_used,
                'total' => self::formatBytes($mem_total),
                'total_raw' => $mem_total,
                'percent' => min($percent, 100),
            ];
        } catch (\Exception $e) {
            return [
                'used' => 'N/A',
                'used_raw' => 0,
                'total' => 'N/A',
                'total_raw' => 0,
                'percent' => 0,
            ];
        }
    }

    /**
     * Get swap memory usage
     */
    public static function getSwapMemory(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $free = shell_exec('free -b');
            if (!$free) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $free_arr = explode("\n", $free);
            
            if (!isset($free_arr[2])) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $swap = preg_split('/\s+/', trim($free_arr[2]));
            
            if (count($swap) < 3) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'percent' => 0,
                ];
            }

            $swap_total = (int)$swap[1];
            $swap_used = (int)$swap[2];
            $swap_percent = $swap_total > 0 ? round($swap_used / $swap_total * 100, 2) : 0;

            return [
                'used' => self::formatBytes($swap_used),
                'total' => self::formatBytes($swap_total),
                'percent' => min($swap_percent, 100),
            ];
        } catch (\Exception $e) {
            return [
                'used' => 'N/A',
                'total' => 'N/A',
                'percent' => 0,
            ];
        }
    }

    /**
     * Get disk usage
     */
    public static function getDiskUsage(string $path = '/'): array
    {
        try {
            $disk_free = disk_free_space($path);
            $disk_total = disk_total_space($path);

            if ($disk_total === false || $disk_free === false) {
                return [
                    'used' => 'N/A',
                    'total' => 'N/A',
                    'free' => 'N/A',
                    'percent' => 0,
                ];
            }

            $disk_used = $disk_total - $disk_free;
            $disk_percent = round(($disk_used / $disk_total) * 100, 2);

            return [
                'used' => self::formatBytes($disk_used),
                'total' => self::formatBytes($disk_total),
                'free' => self::formatBytes($disk_free),
                'percent' => min($disk_percent, 100),
            ];
        } catch (\Exception $e) {
            return [
                'used' => 'N/A',
                'total' => 'N/A',
                'free' => 'N/A',
                'percent' => 0,
            ];
        }
    }

    /**
     * Get load average
     */
    public static function getLoadAverage(): array
    {
        try {
            $load = sys_getloadavg();

            if ($load === false) {
                return [
                    '1min' => 0,
                    '5min' => 0,
                    '15min' => 0,
                ];
            }

            return [
                '1min' => round($load[0], 2),
                '5min' => round($load[1], 2),
                '15min' => round($load[2], 2),
            ];
        } catch (\Exception $e) {
            return [
                '1min' => 0,
                '5min' => 0,
                '15min' => 0,
            ];
        }
    }

    /**
     * Get uptime
     */
    public static function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return self::getUptimeWindows();
        }

        return self::getUptimeLinux();
    }

    /**
     * Get uptime on Linux
     */
    private static function getUptimeLinux(): string
    {
        try {
            $uptime = shell_exec('uptime -p');

            return trim($uptime ?? 'Unknown');
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get uptime on Windows
     */
    private static function getUptimeWindows(): string
    {
        try {
            $wmi = new \COM('winmgmts:');
            $os = $wmi->ExecQuery('SELECT LastBootUpTime FROM Win32_OperatingSystem');

            foreach ($os as $item) {
                $lastBoot = $item->LastBootUpTime;
                $bootTime = strtotime(substr($lastBoot, 0, 14));
                $uptime = time() - $bootTime;
                $days = floor($uptime / 86400);
                $hours = floor(($uptime % 86400) / 3600);
                $minutes = floor(($uptime % 3600) / 60);

                return "{$days}d {$hours}h {$minutes}m";
            }
        } catch (\Exception $e) {
            return 'Unknown';
        }

        return 'Unknown';
    }

    /**
     * Get process count
     */
    public static function getProcessCount(): array
    {
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $wmi = new \COM('winmgmts:');
                $processes = $wmi->ExecQuery('SELECT * FROM Win32_Process');
                return [
                    'total' => $processes->Count(),
                    'running' => $processes->Count(),
                ];
            }

            // Linux
            $ps = shell_exec('ps aux | wc -l');
            $total = (int)$ps - 1; // Subtract header

            return [
                'total' => $total,
                'running' => $total,
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'running' => 0,
            ];
        }
    }

    /**
     * Get system hostname
     */
    public static function getHostname(): string
    {
        try {
            return gethostname() ?: 'Unknown';
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
            if (PHP_OS_FAMILY === 'Windows') {
                $wmi = new \COM('winmgmts:');
                $os = $wmi->ExecQuery('SELECT LastBootUpTime FROM Win32_OperatingSystem');

                foreach ($os as $item) {
                    $lastBoot = $item->LastBootUpTime;
                    $timestamp = strtotime(substr($lastBoot, 0, 14));
                    return date('Y-m-d H:i:s', $timestamp);
                }
            }

            // Linux
            $uptime = shell_exec('uptime -s');
            return trim($uptime ?? 'Unknown');
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Format bytes to human-readable format
     */
    public static function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = abs($bytes);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get system info
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
            'os' => PHP_OS_FAMILY,
            'php_version' => phpversion(),
        ];
    }
}