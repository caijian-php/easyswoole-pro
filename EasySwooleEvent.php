<?php
namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Symfony\Component\Finder\Finder;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
        require EASYSWOOLE_ROOT.'/WorkStartEvent.php';
        echo Utility::displayItem('initializing','EasySwooleEvent initializing'.PHP_EOL);
        self::optimumConfig();
        self::loadConfigFile();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        echo Utility::displayItem('mainServerCreating','EasySwooleEvent mainServerCreating'.PHP_EOL);
        self::registerService();
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
    }

    protected static function registerService()
    {
    }

    public static function loadConfigFile(){
        $configs = [];
        $paths = EASYSWOOLE_ROOT.'/config';
        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');
        foreach ($finder ?? [] as $file) {
            $configs[$file->getBasename('.php')] =  require $file->getRealPath();
        }
        Config::getInstance()->merge($configs);
    }

    protected static function optimumConfig()
    {
        $out = intval(shell_exec('cat /proc/cpuinfo |grep processor|wc -l'));
        $config = Config::getInstance();
        $keys = [
            'MAIN_SERVER.SETTING.reactor_num',
            'MAIN_SERVER.SETTING.worker_num',
            'MAIN_SERVER.TASK.workerNum',
        ];
        foreach ($keys as $key){
            empty($config->getConf($key)) && $config->setConf($key,$out);
        }
        $config->setConf('MAIN_SERVER.SETTING.buffer_output_size',32 * 1024 *1024);
    }
}