<?php

namespace App\Service\Auth;

Interface AuthInterface
{
    public function set($id);

    public function parse($token);
}