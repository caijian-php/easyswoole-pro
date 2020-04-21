<?php


namespace App\Service\Recommend;


Interface RecommendInterface
{
    public function add($repository);

    public function get($repository,$limit);

    public function del($repository);

}