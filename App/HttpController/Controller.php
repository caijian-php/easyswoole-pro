<?php
/**
 * Created by PhpStorm.
 * User: lxh
 * Date: 2020/3/8
 * Time: 16:07
 */

namespace App\HttpController;


use App\Traits\Params;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\WorkStartEvent;

abstract class Controller extends \EasySwoole\Http\AbstractInterface\Controller
{
    use Params;

    /**
     * @var Logger
     */
    protected $log;

    public function index()
    {
        // TODO: Implement index() method.
    }

    /**
     * 获取参数|可选择默认值|可选择过滤方式
     * @param $name
     * @param string $default
     * @param string $filter
     * @return mixed
     */
    protected function getParams($name,$default='',$filter=''){
        $data = $this->request()->getRequestParam();
        return $this->input($data, $name, $default, $filter);
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