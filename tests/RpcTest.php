<?php


namespace tests;


use App\Rpc\Client\DemoClient;
use PHPUnit\Framework\TestCase;

class RpcTest extends TestCase
{
    function testCon(){
        $demo = new DemoClient();
        $res = $demo->hello();
        var_dump($res);
    }
}