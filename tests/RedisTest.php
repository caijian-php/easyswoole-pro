<?php


namespace tests;


use EasySwoole\RedisPool\Redis;
use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{
    function testCon(){
        $redisMaster = Redis::defer('master');
        $key = 'ping';
        $val = 'pong';
        $redisMaster->set($key,$val);
//        $redisCluster = Redis::defer('cluster');
//        $get = $redisCluster->get($key);
        $get = $redisMaster->get($key);
        $this->assertEquals($val,$get);
    }
}