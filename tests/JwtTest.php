<?php

namespace tests;

use App\Service\Auth\JwtService;
use PHPUnit\Framework\TestCase;

class JwtTest extends TestCase
{
    function testCon(){
        $jwt = new JwtService();
        $token = $jwt->set(1);
        $data = $jwt->parse($token);
        self::assertEquals($jwt->getMsg(),NULL);
    }
}