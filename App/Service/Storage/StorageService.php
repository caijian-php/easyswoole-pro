<?php


namespace App\Service;


use EasySwoole\RedisPool\Redis;

class StorageService
{
    static function getStorage($storage='redis',$name='master'){
        switch ($storage) {
            case 'redis':
                return Redis::defer($name);
        }
        return null;
    }
}