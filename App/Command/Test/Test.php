<?php


namespace App\Command\Test;


use App\Command\CommandBase;

class Test extends CommandBase
{
    protected $commandName='test';

    public function handle($args){
        echo 'handle something with args: ';
        echo PHP_EOL;
        echo implode(' ',$args);
        echo PHP_EOL;
    }
}