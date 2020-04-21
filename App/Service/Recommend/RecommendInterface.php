<?php


namespace App\Service\Recommend;


Interface RecommendInterface
{
    public function add($repository);

    public function del($repository,...$key);

    public function get($repository,$limit);
}