<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/8/20
 * Time: 9:22
 */

namespace app\common\library;

class System
{
    /**
     * 判断指定路径下指定文件是否存在，如不存在则创建
     * @param string $fileName 文件名
     * @param string $content 文件内容
     * @return string 返回文件路径
     */
    private function getFilePath($fileName, $content)
    {
        $path = dirname(config('log.path')) . DIRECTORY_SEPARATOR . 'script' . "\\$fileName";
        if (!file_exists(dirname(config('log.path')) . DIRECTORY_SEPARATOR . 'script')) {
            mkdir(dirname(config('log.path')) . DIRECTORY_SEPARATOR . 'script', 0777, true);
        }
        if (!file_exists($path)) {
            file_put_contents($path, $content);
        }
        return $path;
    }

    /**
     * 获得cpu使用率vbs文件生成函数
     * @return string 返回vbs文件路径
     */
    private function getCupUsageVbsPath()
    {
        return $this->getFilePath(
            'cpu_usage.vbs',
            "On Error Resume Next
    Set objProc = GetObject(\"winmgmts:\\\\.\\root\cimv2:win32_processor='cpu0'\")
    WScript.Echo(objProc.LoadPercentage)"
        );
    }

    /**
     * 获得总内存及可用物理内存JSON vbs文件生成函数
     * @return string 返回vbs文件路径
     */
    private function getMemoryUsageVbsPath()
    {
        return $this->getFilePath(
            'memory_usage.vbs',
            "On Error Resume Next
    Set objWMI = GetObject(\"winmgmts:\\\\.\\root\cimv2\")
    Set colOS = objWMI.InstancesOf(\"Win32_OperatingSystem\")
    For Each objOS in colOS
     Wscript.Echo(\"{\"\"TotalVisibleMemorySize\"\":\" & objOS.TotalVisibleMemorySize & \",\"\"FreePhysicalMemory\"\":\" & objOS.FreePhysicalMemory & \",\"\"FreeVirtualMemory\"\":\" & objOS.FreeVirtualMemory & \",\"\"TotalVirtualMemorySize\"\":\" & objOS.TotalVirtualMemorySize & \"}\")
    Next"
        );
    }

    /**
     * 获得CPU使用率
     * @return Number
     */
    public function getCpuUsage()
    {
        $path = $this->getCupUsageVbsPath();
        exec("cscript -nologo $path", $usage);
        return $usage[0];
    }

    /**
     * 获得内存使用率数组
     * @return array
     */
    public function getMemoryUsage()
    {
        $path = $this->getMemoryUsageVbsPath();
        exec("cscript -nologo $path", $usage);
        $memory            = json_decode($usage[0], true);
        $memory['visible'] = Round((($memory['TotalVisibleMemorySize'] - $memory['FreePhysicalMemory']) / $memory['TotalVisibleMemorySize']) * 100);
        $memory['virtual'] = Round((($memory['TotalVirtualMemorySize'] - $memory['FreeVirtualMemory']) / $memory['TotalVirtualMemorySize']) * 100);
        return $memory;
    }

    /**
     * 获取真实本机ip
     * @return string
     */
    public function getLocalIP()
    {
        $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
        //获取操作系统为win2000/xp、win7的本机IP真实地址
        if (is_window()) {
            exec("ipconfig", $out, $stats);
            if (!empty($out)) {
                foreach ($out AS $row) {
                    if (strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
                        $tmpIp = explode(":", $row);
                        if (preg_match($preg, trim($tmpIp[1]))) {
                            return trim($tmpIp[1]);
                        }
                    }
                }
            }
        } else {
            //获取操作系统为linux类型的本机IP真实地址
            exec("ifconfig", $out, $stats);
            if (!empty($out)) {
                if (isset($out[1]) && strstr($out[1], 'addr:')) {
                    $tmpArray = explode(":", $out[1]);
                    $tmpIp    = explode(" ", $tmpArray[1]);
                    if (preg_match($preg, trim($tmpIp[0]))) {
                        return trim($tmpIp[0]);
                    }
                }
            }
        }

        return '127.0.0.1';
    }

    /**
     * 获取window cpu信息
     * @param string $field
     * @return array
     */
    public function getCupInfo($field = 'name,NumberOfCores,NumberOfLogicalProcessors,addresswidth,CurrentClockSpeed,Architecture,SystemName')
    {
        $array  = explode(',', $field);
        $output = [];
        foreach ($array as $key => $value) {
            exec("wmic cpu get " . $value, $output, $status);
        }
        $data = [];
        foreach ($output as $value) {
            if ($value != '') {
                $data[] = $value;
            }
        }
        $key   = array_filter($data, "even", ARRAY_FILTER_USE_KEY);
        $value = array_filter($data, "odd", ARRAY_FILTER_USE_KEY);
        $data  = array_combine($key, $value);
        return $data;
    }

    /**
     * cpu核数
     * @return mixed|string
     */
    public function getCore()
    {
        if (is_window()) {
            $info = $this->getCupInfo('NumberOfCores');
            if ($info) {
                return $info['NumberOfCores'];
            }
        } else {
            exec("cat /proc/cpuinfo |grep \"cpu cores\"|wc -l ", $out, $status);
            if ($status == 0) {
                return $out[0];
            }
        }
        return '未知';
    }

    /**
     * 逻辑处理器个数
     * @return mixed|string
     */
    public function getProcessor()
    {
        if (is_window()) {
            $info = $this->getCupInfo('NumberOfLogicalProcessors');
            if ($info) {
                return $info['NumberOfLogicalProcessors'];
            }
        } else {
            exec("cat /proc/cpuinfo |grep \"processor\"|wc -l ", $out, $status);
            if ($status == 0) {
                return $out[0];
            }
        }
        return '未知';
    }

    /**
     * 获取物理cpu个数
     */
    public function physical()
    {
        if (is_window()) {
            $info = $this->getCupInfo('CpuStatus');
            if ($info) {
                return 0;
            }
        } else {
            exec("cat /proc/cpuinfo| grep \"physical id\"| sort| uniq| wc -l", $out, $status);
            if ($status == 0) {
                return $out[0];
            }
        }
        return '未知';
    }

    public function getOS()
    {
        $ex = '';
        if (is_window()) {
            $info = $this->getCupInfo('AddressWidth');
            if ($info) {
                $ex = $info['AddressWidth'];
            }
        } else {
            exec("getconf LONG_BIT", $out, $status);
            if ($status) {
                $ex = $out[0];
            }
        }
        return PHP_OS . ' x_' . $ex;
    }

    public function uv($time = '', $path = '')
    {
        if (!$path) {
            $path = config('admin.access_log');
        }
        if ($time) {
            exec("grep \"$time\" $path | awk '{print $11}' | sort | uniq -c| sort -nr | wc -l", $out, $status);
            if ($status == 0) {
                return $out[0];
            }
        } else {
            exec("awk '{print $11}' $path | sort -r |uniq -c | wc -l", $output5, $status);
            if ($status == 0) {
                return $output5[0];
            }
        }
        return 0;
    }
}