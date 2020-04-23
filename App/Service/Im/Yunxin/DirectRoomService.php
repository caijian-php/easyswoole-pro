<?php

namespace App\Service;

class DirectRoomService extends YunXinService
{
    protected $baseUrl = "https://vcloud.163.com/app";

    protected  function getUrl($action,$controller=''){
        if (isset($this->url[$action])) {
            return $this->url[$action];
        }
        if (strpos($action,'/') !== false) {
            $arr = explode('/',$action);
            $count = count($arr);
            $action = '';
            for($i=0;$i<$count;$i++){
                $param = array_shift($arr);
                $action .= '/'.$param;
            }
        }else{
            $action = '/channel/'.$action;
        }
        return $this->url[$action] = $this->baseUrl . $action;
    }

}