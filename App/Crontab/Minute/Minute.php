<?php


namespace App\Crontab\Minute;


use App\Crontab\CrontabBase;
use EasySwoole\EasySwoole\Task\TaskManager;

class Minute extends CrontabBase
{
    static $taskName = 'minute';

    function handle(){
        var_dump('1 minute latter');
        TaskManager::getInstance()->async(function (){
            var_dump('1 minute latter in async');
        });
    }
}