<?php


namespace App\Command;

use EasySwoole\EasySwoole\Command\CommandInterface;
use EasySwoole\EasySwoole\Command\Utility;
use EasySwoole\EasySwoole\Logger;

class CommandBase implements CommandInterface
{
    protected $commandName;

    /** @var Logger */
    protected $logger;

    protected $logDir=EASYSWOOLE_ROOT.'/Log/Command';

    public function commandName(): string
    {
        return $this->commandName;
    }

    public function exec(array $args): ?string
    {
        $this->init();
        if (method_exists($this,'handle')) {
            $this->handle($args);
        }else{
            $this->logger->error('There is no handle method for '.get_called_class());
        }
        return null;
    }

    public function help(array $args): ?string
    {
        $logo = Utility::easySwooleLog();
        return $logo;
    }

    public function init(){
        if (is_null($this->logger)){
            !is_dir($this->logDir) && mkdir($this->logDir);
            $this->logger = new Logger(new \EasySwoole\Log\Logger($this->logDir));
        }
        $item = ["Command ".$this->commandName, "starting at ".date('Y-m-d H:i:s')];
        echo Utility::displayItem(...$item).PHP_EOL;
        $this->log(implode(' ',$item));
    }

    public function console($msg){
        $this->logger->console($msg);
    }

    public function log($msg){
        $this->logger->log($msg);
    }

    public function error($msg){
        $this->logger->error($msg);
    }
}