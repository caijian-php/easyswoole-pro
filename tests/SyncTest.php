<?php


namespace tests;


use App\Sync\Driver\DemoDriver;
use App\Sync\Invoker\DemoInvoker;
use EasySwoole\EasySwoole\ServerManager;
use PHPUnit\Framework\TestCase;

class SyncTest extends TestCase
{
    function testCon(){
        // Server没有启动的都无法调用，此处仅作展示
    }
}