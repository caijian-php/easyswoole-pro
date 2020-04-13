<?php


namespace App\HttpController;


use EasySwoole\Http\Message\Status;
use EasySwoole\Validate\Validate;

class ApiBase extends Base
{
    protected $notAuth = [
        '/'
    ];

    protected function onRequest(?string $action): ?bool
    {
        parent::onRequest($action);
        $uri = $this->request()->getUri()->getPath();

        if (method_exists($this, 'validateRule')) {
            /** @var Validate $v */
            $v = $this->validateRule($action);
            if ($v) {
                $ret = $this->validate($v);
                if ($ret == false) {
                    $alias = $v->getError()->getFieldAlias();
                    $msg = $alias.str_replace($alias,'',$v->getError()->getErrorRuleMsg());
                    return $this->error($msg, Status::CODE_BAD_REQUEST);
                }
            }
        }

        if(in_array($uri,$this->notAuth)){
            return true;
        }




        return true;
    }

    /**
     * @param $info
     * @param int $statusCode
     * @param string $msg
     * @return bool
     */
    protected function success($info = [],$statusCode = 0,$msg = '')
    {
        $data = [
            "result"   => true,
            "code"     => $statusCode,
            "info"     => $info ?: (new \ArrayObject()),
            "msg"      => $msg,
            "run_time"      => microtime(true) - getContext('runTime')
        ];

        $this->response()->write(json_en($data));
        $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response()->withHeader('Access-Control-Allow-Origin','*');
        $this->response()->withStatus(Status::CODE_OK);

        return true;
    }

    /**
     * @param $msg
     * @param int $statusCode
     * @param array $info
     * @param int $httpCode
     * @return bool
     */
    protected function error($msg,$statusCode = 400, $info = [], $httpCode = 200)
    {
        if(is_array($msg)){
            $statusCode = $msg['code'];
            $msg = $msg['msg'];
        }

        $data = [
            "result" => false,
            "code" => $statusCode,
            "info" => $info ?: (new \ArrayObject()),
            "msg" => $msg,
            "run_time"      => microtime(true) - getContext('runTime')
        ];

        $this->response()->write(json_en($data));
        $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        $this->response()->withHeader('Access-Control-Allow-Origin','*');
        $this->response()->withStatus($httpCode);

        return false;
    }

    /**
     * @param string|null $action
     */
    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(Status::CODE_NOT_FOUND);
        $file = EASYSWOOLE_ROOT.'/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';
        if(!is_file($file)){
            $file = EASYSWOOLE_ROOT.'/src/Resource/Http/404.html';
        }
        $this->response()->write(file_get_contents($file));
    }

    /**
     * @param \Throwable $throwable
     * @throws \Throwable
     */
    protected function onException(\Throwable $throwable): void
    {
        parent::onException($throwable);
        $this->error('服务器错误',Status::CODE_INTERNAL_SERVER_ERROR,[],Status::CODE_INTERNAL_SERVER_ERROR);
    }
}