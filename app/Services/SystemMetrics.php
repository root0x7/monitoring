<?php

namespace App\Services;

class SystemMetrics
{

    public function getAllMetrics(): array
    {
        return [
            'cpu_busy' => $this->getCpuBusy(),
            'sysload' => $this->getSysLoad(),
            'ram_used' => $this->getRamUsed(),
            'swap' => $this->getSwapUsage(),
            'root_fs' => $this->getRootFileSystem(),
            'cpu_cores' => $this->getCpuCores(),
            'uptime' => $this->getUptime(),
            'ram_total' => $this->getTotalRam(),
            'swap_total' => $this->getTotalSwap(),
            'cpu_info' => $this->getCpuInfo(),
            'memory_details' => $this->getMemoryDetails(),
            'load_average' => $this->getLoadAverage(),
            'timestamp' => now()->toISOString()
        ];
    }

    public function getCpuBusy(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['percentage' => null, 'status' => 'Linux only'];
        }

        try {
            $stat1 = file_get_contents('/proc/stat');
            $cpu1 = $this->parseCpuStat($stat1);
            
            sleep(1);
            
            $stat2 = file_get_contents('/proc/stat');
            $cpu2 = $this->parseCpuStat($stat2);
            
            $totalDiff = ($cpu2['total'] - $cpu1['total']);
            $idleDiff = ($cpu2['idle'] - $cpu1['idle']);
            
            if ($totalDiff > 0) {
                $cpuBusy = 100 - (($idleDiff / $totalDiff) * 100);
                return [
                    'percentage' => round($cpuBusy, 2),
                    'status' => 'active'
                ];
            }
            
            return ['percentage' => 0, 'status' => 'active'];
            
        } catch (\Exception $e) {
            return ['percentage' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function getSysLoad(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['load' => null, 'status' => 'Linux only'];
        }

        try {
            $load = sys_getloadavg();
            $cpuCores = $this->getCpuCoresCount();
            
            return [
                'load_1min' => round($load[0], 2),
                'load_5min' => round($load[1], 2),
                'load_15min' => round($load[2], 2),
                'load_1min_percent' => $cpuCores ? round(($load[0] / $cpuCores) * 100, 2) : null,
                'cpu_cores' => $cpuCores,
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['load' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function getRamUsed(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            $used = memory_get_usage(true);
            return [
                'used_bytes' => $used,
                'used_mb' => round($used / 1024 / 1024, 2),
                'used_gb' => round($used / 1024 / 1024 / 1024, 2),
                'status' => 'PHP memory only'
            ];
        }

        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $memData = $this->parseMeminfo($meminfo);
            
            $totalRam = $memData['MemTotal'] * 1024;
            $freeRam = $memData['MemFree'] * 1024;
            $buffers = ($memData['Buffers'] ?? 0) * 1024;
            $cached = ($memData['Cached'] ?? 0) * 1024;
            
            $usedRam = $totalRam - $freeRam - $buffers - $cached;
            $usedPercentage = ($usedRam / $totalRam) * 100;
            
            return [
                'used_bytes' => $usedRam,
                'used_mb' => round($usedRam / 1024 / 1024, 2),
                'used_gb' => round($usedRam / 1024 / 1024 / 1024, 2),
                'used_percentage' => round($usedPercentage, 2),
                'total_bytes' => $totalRam,
                'total_gb' => round($totalRam / 1024 / 1024 / 1024, 2),
                'free_bytes' => $freeRam,
                'free_gb' => round($freeRam / 1024 / 1024 / 1024, 2),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['used_bytes' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getSwapUsage(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['swap' => null, 'status' => 'Linux only'];
        }

        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $memData = $this->parseMeminfo($meminfo);
            
            $swapTotal = ($memData['SwapTotal'] ?? 0) * 1024;
            $swapFree = ($memData['SwapFree'] ?? 0) * 1024;
            $swapUsed = $swapTotal - $swapFree;
            
            return [
                'total_bytes' => $swapTotal,
                'total_mb' => round($swapTotal / 1024 / 1024, 2),
                'total_gb' => round($swapTotal / 1024 / 1024 / 1024, 2),
                'used_bytes' => $swapUsed,
                'used_mb' => round($swapUsed / 1024 / 1024, 2),
                'used_gb' => round($swapUsed / 1024 / 1024 / 1024, 2),
                'free_bytes' => $swapFree,
                'free_mb' => round($swapFree / 1024 / 1024, 2),
                'used_percentage' => $swapTotal > 0 ? round(($swapUsed / $swapTotal) * 100, 2) : 0,
                'status' => $swapTotal > 0 ? 'active' : 'no_swap'
            ];
        } catch (\Exception $e) {
            return ['swap' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function getRootFileSystem(): array
    {
        try {
            $rootPath = '/';
            if (PHP_OS_FAMILY !== 'Linux') {
                $rootPath = base_path();
            }
            
            $totalBytes = disk_total_space($rootPath);
            $freeBytes = disk_free_space($rootPath);
            $usedBytes = $totalBytes - $freeBytes;
            
            return [
                'path' => $rootPath,
                'total_bytes' => $totalBytes,
                'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
                'used_bytes' => $usedBytes,
                'used_gb' => round($usedBytes / 1024 / 1024 / 1024, 2),
                'free_bytes' => $freeBytes,
                'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
                'used_percentage' => round(($usedBytes / $totalBytes) * 100, 2),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['filesystem' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getCpuCores(): array
    {
        $cores = $this->getCpuCoresCount();
        $cpuInfo = $this->getCpuModelName();
        
        return [
            'physical_cores' => $cores,
            'logical_cores' => $cores,
            'cpu_model' => $cpuInfo,
            'status' => $cores ? 'active' : 'unknown'
        ];
    }


    public function getUptime(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['uptime' => null, 'status' => 'Linux only'];
        }

        try {
            $uptime = file_get_contents('/proc/uptime');
            $uptimeSeconds = (float) explode(' ', $uptime)[0];
            
            return [
                'seconds' => (int) $uptimeSeconds,
                'minutes' => round($uptimeSeconds / 60, 2),
                'hours' => round($uptimeSeconds / 3600, 2),
                'days' => round($uptimeSeconds / 86400, 2),
                'formatted' => $this->formatUptime($uptimeSeconds),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['uptime' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getTotalRam(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['total_ram' => null, 'status' => 'Linux only'];
        }

        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $memData = $this->parseMeminfo($meminfo);
            $totalRam = $memData['MemTotal'] * 1024;
            
            return [
                'total_bytes' => $totalRam,
                'total_mb' => round($totalRam / 1024 / 1024, 2),
                'total_gb' => round($totalRam / 1024 / 1024 / 1024, 2),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['total_ram' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    public function getTotalSwap(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['total_swap' => null, 'status' => 'Linux only'];
        }

        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $memData = $this->parseMeminfo($meminfo);
            $totalSwap = ($memData['SwapTotal'] ?? 0) * 1024;
            
            return [
                'total_bytes' => $totalSwap,
                'total_mb' => round($totalSwap / 1024 / 1024, 2),
                'total_gb' => round($totalSwap / 1024 / 1024 / 1024, 2),
                'status' => $totalSwap > 0 ? 'active' : 'no_swap'
            ];
        } catch (\Exception $e) {
            return ['total_swap' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getCpuInfo(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['cpu_info' => null, 'status' => 'Linux only'];
        }

        try {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            $lines = explode("\n", $cpuinfo);
            $info = [];
            
            foreach ($lines as $line) {
                if (strpos($line, 'model name') === 0) {
                    $info['model_name'] = trim(explode(':', $line)[1]);
                    break;
                }
            }
            
            return [
                'model_name' => $info['model_name'] ?? 'Unknown',
                'cores' => $this->getCpuCoresCount(),
                'architecture' => php_uname('m'),
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['cpu_info' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getMemoryDetails(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['memory_details' => null, 'status' => 'Linux only'];
        }

        try {
            $meminfo = file_get_contents('/proc/meminfo');
            $memData = $this->parseMeminfo($meminfo);
            
            return [
                'total' => ($memData['MemTotal'] ?? 0) * 1024,
                'free' => ($memData['MemFree'] ?? 0) * 1024,
                'available' => ($memData['MemAvailable'] ?? 0) * 1024,
                'buffers' => ($memData['Buffers'] ?? 0) * 1024,
                'cached' => ($memData['Cached'] ?? 0) * 1024,
                'swap_cached' => ($memData['SwapCached'] ?? 0) * 1024,
                'active' => ($memData['Active'] ?? 0) * 1024,
                'inactive' => ($memData['Inactive'] ?? 0) * 1024,
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['memory_details' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }


    public function getLoadAverage(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return ['load_average' => null, 'status' => 'Linux only'];
        }

        try {
            $loadavg = file_get_contents('/proc/loadavg');
            $parts = explode(' ', trim($loadavg));
            $cpuCores = $this->getCpuCoresCount();
            
            return [
                'load_1min' => (float) $parts[0],
                'load_5min' => (float) $parts[1],
                'load_15min' => (float) $parts[2],
                'processes' => $parts[3] ?? null, // running/total processes
                'last_pid' => (int) ($parts[4] ?? 0),
                'load_1min_percent' => $cpuCores ? round(((float) $parts[0] / $cpuCores) * 100, 2) : null,
                'cpu_cores' => $cpuCores,
                'status' => 'active'
            ];
        } catch (\Exception $e) {
            return ['load_average' => null, 'status' => 'error', 'error' => $e->getMessage()];
        }
    }

    private function parseCpuStat(string $stat): array
    {
        $lines = explode("\n", $stat);
        $cpuLine = $lines[0];
        $values = preg_split('/\s+/', $cpuLine);
        
        $user = (int) $values[1];
        $nice = (int) $values[2];
        $system = (int) $values[3];
        $idle = (int) $values[4];
        $iowait = (int) ($values[5] ?? 0);
        $irq = (int) ($values[6] ?? 0);
        $softirq = (int) ($values[7] ?? 0);
        
        $total = $user + $nice + $system + $idle + $iowait + $irq + $softirq;
        
        return [
            'total' => $total,
            'idle' => $idle
        ];
    }

    private function parseMeminfo(string $meminfo): array
    {
        $data = [];
        $lines = explode("\n", $meminfo);
        
        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s*(\d+)\s*kB?/', $line, $matches)) {
                $data[$matches[1]] = (int) $matches[2];
            }
        }
        
        return $data;
    }

    private function getCpuCoresCount(): ?int
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return null;
        }
        
        try {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            return substr_count($cpuinfo, 'processor');
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getCpuModelName(): ?string
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return null;
        }
        
        try {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            $lines = explode("\n", $cpuinfo);
            
            foreach ($lines as $line) {
                if (strpos($line, 'model name') === 0) {
                    return trim(explode(':', $line)[1]);
                }
            }
        } catch (\Exception $e) {
            return null;
        }
        
        return null;
    }

    private function formatUptime(float $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);

        return sprintf('%d kun, %d soat, %d daqiqa, %d soniya', $days, $hours, $minutes, $secs);
    }
}