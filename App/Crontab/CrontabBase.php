<?php


namespace App\Crontab;


use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Crontab\AbstractCronTask;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Task\TaskManager;

class CrontabBase  extends AbstractCronTask
{
    static $taskName='base';

    /** @var Logger */
    protected $logger;

    protected $logDir=EASYSWOOLE_ROOT.'/Log/Command';

    //*    *    *    *    *
    //-    -    -    -    -
    //|    |    |    |    |
    //|    |    |    |    |
    //|    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
    //|    |    |    +---------- month (1 - 12)
    //|    |    +--------------- day of month (1 - 31)
    //|    +-------------------- hour (0 - 23)
    //+------------------------- min (0 - 59)
    static $rule=[
        'min' => '*/1 * * * *', // (0 - 59)
        'hour' => '*/1 * * * *', // (0 - 23)
        'day' => '*/1 * * * *', // (1 - 31)
        'month' => '*/1 * * * *', // (1 - 12)
        'day-of-week' => '*/1 * * * *', // (0 - 7)
    ];


    public static function getRule(): string
    {
        return self::$rule['min'];
    }

    public static function getTaskName(): string
    {
        return  self::$taskName;
    }

    function run(int $taskId, int $workerIndex)
    {
        $this->init();
        if (method_exists($this, 'handle')) {
            $this->handle();
        }
    }

    function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        echo $throwable->getMessage();
    }

    public function init(){
        if (is_null($this->logger)){
            !is_dir($this->logDir) && mkdir($this->logDir);
            $this->logger = new Logger(new \EasySwoole\Log\Logger($this->logDir));
        }
    }
}