<?php

namespace EasySwoole\EasySwoole;

use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use Swoole\Coroutine as co;
use Symfony\Component\Finder\Finder;

class WorkStartEvent
{
    static $notDebug = [
        '/',
        '/push/'
    ];

    public static function run(){
        Logger::getInstance()->console(posix_getpid().'启动于'.date('Y-m-d H:i:s'));
        Config::getInstance()->merge(array_merge(self::loadConfigFile(),self::loadConfigModel()));
        require EASYSWOOLE_ROOT.'/App/Helper/Functions.php';
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        setContext('runTime',microtime(true));

        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(Status::CODE_OK);
            return false;
        }

        $raw = $request->getSwooleRequest()->rawContent();
        $data = (array)json_decode($raw,true);
        $data AND $request->withParsedBody($data);

        $ip = $request->getHeaders()['x-real-ip'][0] ?? '';
        !($ip && $ip != '127.0.0.1') AND $ip = $request->getServerParams()['remote_addr'];
        $request->withAddedHeader('remote_ip',$ip);

        $uri = $request->getUri()->getPath();
        setContext('request',[$uri,$request->getRequestParam()]);
        debug(function()use($uri,$request){
            !in_array($uri,WorkStartEvent::$notDebug) && Logger::getInstance()->info($uri);
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

    static function loadConfigFile():?array
    {
        return [];
    }
    static function loadConfigModel():?array
    {
        return [];
    }
}