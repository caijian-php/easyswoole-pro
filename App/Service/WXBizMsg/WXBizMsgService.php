<?php


namespace App\Service\WXBizMsg;


use DOMDocument;
use WXBizMsgCrypt;

class WXBizMsgService
{
    protected $token;
    protected $encodingAesKey;
    protected $appId;
    protected $text;

    protected $errCode;
    protected $errMsg;

    public function __construct($appid,$encodingAesKey,$token)
    {
        include_once "wxBizMsgCrypt.php";
        $this->appId = $appid;
        $this->encodingAesKey = $encodingAesKey;
        $this->token = $token;
    }

    public function getMsg($from_xml,$msg_sign,$timeStamp,$nonce){
        $pc = new WXBizMsgCrypt($this->token, $this->encodingAesKey, $this->appId);

        $msg = '';
        $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
        if ($errCode == 0) {
            return $msg;
        } else {
            $this->errCode = 500;
            $this->errMsg = '解密失败';
            return 'fail';
        }
    }
}