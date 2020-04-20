<?php


namespace tests;


use EasySwoole\WordsMatch\WordsMatchClient;
use PHPUnit\Framework\TestCase;

class WordsMatchTest extends TestCase
{
    function testCon()
    {
        // Server没有启动的都无法调用，此处仅作展示
        $res = WordsMatchClient::getInstance()->search('暴露的');
        self::assertNotEmpty($res);
    }
}