<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 2019/8/17
 * Time: 15:09
 */

namespace app\admin\controller;


use app\common\controller\Backend;

class Home extends Backend
{
    public function console()
    {
        exec("netstat -tan | grep \"ESTABLISHED\" | grep \":80\" | wc -l 2>&1", $output, $status);

        //获取当前tcp连接数
        $TcpNum = 0;
        if ($output) {
            $TcpNum = $output[0];
        }

        //查看日志中访问次数最多的前10个IP
        exec("cat /home/zzcard/wwwlogs/api.log |cut -d ' ' -f 1 |sort |uniq -c | sort -nr | awk '{print $0 }' | head -n 10 |less 2>&1", $output, $status);
        $ipMax = [];
        if ($output) {
            foreach ($output as $v) {
                $data = explode(' ', trim($v));

                if (count($data) > 1) {
                    list($num, $ip) = explode(' ', trim($v));
                    $ipMax[] = [
                        'num' => $num,
                        'ip'  => $ip
                    ];
                }
            }
        }

        //查看日志中出现100次以上的IP
        $hIp = [];
        exec("cat /home/zzcard/wwwlogs/api.log |cut -d ' ' -f 1 |sort |uniq -c | sort -nr | awk '{if($1>0)print $0 }'  |less 2>&1", $output1, $status);
        if ($output1) {
            foreach ($output1 as $v) {
                $data = explode(' ', trim($v));
                if (count($data) > 1) {
                    list($num, $ip) = explode(' ', trim($v));
                    $hIp[] = [
                        'num' => $num,
                        'ip'  => $ip
                    ];
                }
            }
        }


        exec("cat /home/zzcard/wwwlogs/api.log | awk '{if(\$NF>0)print $7,\$NF,$11}' |sort -n|uniq -c|sort -nr|head -20 2>&1", $output2, $status);
        $maxTimeUrl = [];

        if ($output2) {
            foreach ($output2 as $v) {
                $data = explode(' ', trim($v));
                if (count($data) > 2) {
                    list($num, $url, $time) = explode(' ', trim($v));
                    $maxTimeUrl[] = [
                        'num'  => $num,
                        'url'  => $url,
                        'time' => $time
                    ];
                }
            }
        }
        //pv
        exec("awk '{print $7}' /home/zzcard/wwwlogs/api.log | wc -l", $output3, $status);
        //独立ip
        exec("awk '{print $1}' /home/zzcard/wwwlogs/api.log | sort -r |uniq -c | wc -l", $output4, $status);
        //uv
        exec("awk '{print $11}' /home/zzcard/wwwlogs/api.log | sort -r |uniq -c | wc -l", $output5, $status);
        dump(app('System')->get_used_status());exit;
//        dump(date('Ymd',strtotime('+2 year',strtotime('20170901'))));exit;
        return $this->fetch();
    }
}