<?php
/**
 * Created by PhpStorm.
 * User: lxh
 * Date: 2020/3/8
 * Time: 16:07
 */

namespace App\HttpController;


use EasySwoole\EasySwoole\WorkStartEvent;

abstract class Controller extends \EasySwoole\Http\AbstractInterface\Controller
{

    public function index()
    {
        // TODO: Implement index() method.
    }

    protected function onRequest(?string $action): ?bool
    {
        WorkStartEvent::onRequest($this->request(),$this->response());
        return true;
    }

    protected function afterAction(?string $actionName): void
    {
        WorkStartEvent::afterRequest($this->request(),$this->response());
    }
}