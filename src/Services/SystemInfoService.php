<?php

namespace Xolli\SystemStatusMonitor\Services;

class SystemInfoService
{
    private static function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    private static function readFile(string $path): ?string
    {
        $content = @file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * Run a shell command safely (shell_exec can be disabled / return null).
     */
    private static function run(string $command): ?string
    {
        if (!function_exists('shell_exec')) {
            return null;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('shell_exec', $disabled, true)) {
            return null;
        }

        $output = @shell_exec($command);

        return is_string($output) && $output !== '' ? $output : null;
    }

    private static function percent(int|float $used, int|float $total): float
    {
        if ($total <= 0) {
            return 0.0;
        }

        return round(max(0, min(100, ($used / $total) * 100)), 2);
    }

    private static function memoryArray(int $used, int $total): array
    {
        $used = max(0, $used);
        $total = max(0, $total);

        return [
            'used' => self::formatBytes($used),
            'total' => self::formatBytes($total),
            'free' => self::formatBytes(max(0, $total - $used)),
            'used_bytes' => $used,
            'total_bytes' => $total,
            'percent' => self::percent($used, $total),
        ];
    }

    private static function emptyMemory(): array
    {
        return ['used' => 'N/A', 'total' => 'N/A', 'free' => 'N/A', 'used_bytes' => 0, 'total_bytes' => 0, 'percent' => 0.0];
    }

    // ------------------------------------------------------------------
    // CPU
    // ------------------------------------------------------------------

    public static function getCpuUsage(): float
    {
        if (self::isWindows()) {
            return self::getCpuUsageWindows();
        }

        try {
            return self::getCpuUsageFromProcStat();
        } catch (\Throwable) {
            // Fallback: load average relative to core count
            $load = function_exists('sys_getloadavg') ? @sys_getloadavg() : false;
            if (is_array($load)) {
                return round(min(100, ($load[0] / max(1, self::getCpuCores())) * 100), 2);
            }
        }

        return 0.0;
    }

    private static function getCpuUsageFromProcStat(): float
    {
        $stat1 = self::readFile('/proc/stat');
        if ($stat1 === null) {
            throw new \RuntimeException('/proc/stat not available');
        }

        usleep(250000);
        $stat2 = self::readFile('/proc/stat');

        $cpu1 = self::parseProcStat($stat1);
        $cpu2 = self::parseProcStat($stat2 ?? '');

        if ($cpu1 === null || $cpu2 === null) {
            throw new \RuntimeException('Failed to parse /proc/stat');
        }

        $totalDelta = $cpu2['total'] - $cpu1['total'];
        $idleDelta = $cpu2['idle'] - $cpu1['idle'];

        if ($totalDelta <= 0) {
            return 0.0;
        }

        return round(max(0, min(100, (($totalDelta - $idleDelta) / $totalDelta) * 100)), 2);
    }

    /**
     * @return array{total: int, idle: int}|null
     */
    private static function parseProcStat(string $content): ?array
    {
        foreach (explode("\n", $content) as $line) {
            if (str_starts_with($line, 'cpu ')) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) < 5) {
                    return null;
                }

                // user nice system idle iowait irq softirq steal (guest is already in user)
                $values = array_map('intval', array_slice($parts, 1, 8));
                $values = array_pad($values, 8, 0);

                return [
                    'total' => array_sum($values),
                    'idle' => $values[3] + $values[4],
                ];
            }
        }

        return null;
    }

    private static function getCpuUsageWindows(): float
    {
        $output = self::run('wmic path win32_perfformatteddata_perfos_processor where name="_Total" get PercentProcessorTime /value');
        if ($output && preg_match('/PercentProcessorTime=(\d+)/', $output, $matches)) {
            return (float) $matches[1];
        }

        return 0.0;
    }

    public static function getCpuCores(): int
    {
        if (self::isWindows()) {
            $output = self::run('wmic cpu get NumberOfLogicalProcessors /value');
            if ($output && preg_match('/=(\d+)/', $output, $matches)) {
                return max(1, (int) $matches[1]);
            }

            return 1;
        }

        $cpuinfo = self::readFile('/proc/cpuinfo');
        if ($cpuinfo !== null) {
            $count = preg_match_all('/^processor\s*:/m', $cpuinfo);
            if ($count > 0) {
                return $count;
            }
        }

        $output = self::run('nproc');

        return $output ? max(1, (int) trim($output)) : 1;
    }

    public static function getCpuModel(): string
    {
        if (self::isWindows()) {
            $output = self::run('wmic cpu get name /value');
            if ($output && preg_match('/Name=(.+)/', $output, $matches)) {
                return trim($matches[1]);
            }

            return 'Unknown';
        }

        $cpuinfo = self::readFile('/proc/cpuinfo');
        if ($cpuinfo !== null) {
            foreach (['model name', 'Model', 'Hardware', 'cpu model'] as $key) {
                if (preg_match('/^' . preg_quote($key, '/') . '\s*:\s*(.+)$/mi', $cpuinfo, $matches)) {
                    return trim($matches[1]);
                }
            }
        }

        return 'Unknown';
    }

    // ------------------------------------------------------------------
    // Memory
    // ------------------------------------------------------------------

    /**
     * Parse /proc/meminfo (values in kB) into bytes.
     *
     * @return array<string, int>
     */
    private static function readMeminfo(): array
    {
        $content = self::readFile('/proc/meminfo');
        $info = [];

        if ($content === null) {
            return $info;
        }

        foreach (explode("\n", $content) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s*kB/i', $line, $m)) {
                $info[$m[1]] = (int) $m[2] * 1024;
            }
        }

        return $info;
    }

    /**
     * Memory limit/usage of the current cgroup (Docker). Returns null if the container is not limited.
     *
     * @return array{used: int, total: int}|null
     */
    private static function readCgroupMemory(int $hostTotal): ?array
    {
        // cgroup v2
        $max = self::readFile('/sys/fs/cgroup/memory.max');
        if ($max !== null) {
            $max = trim($max);
            $current = self::readFile('/sys/fs/cgroup/memory.current');
            if (ctype_digit($max) && $current !== null && ctype_digit(trim($current))) {
                $inactive = 0;
                $stat = self::readFile('/sys/fs/cgroup/memory.stat');
                if ($stat !== null && preg_match('/^inactive_file\s+(\d+)/m', $stat, $m)) {
                    $inactive = (int) $m[1];
                }

                return self::validCgroup((int) $max, (int) trim($current) - $inactive, $hostTotal);
            }
        }

        // cgroup v1
        $limit = self::readFile('/sys/fs/cgroup/memory/memory.limit_in_bytes');
        $usage = self::readFile('/sys/fs/cgroup/memory/memory.usage_in_bytes');
        if ($limit !== null && $usage !== null && ctype_digit(trim($limit)) && ctype_digit(trim($usage))) {
            $inactive = 0;
            $stat = self::readFile('/sys/fs/cgroup/memory/memory.stat');
            if ($stat !== null && preg_match('/^total_inactive_file\s+(\d+)/m', $stat, $m)) {
                $inactive = (int) $m[1];
            }

            return self::validCgroup((int) trim($limit), (int) trim($usage) - $inactive, $hostTotal);
        }

        return null;
    }

    private static function validCgroup(int $limit, int $used, int $hostTotal): ?array
    {
        // "unlimited" is reported as "max" or a gigantic number; ignore limits above host RAM
        if ($limit <= 0 || ($hostTotal > 0 && $limit >= $hostTotal)) {
            return null;
        }

        return ['used' => max(0, $used), 'total' => $limit];
    }

    public static function getMemoryUsage(): array
    {
        if (self::isWindows()) {
            return self::getMemoryUsageWindows();
        }

        $info = self::readMeminfo();

        if (isset($info['MemTotal']) && $info['MemTotal'] > 0) {
            $total = $info['MemTotal'];

            // Container memory limit takes priority (Docker)
            if ($cgroup = self::readCgroupMemory($total)) {
                return self::memoryArray($cgroup['used'], $cgroup['total']);
            }

            if (isset($info['MemAvailable'])) {
                $used = $total - $info['MemAvailable'];
            } else {
                $used = $total - ($info['MemFree'] ?? 0) - ($info['Buffers'] ?? 0) - ($info['Cached'] ?? 0);
            }

            return self::memoryArray($used, $total);
        }

        // Fallback: `free -b`
        $output = self::run('free -b');
        if ($output && preg_match('/^Mem:\s+(\d+)\s+(\d+)/mi', $output, $m)) {
            return self::memoryArray((int) $m[2], (int) $m[1]);
        }

        return self::emptyMemory();
    }

    private static function getMemoryUsageWindows(): array
    {
        $output = self::run('wmic os get TotalVisibleMemorySize,FreePhysicalMemory /value');
        if ($output
            && preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $t)
            && preg_match('/FreePhysicalMemory=(\d+)/', $output, $f)) {
            $total = (int) $t[1] * 1024;
            $free = (int) $f[1] * 1024;

            return self::memoryArray($total - $free, $total);
        }

        return self::emptyMemory();
    }

    public static function getSwapMemory(): array
    {
        if (self::isWindows()) {
            return self::emptyMemory();
        }

        $info = self::readMeminfo();
        if (isset($info['SwapTotal'])) {
            $total = $info['SwapTotal'];
            $used = $total - ($info['SwapFree'] ?? 0);

            return self::memoryArray($used, $total);
        }

        $output = self::run('free -b');
        if ($output && preg_match('/^Swap:\s+(\d+)\s+(\d+)/mi', $output, $m)) {
            return self::memoryArray((int) $m[2], (int) $m[1]);
        }

        return self::emptyMemory();
    }

    // ------------------------------------------------------------------
    // Disk
    // ------------------------------------------------------------------

    public static function getDiskUsage(?string $path = null): array
    {
        $path ??= function_exists('base_path') ? base_path() : (self::isWindows() ? 'C:' : '/');

        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false || $free === false || $total <= 0) {
            return ['used' => 'N/A', 'total' => 'N/A', 'free' => 'N/A', 'percent' => 0.0];
        }

        $used = $total - $free;

        return [
            'used' => self::formatBytes($used),
            'total' => self::formatBytes($total),
            'free' => self::formatBytes($free),
            'percent' => self::percent($used, $total),
        ];
    }

    // ------------------------------------------------------------------
    // Load / uptime / processes
    // ------------------------------------------------------------------

    public static function getLoadAverage(): array
    {
        if (self::isWindows() || !function_exists('sys_getloadavg')) {
            return ['1' => 0, '5' => 0, '15' => 0];
        }

        $loads = @sys_getloadavg();
        if (!is_array($loads)) {
            return ['1' => 0, '5' => 0, '15' => 0];
        }

        return [
            '1' => round($loads[0], 2),
            '5' => round($loads[1], 2),
            '15' => round($loads[2], 2),
        ];
    }

    private static function getUptimeSeconds(): ?int
    {
        if (self::isWindows()) {
            $output = self::run('wmic os get lastbootuptime /value');
            if ($output && preg_match('/=(\d{14})/', $output, $m)) {
                $boot = \DateTime::createFromFormat('YmdHis', $m[1]);

                return $boot ? max(0, time() - $boot->getTimestamp()) : null;
            }

            return null;
        }

        $uptime = self::readFile('/proc/uptime');
        if ($uptime !== null && preg_match('/^(\d+(?:\.\d+)?)/', $uptime, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    public static function getUptime(): string
    {
        $uptime = self::getUptimeSeconds();
        if ($uptime === null) {
            return 'Unknown';
        }

        $days = intdiv($uptime, 86400);
        $hours = intdiv($uptime % 86400, 3600);
        $minutes = intdiv($uptime % 3600, 60);

        return "{$days}d {$hours}h {$minutes}m";
    }

    public static function getLastReboot(): string
    {
        $uptime = self::getUptimeSeconds();

        return $uptime === null ? 'Unknown' : date('Y-m-d H:i:s', time() - $uptime);
    }

    public static function getProcessCount(): array
    {
        if (self::isWindows()) {
            $output = self::run('tasklist /fo csv /nh');
            $total = $output ? count(array_filter(explode("\n", trim($output)))) : 0;

            return ['total' => $total, 'running' => $total];
        }

        $total = 0;
        $running = 0;

        if (is_dir('/proc')) {
            foreach (@scandir('/proc') ?: [] as $entry) {
                if (!ctype_digit($entry)) {
                    continue;
                }
                $total++;

                $stat = self::readFile("/proc/{$entry}/stat");
                if ($stat !== null && preg_match('/\)\s+(\w)\s/', $stat, $m) && $m[1] === 'R') {
                    $running++;
                }
            }
        }

        return ['total' => $total, 'running' => $running];
    }

    public static function getHostname(): string
    {
        return gethostname() ?: 'Unknown';
    }

    /**
     * Whether the panel itself runs inside a container.
     */
    public static function isContainer(): bool
    {
        return !self::isWindows() && (file_exists('/.dockerenv') || file_exists('/run/.containerenv'));
    }

    // ------------------------------------------------------------------
    // Aggregates
    // ------------------------------------------------------------------

    public static function getSystemInfo(): array
    {
        $load = self::getLoadAverage();

        return [
            'cpu' => self::getCpuUsage(),
            'cpu_cores' => self::getCpuCores(),
            'cpu_model' => self::getCpuModel(),
            'memory' => self::getMemoryUsage(),
            'swap' => self::getSwapMemory(),
            'disk' => self::getDiskUsage(),
            'load' => [
                '1min' => $load['1'] ?? 0,
                '5min' => $load['5'] ?? 0,
                '15min' => $load['15'] ?? 0,
            ],
            'uptime' => self::getUptime(),
            'last_reboot' => self::getLastReboot(),
            'processes' => self::getProcessCount(),
            'hostname' => self::getHostname(),
            'os' => php_uname('s'),
            'php_version' => PHP_VERSION,
            'container' => self::isContainer(),
        ];
    }

    public static function formatBytes($bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        $bytes = max((float) $bytes, 0);
        $pow = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }
}
