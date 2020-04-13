<?php


namespace tests;


use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    function testCon()
    {
        $builder = new QueryBuilder();
        $builder->raw('select version()');
        $ret = DbManager::getInstance()->query($builder,true)->getResult();
        $this->assertArrayHasKey('version()',$ret[0]);
    }
}