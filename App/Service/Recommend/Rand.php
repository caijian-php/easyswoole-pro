<?php


namespace App\Service\Recommend;


use App\Storage\StorageClient;

/**
 * 实现随机推荐不重复内容
 *
 * Class Rand
 * @package App\Service\Recommend
 */
class Rand implements RecommendInterface
{
    private $all = \App\Constants\Recommend\Rand::REPOSITORY;

    private $get = \App\Constants\Recommend\Rand::GET;

    private $del = \App\Constants\Recommend\Rand::DEL;

    public function add($key){
        return StorageClient::getStorage()->sAdd($this->all, ...$key);
    }

    public function del($id, ...$key){
        return StorageClient::getStorage()->sAdd($this->del.$id, ...$key);
    }

    public function get($id,$limit=5,$time=1){
        $tryTime = 0;
        begin:
        try{
            if ($tryTime > $time) {
                throw new \Exception('总仓库为空');
            }
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->sDiffStore($this->get . $id, $this->all, $this->del . $id);
            $list = StorageClient::getStorage()->sRandMember($this->get . $id, $limit);
            if (empty($list)) {
                $this->flushDel($id);
                $tryTime++;
                goto begin;
            }
            $this->del($id,$list);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return $list;
    }

    public function init($list){
        $keys = array_column($list,'id');
        return $this->add($keys);
    }

    public function flushDel($id){
        return StorageClient::getStorage()->del($this->del.$id);
    }
}