<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


if (!function_exists('is_window')) {
    /**
     * 判断是否是windows服务器
     * @return bool
     */
    function is_window()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? true : false;
    }
}

if (!function_exists('get_process_num')) {
    /**
     * 查看进程
     * @param $daemon
     * @return int|string
     */
    function get_process_num($daemon)
    {
        if (function_exists('exec') && !is_window()) {
            return exec("ps -ef | grep {$daemon} | grep -v grep | wc -l");
        } else {
            return 0;
        }
    }

}

if (!function_exists('charset')) {
    function charset($str, $out_charset = 'UTF-8')
    {
        $encode = mb_detect_encoding($str, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
        $line   = iconv($encode, $out_charset, $str);
        return $line;
    }
}

if (!function_exists('merge_spaces')) {
    function merge_spaces($string)
    {
        return preg_replace("/\s(?=\s)/", "\\1", trim($string));
    }
}

if (!function_exists('loadRoutesFile')) {
    /**
     * 递归文件
     * @param $path
     * @return array
     */
    function loadRoutesFile($path)
    {
        $allRoutesFilePath = array();
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                $allRoutesFilePath = array_merge($allRoutesFilePath, loadRoutesFile($file . '/*'));
            } else {
                $allRoutesFilePath[] = $file;
            }
        }
        return $allRoutesFilePath;
    }
}


if (!function_exists('odd')) {

    function odd($var)
    {
        // 返回$var最后一个二进制位，
        // 为1则保留（奇数的二进制的最后一位肯定是1）
        return ($var & 1);
    }
}
if (!function_exists('even')) {
    function even($var)
    {
        // 返回$var最后一个二进制位，
        // 为0则保留（偶数的二进制的最后一位肯定是0）
        return (!($var & 1));
    }
}

