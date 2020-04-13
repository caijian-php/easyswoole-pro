<?php

use EasySwoole\Jwt\Jwt;

class JwtService implements AuthInterface
{
    protected $msg;

    protected $key = 'pro';

    protected $encryptionMode = 'HMACSHA256';

    protected $expireTime = 3600;

    public function set($id,$expireTime=0)
    {
        $expireTime = $expireTime ?? $this->expireTime;
        $jwtObject = Jwt::getInstance()
            ->setSecretKey($this->key) // 秘钥
            ->publish();

        $jwtObject->setAlg($this->encryptionMode); // 加密方式
        $jwtObject->setExp(time()+$expireTime); // 过期时间
        $jwtObject->setJti(md5($id.'-'.time())); // jwt id 用于标识该jwt
        $jwtObject->setData($id);

        return $jwtObject->__toString();
    }

    public function parse($token)
    {
        try {
            $jwtObject = Jwt::getInstance()->setSecretKey($this->key);
            $result = $jwtObject->decode($token);
            $status = $result->getStatus();
            switch ($status)
            {
                case  1:
                    return $result->getData();
                case  -1:
                    $this->msg = 'token无效';
                    return false;
                case  -2:
                    $this->msg = 'token过期';
                    return false;
            }
        } catch (\EasySwoole\Jwt\Exception $e) {
            $this->msg = 'token非法';
            return false;
        }
    }

    public function getMsg(){
        return $this->msg;
    }
}