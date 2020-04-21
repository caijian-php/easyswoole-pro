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
        return StorageClient::getStorage()->sAdd($this->all, getMicroTime(),$key);
    }

    public function del($id, ...$key){
        return StorageClient::getStorage()->sAdd($this->del.$id, getMicroTime(), ...$key);
    }

    public function get($repository,$limit=5){
        try{
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->sDiffStore($this->get . $repository, $this->all, $this->del . $repository);
            $list = StorageClient::getStorage()->sRandMember($this->get . $repository, $limit);
            $this->del($repository,$list);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return $list;
    }

    // select all for add 初始化一个全集合
    public function init(){

    }
}