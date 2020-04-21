<?php


namespace App\HttpController\User;


use App\HttpController\ApiBase;
use EasySwoole\Validate\Validate;

class Auth extends ApiBase
{
    protected $notAuth = [
        '/user/auth/smsCode',
        '/user/auth/smsLogin',
    ];

    protected function validateRule(?string $action): ?Validate
    {
        $v = new Validate();
        switch ($action){
            case 'smsCode':
                $v->addColumn('tel','手机号码')->required('必选项')->notEmpty('不能为空')->regex('/^1[3-9]\d{9}$/','手机号码格式有误，重新填写');
                break;
            case 'smsLogin':
                $v->addColumn('tel','手机号码')->required('必选项')->notEmpty('不能为空')->regex('/^1[1|3-9]\d{9}$/','手机号码格式有误，重新填写')->lengthMin(11,'长度11个字符')->lengthMax(11,'长度11个字符');
                $v->addColumn('code','验证码')->required('必选项')->notEmpty('不能为空')->numeric('必须是纯数字')->length(4,'长度4个字符');
                break;
        }
        return $v;
    }

    /**
     * 发送验证码
     */
    public function smsCode()
    {
        return $this->success();
    }

    /**
     * 验证码登录
     */
    public function smsLogin(){
        return $this->success();
    }
}