<?php


namespace App\Service;


class AudioRoomService extends YunXinService
{
    protected $baseUrl = "https://roomserver-dev.netease.im/v1/api/rooms/";

    protected function getUrl($action)
    {
        return $this->url[$action] = $this->baseUrl . $action;
    }

    public function getMembers($id,$data=[]){
        return $this->getData($id.'/members','json');
    }

    public function getStatus($id){
        return $this->getData($id,'json');
    }
}