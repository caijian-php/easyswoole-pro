<?php


namespace App\Service\Recommend;


use App\Service\StorageService;

/**
 * 实现随机推荐不重复内容
 *
 * Class Rand
 * @package App\Service\Recommend
 */
class Rand implements RecommendInterface
{
    public function add($key){
        return StorageService::getStorage()->sAdd(\App\Constants\Recommend\Rand::REPOSITORY, getMicroTime(),$key);
    }

    public function del($repository,...$key){
        return StorageService::getStorage()->sAdd(\App\Constants\Recommend\Rand::DEL.$repository, getMicroTime(), ...$key);
    }

    public function get($repository,$limit=5){
        try{
            StorageService::getStorage()->multi();
            $getRepository = \App\Constants\Recommend\Rand::GET.$repository;
            StorageService::getStorage()->sDiffStore($getRepository,\App\Constants\Recommend\Rand::REPOSITORY,\App\Constants\Recommend\Rand::DEL.$repository);
            $list = StorageService::getStorage()->sRandMember($getRepository,$limit);
            $this->del($repository,$list);
            StorageService::getStorage()->exec();
        }catch (\Throwable $e){
            return false;
        }
        return $list;
    }

    // select all for add 初始化一个全集合
    public function init(){

    }
}