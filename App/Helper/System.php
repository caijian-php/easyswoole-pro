<?php


use EasySwoole\Component\Context\ContextManager;
use EasySwoole\EasySwoole\Logger;
use Swoole\Coroutine as co;


if (! function_exists('config')) {
    function config($key = '',$fields = '')
    {
        $data = \EasySwoole\EasySwoole\Config::getInstance()->getConf($key);
        if ($fields) {
            $fields = explode(',', $fields);
            $data  = array_filter($data, function ($k) use ($fields) {
                return in_array($k, $fields);
            }, ARRAY_FILTER_USE_KEY);
        }
        return $data;
    }
}

if( ! function_exists('array_all_diff')){
    /*
     * 返回包含所有数组中值不同的元素
     */
    function array_all_diff(array ...$params){
        return array_diff(array_merge_recursive(...$params),array_intersect(...$params));
    }
}

if (! function_exists('setContext')) {
    function setContext($key,$value=[]){
        $error = ContextManager::getInstance()->get('error') ?? [];
        $error[$key] = $value;
        return ContextManager::getInstance()->set('error', $error);
    }
}

if (! function_exists('getContext')) {
    function getContext($key){
        $error = ContextManager::getInstance()->get('error') ?? [];
        return $error[$key] ?? null;
    }
}

if (! function_exists('debug')){
    function debug(callable $callback){
        $config = config('StdoutLoggerInterface');
        if ($config && isset($config['DEBUG']) && $config['DEBUG']) {
            $callback();
        }
    }
}

if (! function_exists('errorLog')) {
    function errorLog(\Throwable $e, array $params, $location){
        Logger::getInstance()->error($location.'@'.$e->getMessage().':'.json_encode($params));
    }
}

if (! function_exists('go')) {
    function go(callable $callable)
    {
        co::create($callable);
    }
}

if (! function_exists('co')) {
    function co(callable $callable)
    {
        co::create($callable);
    }
}

if (! function_exists('defer')) {
    function defer(callable $callable): void
    {
        co::defer($callable);
    }
}

if (! function_exists('getCid')){
    function getCid(){
        return  $cid = co::getCid();
    }
}

if (! function_exists('addTask')) {
    function addTask($call)
    {
        return \EasySwoole\EasySwoole\Task\TaskManager::getInstance()->async($call);
    }
}

if (! function_exists('value')) {
    function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}

if (! function_exists('call')) {
    function call($callback, array $args = [])
    {
        $result = null;
        if ($callback instanceof \Closure) { // 调用闭包
            $result = $callback(...$args);
        } elseif (is_object($callback) || (is_string($callback) && function_exists($callback))) {
            $result = $callback(...$args); // 调用对象  调用方法
        } elseif (is_array($callback)) {
            [$object, $method] = $callback;
            $result = is_object($object) ? $object->{$method}(...$args) : $object::$method(...$args); // 数组形式下 调用对象
        } else {
            $result = call_user_func_array($callback, $args); // 不管那么多的调用
        }
        return $result;
    }
}

if (! function_exists('retry')) {
    function retry($times, callable $callback, $sleep = 0)
    {
        beginning:
        try {
            return $callback();
        } catch (\Throwable $e) {
            if (--$times < 0) {
                throw $e;
            }
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}