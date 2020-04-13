<?php
/**
 * Created by PhpStorm.
 * User: lxh
 * Date: 2020/3/8
 * Time: 16:07
 */

namespace App\HttpController;


use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\WorkStartEvent;

abstract class Base extends \EasySwoole\Http\AbstractInterface\Controller
{
    /**
     * @var Logger
     */
    protected $log;

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

    protected function onException(\Throwable $throwable): void
    {
        $message = "\n[params]".json_encode($this->request()->getRequestParam(),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $headers = [
            'host' => $this->request()->getHeader('remote_ip')[0],
            'path' => $this->request()->getUri()->getPath(),
            'method' => $this->request()->getMethod(),
        ];
        $message .= "\n[headers]".json_encode($headers,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $message .= "\n[error]".$throwable;
        Logger::getInstance()->error($message);
    }
}