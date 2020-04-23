<?php

namespace App\Service;

use EasySwoole\EasySwoole\Logger;
use Swoole\Coroutine as co;

class YunXinService
{
    private $AppKey;                //开发者平台分配的AppKey
    private $AppSecret;             //开发者平台分配的AppSecret,可刷新
    private $Nonce;					//随机数（最大长度128个字符）
    private $CurTime;             	//当前UTC时间戳，从1970年1月1日0点0 分0 秒开始到现在的秒数(String)
    private $CheckSum;				//SHA1(AppSecret + Nonce + CurTime),三个参数拼接的字符串，进行SHA1哈希计算，转化成16进制字符(String，小写)
    protected $RequestType = 'curl';
    const   HEX_DIGITS = "0123456789abcdef";
    public $errorCode;
    public $errorMsg;
    protected $log;
    protected $baseUrl = "";
    protected $url;
    protected static $curl = [];
    protected $ignoreError = false;
    private $lastErrorCode;
    private $normalCode = [200,416,611,806];

    /**
     * 参数初始化
     * @param $AppKey
     * @param $AppSecret
     */
    public function __construct($AppKey = '',$AppSecret = ''){

        // 加载系统配置
        $config = config('IM');
        // 日志
        $this->log = Logger::getInstance();

        $this->AppKey    = $AppKey ?: $config['AppKey'];
        $this->AppSecret = $AppSecret ?: $config['AppSecret'];
    }

    protected  function getUrl($action){
        if (isset($this->url[$action])) {
            return $this->url[$action];
        }
        return $this->url[$action] = $this->baseUrl . $action . '.action';
    }

    /**
     * 通用方法
     * @param $action
     * @param $data
     * @param $type
     * @return array|bool
     */
    public function postData($action, $data, $type='urlencode'){
        $url = $this->getUrl($action);
        $result = $this->postDataCurl($url,$data,$type);
        if (!$this->checkResult($result)) {
            return false;
        }
        return $result;
    }

    public function getData($action, $type='urlencode'){
        $url = $this->getUrl($action);
        $result = $this->postDataCurl($url,$data=[],$type);
        if (!$this->checkResult($result)) {
            return false;
        }
        return $result;
    }

    /**
     * @param $result
     * @return bool
     */
    public function checkResult(&$result)
    {
        if ($result=== false || (isset($result['code']) && !in_array($result['code'],$this->normalCode))) {
            return false;
        }
        return $result;
    }

    public function ignoreError(bool $ignore)
    {
        $this->ignoreError = $ignore;
    }

    /**=============================分割线==================================***/
    /**以下是demo**/
    /**
     * API checksum校验生成
     * @param  void
     * @return $CheckSum(对象私有属性)
     */
    public function checkSumBuilder(){
        //此部分生成随机字符串
        $hex_digits = self::HEX_DIGITS;
        $this->Nonce;
        for($i=0;$i<128;$i++){			//随机字符串最大128个字符，也可以小于该数
            $this->Nonce.= $hex_digits[rand(0,15)];
        }
        $this->CurTime = (string)(time());	//当前时间戳，以秒为单位

        $join_string = $this->AppSecret.$this->Nonce.$this->CurTime;
        $this->CheckSum = sha1($join_string);
        //print_r($this->CheckSum);
    }

    /**
     * 将json字符串转化成php数组
     * @param  $json_str
     * @return $json_arr
     */
    public function json_to_array($json_str){
        if(is_array($json_str) || is_object($json_str)){;
        }else if(is_null(json_decode($json_str))){;
        }else{
            $json_str =  strval($json_str);
            $json_str = json_decode($json_str,true);
        }
        $json_arr=array();
        foreach($json_str as $k=>$w){
            if(is_object($w)){
                $json_arr[$k]= $this->json_to_array($w); //判断类型是不是object
            }else if(is_array($w)){
                $json_arr[$k]= $this->json_to_array($w);
            }else{
                $json_arr[$k]= $w;
            }
        }

        return $json_arr;
    }

    /**
     * 使用CURL方式发送post请求
     * @param  $url     [请求地址]
     * @param  $data    [array格式数据]
     * @return $请求返回结果(array)
     */
    public function postDataCurl($url,$data, $type='urlencoded'){
        //初始化
        $this->errorCode = null;
        $this->errorMsg = null;
        $this->lastErrorCode = null;
        $this->checkSumBuilder();       //发送请求前需先生成checkSum
        // header
        $http_header = array(
            'AppKey:'.$this->AppKey,
            'Nonce:'.$this->Nonce,
            'CurTime:'.$this->CurTime,
            'CheckSum:'.$this->CheckSum,
        );
        if ($type=='json') {
            $http_header[] = 'Content-Type:application/json;charset=utf-8';
            $postdata = !empty($data) ? json_encode($data) : [];
        }else{
            $http_header[] = 'Content-Type:application/x-www-form-urlencoded;charset=utf-8';
            $postdataArray = array();
            foreach ($data as $key=>$value){
                if (is_array($value)) {
                    $value=json_encode(array_values($value));
                }
                array_push($postdataArray, $key.'='.urlencode($value));
            }
            $postdata = !empty($data) ? join('&', $postdataArray) : [];
        }

        // do request
        $times = 3;
        do{
            $result =  $this->curl($url,$http_header,$postdata);
        }while(!$result && --$times);
        $runTime = microtime(true) - getContext('time');
        if($result == null){
            $this->log->error(var_export([$url,['runTime'=>$runTime,'checkSum'=> $this->CheckSum],'[IM][code]'.$this->lastErrorCode],true));
            return false;
        }
        $result =  $this->json_to_array($result);

        // log
        $result['run_time'] = $runTime;
        $result['check_sum'] = $this->CheckSum;
        debug(function()use($url,$data,$result){
            $this->log->info(var_export([$url,$data,$result],true));
        });

        // error
        if (isset($result['code']) && !in_array($result['code'],$this->normalCode) || $runTime > 5) {
            $this->errorMsg = $result['desc'] ?? '';
            // 记录错误日志
            $this->ignoreError OR $this->log->error(var_export([$url,$data,$result],true));
        }

        $this->errorCode = isset($result['code']) ? $result['code'] : 0;

        return $result;
    }

    private function curl($url,$http_header,$postData)
    {
        $timout = 5;
        $cid = getCid();
        if($cid > -1){
            setContext('time',microtime(true));
            $rs = coRequest($url,$postData,$timout,$http_header);
            $this->lastErrorCode = $rs['errorCode'];
            $result = $rs['body'];
            return $result;
        }

        // curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //处理http证书问题
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timout);
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt ($ch, CURLOPT_HTTPHEADER,$http_header);
        setContext('time',microtime(true));
        $result = curl_exec($ch);
        $this->lastErrorCode = curl_errno($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * 使用FSOCKOPEN方式发送post请求
     * @param  $url     [请求地址]
     * @param  $data    [array格式数据]
     * @return $请求返回结果(array)
     */
    public function postDataFsockopen($url,$data){
        $this->checkSumBuilder();       //发送请求前需先生成checkSum

        // $postdata = '';
        $postdataArray = array();
        foreach ($data as $key=>$value){
            array_push($postdataArray, $key.'='.urlencode($value));
            // $postdata.= ($key.'='.urlencode($value).'&');
        }
        $postdata = join('&', $postdataArray);
        // building POST-request:
        $URL_Info=parse_url($url);
        if(!isset($URL_Info["port"])){
            $URL_Info["port"]=80;
        }
        $request = '';
        $request.="POST ".$URL_Info["path"]." HTTP/1.1\r\n";
        $request.="Host:".$URL_Info["host"]."\r\n";
        $request.="Content-type: application/x-www-form-urlencoded;charset=utf-8\r\n";
        $request.="Content-length: ".strlen($postdata)."\r\n";
        $request.="Connection: close\r\n";
        $request.="AppKey: ".$this->AppKey."\r\n";
        $request.="Nonce: ".$this->Nonce."\r\n";
        $request.="CurTime: ".$this->CurTime."\r\n";
        $request.="CheckSum: ".$this->CheckSum."\r\n";
        $request.="\r\n";
        $request.=$postdata."\r\n";

        // print_r($request);
        $fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
        fputs($fp, $request);
        $result = '';
        while(!feof($fp)) {
            $result .= fgets($fp, 128);
        }
        fclose($fp);

        $str_s = strpos($result,'{');
        $str_e = strrpos($result,'}');
        $str = substr($result, $str_s,$str_e-$str_s+1);
        return $this->json_to_array($str);
    }

    /**
     * 使用FSOCKOPEN方式发送post请求（json）
     * @param  $url     [请求地址]
     * @param  $data    [array格式数据]
     * @return $请求返回结果(array)
     */
    public function postJsonDataFsockopen($url, $data){
        $this->checkSumBuilder();       //发送请求前需先生成checkSum

        $postdata = json_encode($data);

        // building POST-request:
        $URL_Info=parse_url($url);
        if(!isset($URL_Info["port"])){
            $URL_Info["port"]=80;
        }
        $request = '';
        $request.="POST ".$URL_Info["path"]." HTTP/1.1\r\n";
        $request.="Host:".$URL_Info["host"]."\r\n";
        $request.="Content-type: application/json;charset=utf-8\r\n";
        $request.="Content-length: ".strlen($postdata)."\r\n";
        $request.="Connection: close\r\n";
        $request.="AppKey: ".$this->AppKey."\r\n";
        $request.="Nonce: ".$this->Nonce."\r\n";
        $request.="CurTime: ".$this->CurTime."\r\n";
        $request.="CheckSum: ".$this->CheckSum."\r\n";
        $request.="\r\n";
        $request.=$postdata."\r\n";

        print_r($request);
        $fp = fsockopen($URL_Info["host"],$URL_Info["port"]);
        fputs($fp, $request);
        $result = '';
        while(!feof($fp)) {
            $result .= fgets($fp, 128);
        }
        fclose($fp);

        $str_s = strpos($result,'{');
        $str_e = strrpos($result,'}');
        $str = substr($result, $str_s,$str_e-$str_s+1);
        return $this->json_to_array($str);
    }


}
