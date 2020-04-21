<?php


namespace App\Storage;


use EasySwoole\RedisPool\Redis;

class StorageClient
{
    static function getStorage($storage='redis',$name='master'){
        switch ($storage) {
            case 'redis':
                return Redis::defer($name);
        }
        return null;
    }
}