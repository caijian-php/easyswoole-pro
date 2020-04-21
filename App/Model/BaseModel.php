<?php


namespace App\Model;


use EasySwoole\ORM\AbstractModel;

class BaseModel extends AbstractModel
{
    protected $connectionName = 'master';

    /**
     * @param int $page
     * @param int $limit
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function pagination(int $page=1,int $limit=10):array
    {
        $list = $this->page($page,$limit)->all();
        $count = $this->lastQueryResult()->getTotalCount();
        return [
            'page'=>$page,
            'limit'=>$limit,
            'count'=>$count,
            'list'=>$list,
        ];
    }
}