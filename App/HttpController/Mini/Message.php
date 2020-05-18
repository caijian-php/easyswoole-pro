<?php


namespace App\HttpController\Mini;


use App\HttpController\ApiBase;
use App\Service\WXBizMsg\WXBizMsgService;

class Message extends ApiBase
{
    /**
     * 小程序登录->开发->开发配置->消息配置->启用
     * 微信小程序客服消息地址
     */
    public function sign(){
        $params = $this->request()->getRequestParam();
        if ($this->checkSignature()){
            if (isset($params["echostr"])) {
                return $this->response()->write($params["echostr"]);
            }

            $postStr = $this->request()->getSwooleRequest()->rawContent();
            $wx = config('WeChatMini');
            $msgService = new WXBizMsgService($wx['appId'],'JSKJ000hijklmnopqrstuvwxyz0123456789000JSKJ','JSKJ1234');
            $msg_sign = $params['msg_signature'];
            $timeStamp = $params['timestamp'];
            $nonce = $params['nonce'];
            $msg = $msgService->getMsg($postStr,$msg_sign,$timeStamp,$nonce);
            $arr = $this->xmlToArr($msg);
            \EasySwoole\EasySwoole\Logger::getInstance()->console(var_export($arr,true));

            $mini = config('WeChatMini');
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$mini['appId']}&secret={$mini['appSecret']}";
            $res = coRequest($url);

            if (!isset($res['body'])) {
                return $this->response()->write('请求access_token失败');
            }
            $body = deJson($res['body']);
            $ak = $body['access_token'];
            $url="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$ak}";
            $responseArr = $this->responseArr($arr,$params['openid'],$ak);
            if (!empty($responseArr)) {
                $res = coRequest($url,enJson($responseArr));
                \EasySwoole\EasySwoole\Logger::getInstance()->console(var_export($responseArr,true));
                \EasySwoole\EasySwoole\Logger::getInstance()->console(var_export($res,true));
            }

            return $this->response()->write( 'success');
        }
        return $this->response()->write('no echostr');
    }

    private function responseArr($arr,$toUser,$ak){
        if (!isset($arr['Content'])) {
            return [];
        }
        switch ($arr['Content']){
            case 1:
                $responseArr = [
                    'access_token' => $ak,
                    'touser' => $toUser,
                    'msgtype' => 'link',
                    'link' => [
                        'title' => '下载等Ta完整版，体验更多互动功能',
                        'description' => '红娘月老牵线搭桥，海量帅哥美女在线视频相亲，一起来相亲吧',
                        'url' => 'http://down.51dengta.net',
                        'thumb_url' => 'http://dengta001.oss-cn-shenzhen.aliyuncs.com/upload/imgs/pro_img/e07f016a8d4224e11f1c492133bff9b7.png',
                    ],
                ];
                break;
            default:
                $responseArr = [];
        }

        return $responseArr;
    }

    private function xmlToArr($str){
        $obj = simplexml_load_string($str,"SimpleXMLElement", LIBXML_NOCDATA);
        $arr = json_decode(json_encode($obj),true);

        return $arr;
    }

    private function checkSignature()
    {
        $params = $this->request()->getRequestParam();
        $signature = $params["signature"];
        $timestamp = $params["timestamp"];
        $nonce = $params["nonce"];

        $token = 'JS'; //
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature ) {
            return true;
        } else {
            return false;
        }
    }
}