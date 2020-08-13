<?php


namespace App\HttpController\Demo;


use App\HttpController\ApiBase;
use App\Sync\Driver\DemoDriver;
use App\Sync\Invoker\DemoInvoker;

class Demo extends ApiBase
{
    public function index()
    {
        // TODO: Implement index() method.
    }

    public function test1(){
        $ret = DemoInvoker::getInstance()->client()->test(1,2);
        var_dump($ret);
        var_dump(DemoInvoker::getInstance()->client()->a());
        var_dump(DemoInvoker::getInstance()->client()->a(1));
        var_dump(DemoInvoker::getInstance()->client()->fuck());
        $ret = DemoInvoker::getInstance()->client()->callback(function (DemoDriver $driver){
            $std = $driver->getStdClass();
            if(isset($std->time)){
                return $std->time;
            }else{
                $std->time = time();
                return 'new set time';
            }
        });
        var_dump($ret);
    }
}