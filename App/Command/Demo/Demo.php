<?php


namespace App\Command\Demo;


use App\Command\CommandBase;

class Demo extends CommandBase
{
    protected $commandName='demo';

    public function handle($args){
        echo 'handle something with args: ';
        echo PHP_EOL;
        echo implode(' ',$args);
        echo PHP_EOL;
    }
}