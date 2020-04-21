<?php


namespace App\Storage;


use EasySwoole\RedisPool\Redis;

class StorageClient
{
    static function getStorage($storage='redis',$name='master'){
        try{
            switch ($storage) {
                case 'redis':
                    return Redis::defer($name);
                case 'els': // ElasticSearch
                    return null;
            }
            throw new \Exception('缺少该类型');
        }catch (\Throwable $e){

        }
    }
}