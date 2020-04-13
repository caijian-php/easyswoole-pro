<?php

use EasySwoole\Jwt\Jwt;

Interface AuthInterface
{
    public function set($id);

    public function parse($token);
}