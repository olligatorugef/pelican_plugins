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
            // Method 1: Try using /proc/stat for accurate average
            if (file_exists('/proc/stat')) {
                return self::getCpuUsageFromProcStat();
            }
            
            // Method 2: Fallback to top command
            $output = shell_exec('top -bn2 -d 0.5 | grep "Cpu(s)" | tail -1');
            if ($output) {
                // Try different formats: "us,sy,ni,id,wa,hi,si,st" or "%us,%sy,%ni,%id,%wa,%hi,%si"
                if (preg_match('/(\d+\.?\d*)\s*%?\s*us/', $output, $matches)) {
                    return (float)$matches[1];
                }
                if (preg_match('/^[\s\S]*?(\d+\.?\d*)\s*%us/', $output, $matches)) {
                    return (float)$matches[1];
                }
            }
            
            // Method 3: Use ps aux as last resort
            $output = shell_exec('ps aux | awk \'BEGIN {sum=0} {sum+=$3} END {print sum}\'');
            if ($output) {
                return min((float)$output, 100);
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }

    /**
     * Calculate CPU usage from /proc/stat
     */
    private static function getCpuUsageFromProcStat(): float
    {
        try {
            $stat1 = file_get_contents('/proc/stat');
            if (!$stat1) return 0;
            
            sleep(1);
            
            $stat2 = file_get_contents('/proc/stat');
            if (!$stat2) return 0;
            
            // Parse first CPU line
            preg_match('/^cpu\s+(.+)$/m', $stat1, $matches1);
            preg_match('/^cpu\s+(.+)$/m', $stat2, $matches2);
            
            if (!isset($matches1[1]) || !isset($matches2[1])) {
                return 0;
            }
            
            $cpu1 = array_map('intval', explode(' ', $matches1[1]));
            $cpu2 = array_map('intval', explode(' ', $matches2[1]));
            
            // Calculate deltas
            $user1 = $cpu1[0] ?? 0;
            $nice1 = $cpu1[1] ?? 0;
            $system1 = $cpu1[2] ?? 0;
            $idle1 = $cpu1[3] ?? 0;
            
            $user2 = $cpu2[0] ?? 0;
            $nice2 = $cpu2[1] ?? 0;
            $system2 = $cpu2[2] ?? 0;
            $idle2 = $cpu2[3] ?? 0;
            
            $userDiff = $user2 - $user1;
            $niceDiff = $nice2 - $nice1;
            $systemDiff = $system2 - $system1;
            $idleDiff = $idle2 - $idle1;
            
            $total = $userDiff + $niceDiff + $systemDiff + $idleDiff;
            
            if ($total === 0) {
                return 0;
            }
            
            $usage = (($userDiff + $niceDiff + $systemDiff) / $total) * 100;
            return round($usage, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get CPU usage for Windows
     */
    private static function getCpuUsageWindows(): float
    {
        try {
            $output = shell_exec('wmic path win32_perfformatteddata_perfos_processor where name="Total" get PercentProcessorTime /value');
            if (preg_match('/PercentProcessorTime=(\d+)/', $output, $matches)) {
                return (float)$matches[1];
            }
            return 0;
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
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic os get numberofprocessors');
                if (preg_match('/(\d+)/', $output, $matches)) {
                    return (int)$matches[1];
                }
            } else {
                $output = shell_exec('nproc');
                if ($output) {
                    return (int)trim($output);
                }
            }
        } catch (\Exception $e) {
            return 1;
        }
        return 1;
    }

    /**
     * Get CPU model
     */
    public static function getCpuModel(): string
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic cpu get name');
                if (preg_match('/Intel|AMD|ARM/', $output, $matches)) {
                    return trim(str_replace('Name', '', $output));
                }
            } else {
                $output = shell_exec("cat /proc/cpuinfo | grep 'model name' | head -1");
                if ($output && preg_match('/:\s*(.+)/', $output, $matches)) {
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
                $total = (int)$matches[1];
                $used = (int)$matches[2];
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
                $total = (int)$matches[1];
                $used = (int)$matches[2];
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
    public static function getDiskUsage($path = '/'): array
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
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                return ['1' => 0, '5' => 0, '15' => 0];
            }

            $loads = sys_getloadavg();
            return [
                '1' => round($loads[0], 2),
                '5' => round($loads[1], 2),
                '15' => round($loads[2], 2),
            ];
        } catch (\Exception $e) {
            return ['1' => 0, '5' => 0, '15' => 0];
        }
    }

    /**
     * Get system uptime
     */
    public static function getUptime(): string
    {
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic os get lastbootuptime');
                if (preg_match('/(\d{14})/', $output, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    $uptime = time() - $timestamp;
                } else {
                    return 'Unknown';
                }
            } else {
                $output = shell_exec('cat /proc/uptime');
                if ($output && preg_match('/(\d+)/', $output, $matches)) {
                    $uptime = (int)$matches[1];
                } else {
                    return 'Unknown';
                }
            }

            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);

            return "{$days}d {$hours}h {$minutes}m";
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
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic os get lastbootuptime');
                if (preg_match('/(\d{14})/', $output, $matches)) {
                    $timestamp = strtotime($matches[1]);
                    return date('Y-m-d H:i:s', $timestamp);
                }
            } else {
                $output = shell_exec('who -b');
                if ($output) {
                    preg_match('/(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2})/', $output, $matches);
                    if (isset($matches[1])) {
                        return $matches[1];
                    }
                }
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
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $total = shell_exec('wmic process list brief | find /c /v ""');
                return ['total' => (int)$total, 'running' => (int)$total];
            } else {
                $output = shell_exec('ps aux | wc -l');
                $total = (int)$output - 2;

                $running = shell_exec("ps aux | grep -c ' S '");

                return ['total' => max(0, $total), 'running' => max(0, (int)$running)];
            }
        } catch (\Exception $e) {
            return ['total' => 0, 'running' => 0];
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
     * Get all system info
     */
    public static function getSystemInfo(): array
    {
        $cpuInfo = self::getLoadAverage();
        $loadAverage = [
            '1min' => $cpuInfo['1'] ?? 0,
            '5min' => $cpuInfo['5'] ?? 0,
            '15min' => $cpuInfo['15'] ?? 0,
        ];
        
        return [
            'cpu' => self::getCpuUsage(),
            'cpu_cores' => self::getCpuCores(),
            'cpu_model' => self::getCpuModel(),
            'memory' => self::getMemoryUsage(),
            'swap' => self::getSwapMemory(),
            'disk' => self::getDiskUsage(),
            'load' => $loadAverage,
            'uptime' => self::getUptime(),
            'last_reboot' => self::getLastReboot(),
            'processes' => self::getProcessCount(),
            'hostname' => self::getHostname(),
            'os' => php_uname('s'),
        ];
    }

    /**
     * Format bytes to human-readable format
     */
    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
