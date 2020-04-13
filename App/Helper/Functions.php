<?php

use EasySwoole\EasySwoole\Logger;
use Swoole\Coroutine as co;

if (! function_exists('dump')) {
    function dump($data){
        Logger::getInstance()->console($data);
    }
}

if (! function_exists('formatTime')) {
    function formatTime($startTime, $endTime){
        $diffTime = substr($endTime,0,10)  - substr($startTime,0,10);
        if ($diffTime<3600) {
            $min = floor($diffTime/60);
            $sec = ($diffTime - $min * 60);
            return  $min . '分钟' . $sec . '秒';
        }
        $hour = floor($diffTime/3600);
        $min = floor(($diffTime - $hour*3600)/60);
        return $hour . '小时' . $min . '分钟';
    }
}

if (! function_exists('num_format')) {
    function num_format($num, $decimals=2){
        return bcdiv(bcmul($num,100,$decimals),100,$decimals);
    }
}

if (! function_exists('enJson')) {
    function enJson($data)
    {
        return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('deJson')) {
    function deJson(string $json)
    {
        return json_decode($json,true);
    }
}

if (! function_exists('filePuts')){
    function filePuts($filename, $data, $flags = null){
        $cid = co::getCid();
        if($cid > -1){
            return co::writeFile($filename, $data, $flags);
        }else{
            return file_put_contents($filename, $data, $flags);
        }
    }
}

if (! function_exists('fileGets')) {
    function fileGets($filename)
    {
        $cid = co::getCid();
        if ($cid > -1) {
            return co::readFile($filename);
        } else {
            return file_get_contents($filename);
        }
    }
}

if (! function_exists('toLog')) {
    function toLog($info,$fileName = '')
    {
        $fileName or $fileName = 'debug';
        $dir = config('LOG_DIR').'/debug/';
        is_dir($dir) OR mkdir($dir);
        $fileName = $dir.addslashes($fileName).'.log';
        return filePuts($fileName,"[".date('Y-m-d H:i:s')."]".PHP_EOL.var_export($info,true).PHP_EOL.PHP_EOL,FILE_APPEND);
    }
}