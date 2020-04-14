<?php


namespace tests;


use App\Rpc\Client\Demo;
use PHPUnit\Framework\TestCase;

class RpcTest extends TestCase
{
    function testCon(){
        $demo = new Demo();
        $res = $demo->hello();
        var_dump($res);
    }
}