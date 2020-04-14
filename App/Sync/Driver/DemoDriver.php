<?php


namespace App\Sync\Driver;


use EasySwoole\SyncInvoker\AbstractInvoker;

class DemoDriver extends AbstractInvoker{

    private $stdclass;

    function __construct()
    {
        $this->stdclass = new \stdClass();
        parent::__construct();
    }

    public function test($a,$b)
    {
        return $a+$b;
    }

    public function a()
    {
        return 'this is a';
    }

    public function getStdClass()
    {
        return $this->stdclass;
    }
}