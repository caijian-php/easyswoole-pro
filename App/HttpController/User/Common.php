<?php


namespace App\HttpController\User;


use App\HttpController\ApiBase;

class Common extends ApiBase
{
    protected $notAuth=[
        '/user/common/info'
    ];

    /**
     * 用户注册信息
     */
    public function info(){

    }
}