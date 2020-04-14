<?php


namespace App\Rpc\Client;


use GuzzleHttp\Exception\RequestException;

class Base
{
    private $rpcHost;
    private $rpcPath = '/Rpc';
    private $rpcUrl;
    private $key = 'pro';
    protected $uri = '/';

    public function __construct()
    {
        $this->rpcHost = 'http://127.0.0.1:'.config('MAIN_SERVER')['PORT'];
        $this->rpcUrl = $this->rpcHost . $this->rpcPath;
    }

    /**
     * @param $name
     * @param $arguments
     * @return string
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        return $this->request($name,$arguments);
    }

    /**
     * @param $method
     * @param $params
     * @return string
     * @throws \Exception
     */
    protected function request($method,$params)
    {
        try {
            $uri = $this->uri.$method;
            $timestamp =  time();
            $client = new \GuzzleHttp\Client();
            $sign = md5(json_encode($params) .$timestamp. $uri . $this->key);
            $response = $client->request('POST',$this->rpcUrl,
                [
                    'json' => $params,
                    'headers' => [
                        'timestamp' =>$timestamp,
                        'uri' => $uri,
                        'sign' => $sign
                    ],
                    'verify' => false
                ]
            );
            $body = $response->getBody()->__toString();
            if($json = json_decode($body,true)){
                return $json['data'];
            }
            return $body;
        } catch (RequestException $e) {
            $code = $e->getCode();
            if($code == '404'){
                if($e->hasResponse()){
                    throw new \Exception(get_called_class().'::'.$method . '方法不存在');
                }
            }else{
                if($e->hasResponse()){
                    throw new \Exception($e->getResponse()->getBody()->__toString());
                }
            }
        }
    }
}