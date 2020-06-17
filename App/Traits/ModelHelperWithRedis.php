<?php


namespace App\Traits;


use App\Storage\StorageClient;

Trait ModelHelperWithRedis
{
    protected function getRedisConfig($name){
        $redisConfig = [
            'list' => [
                'key' => $this->tableName . '-list',
                'expire' => 86400, // 有效期一天，适用于登录签到活动，改完登录签到，明天生效
            ],
        ];
        return $redisConfig[$name];
    }

    /*
     * 每日生效
     */
    public function getTodayData($name='list'){
        $config = $this->getRedisConfig($name);
        $key = $config['key'];
        $expire = $config['expire'];
        $redis = StorageClient::getStorage();
        $date = date("Y-m-d");
        $list = $redis->get($key.$date);
        if ($list) {
            return json_de($list);
        }
        $list = $this->getList() ?? [];
        $redis->set($key.$date, json_en($list));
        $redis->expire($key.$date,$expire);

        return $list;
    }

    protected function getList(){
        throw new \Exception("please implement it.");
    }
}