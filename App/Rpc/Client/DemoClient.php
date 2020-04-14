<?php


namespace App\Rpc\Client;

/**
 * Class Config
 * @package App\Client\RPC
 * @method hello() @return string
 */
class DemoClient extends Base
{
    protected $uri = '/demo/';
}