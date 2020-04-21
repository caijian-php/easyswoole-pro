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
    private $repository = \App\Constants\Recommend\Order::REPOSITORY;

    private $timeline = \App\Constants\Recommend\Order::REPOSITORY_TIMELINE;

    public function add($id,$fields=[])
    {
        try{
            if (empty($fields)) {
                throw new \Exception('fields不能为空');
            }
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->hmset($this->repository.$id, $fields);
            StorageClient::getStorage()->zAdd($this->timeline, getMicroTime(), $id);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return true;
    }

    public function get($id, $limit,$page=1)
    {
        $start = ($page-1)*$limit;
        $stop = $page*$limit;
        return StorageClient::getStorage()->zRevRange($this->repository.$id,$start,$stop);
    }

    public function del($id)
    {
        try{
            StorageClient::getStorage()->multi();
            StorageClient::getStorage()->del($this->repository.$id);
            StorageClient::getStorage()->zRem($this->timeline, $id);
            StorageClient::getStorage()->exec();
        }catch (\Throwable $e){
            // 自行处理异常
            return false;
        }
        return true;
    }
}