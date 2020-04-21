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
    public function add($key){
        return StorageClient::getStorage()->sAdd(\App\Constants\Recommend\Rand::REPOSITORY, getMicroTime(),$key);
    }

    public function del($repository,...$key){
        return StorageClient::getStorage()->sAdd(\App\Constants\Recommend\Rand::DEL.$repository, getMicroTime(), ...$key);
    }

    public function get($repository,$limit=5){
        try{
            StorageClient::getStorage()->multi();
            $getRepository = \App\Constants\Recommend\Rand::GET.$repository;
            StorageClient::getStorage()->sDiffStore($getRepository,\App\Constants\Recommend\Rand::REPOSITORY,\App\Constants\Recommend\Rand::DEL.$repository);
            $list = StorageClient::getStorage()->sRandMember($getRepository,$limit);
            $this->del($repository,$list);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            return false;
        }
        return $list;
    }

    // select all for add 初始化一个全集合
    public function init(){

    }
}