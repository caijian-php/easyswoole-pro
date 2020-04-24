<?php


namespace tests;


use App\Service\IdVerifyService;
use PHPUnit\Framework\TestCase;

class IdCardTest extends TestCase
{
    public function testCon(){

    }

    /**
     * 验证名字和身份证，活体在APP端验证，结果保存在阿里云
     * @param $params
     * @return array
     */
    private function verifyToken($params){
        $result = (new IdVerifyService())->DescribeVerifyToken([
            'Name'=>$params['real_name'],
            'IdCardNumber'=>$params['number'],
        ]);
        // 包含BizId - 在Result中调用
        // 包含token - 客户端使用
        return $result;
    }

    /**
     * @param $params
     */
    private function verifyResult($params){
        $result = (new IdVerifyService())->DescribeVerifyResult([
            'BizId' => $params['BizId']
        ]);
        if (is_string($result)) {
            echo 'err is not nil: '.$result;
        }
        echo 'success';
    }
}