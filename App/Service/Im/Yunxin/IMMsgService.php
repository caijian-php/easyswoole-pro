<?php


namespace App\Service;


use App\Model\ChatRoomModel;
use App\Model\GiftModel;
use App\Model\UserModel;

class IMMsgService extends YunXinService
{
    protected $baseUrl = 'https://api.netease.im/nimserver/msg/';

    public function officialMsg($uid,$msg){
        $this->postData('sendMsg',[
            'from' => 'date',
            'ope' => 0,
            'to' => $uid,
            'type' => 0,
            'body' => enJson([
                'msg'=>$msg
            ]),
        ]);
    }

}