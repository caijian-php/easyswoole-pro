<?php


namespace App\HttpController;


class ApiBase extends Controller
{
    protected function onRequest(?string $action): ?bool
    {
        $uri = $this->request()->getUri()->getPath();
        return true;
    }
}