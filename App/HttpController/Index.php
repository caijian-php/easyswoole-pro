<?php


namespace App\HttpController;


class Index extends ApiBase
{

    function index()
    {
        $this->hello();
    }

    protected function hello()
    {
        $this->response()->withHeader('Access-Control-Allow-Origin','*');
        $this->response()->withHeader('Content-Security-Policy','upgrade-insecure-requests');
        $this->response()->write('welcome to '.config('SERVER_NAME'));
    }
}