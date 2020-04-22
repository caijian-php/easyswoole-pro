<?php


namespace App\Command;


use EasySwoole\EasySwoole\Command\CommandContainer;

class Loader
{
    public static function init(): bool
    {
        $commands = require (APP_COMMAND_DIR.'/CommandConfig.php');
        $contain = CommandContainer::getInstance();
        foreach ($commands as $cmd){
            $contain->set(new $cmd);
        }
        return true;
    }
}