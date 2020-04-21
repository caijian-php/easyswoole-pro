<?php


namespace App\Service\Recommend;


use App\Storage\StorageClient;

/**
 * 顺序推荐
 *
 * Class Order
 * @package App\Service\Recommend
 */
class Order implements RecommendInterface
{
    public function add($repository,$fields=[])
    {
        try{
            if (empty($fields)) {
                throw new \Exception('fields不能为空');
            }
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->hmset(\App\Constants\Recommend\Order::REPOSITORY.$repository,$fields);
            StorageClient::getStorage()->zAdd(\App\Constants\Recommend\Order::REPOSITORY_TIMELINE,getMicroTime(),$repository);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return true;
    }

    public function get($repository, $limit,$page=1)
    {
        $start = ($page-1)*$limit;
        $stop = $page*$limit;
        return StorageClient::getStorage()->zRevRange(\App\Constants\Recommend\Order::REPOSITORY.$repository,$start,$stop);
    }

    public function del($repository)
    {
        try{
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->del(\App\Constants\Recommend\Order::REPOSITORY.$repository);
            StorageClient::getStorage()->zRem(\App\Constants\Recommend\Order::REPOSITORY_TIMELINE, $repository);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return true;
    }
}