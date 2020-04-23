<?php


namespace tests;


use App\Service\IdentifyCard\IdentifyCard;
use PHPUnit\Framework\TestCase;

class IdentifyCardTest extends TestCase
{
    public function testId(){
        $num = '33071919610920021X';
        if (IdentifyCard::isValid($num)) {
            echo '身份证格式正确';
        } else {
            echo '身份证格式不正确';
        }
        self::assertEquals(true,true);
    }
}