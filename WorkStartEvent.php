<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Swoole\Coroutine as co;
use Symfony\Component\Finder\Finder;

class WorkStartEvent
{
    public static function run(){
        Logger::getInstance()->console(posix_getpid().'启动于'.date('Y-m-d H:i:s'));
    }

    // http服务器刚刚监听到客户端请求
    public static function onRequest(Request $request, Response $response): bool
    {
        setContext('runTime',microtime(true));

        $raw = $request->getSwooleRequest()->rawContent();
        $data = (array)json_decode($raw,true);
        $data AND $request->withParsedBody($data);

        $ip = $request->getHeaders()['x-real-ip'][0] ?? '';
        !($ip && $ip != '127.0.0.1') AND $ip = $request->getServerParams()['remote_addr'];
        $request->withAddedHeader('remote_ip',$ip);

        $uri = $request->getUri()->getPath();
        setContext('request',[$uri,$request->getRequestParam()]);
        debug(function()use($uri,$request){
            !in_array($uri,['/','/user/appAuth/login','/push/']) && Logger::getInstance()->info($uri);
        });

        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        $runTime = microtime(true) -  getContext('runTime');
        $runTime > 10 && toLog([
            'too-slow',
            'runtime' => $runTime,
            'request' => getContext('request')
        ],'request');
    }
}