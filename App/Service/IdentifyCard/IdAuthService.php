<?php


namespace App\Service;


use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use App\Service\IdentifyCard\IdentifyCard;
use EasySwoole\Component\CoroutineSingleTon;

class IdAuthService
{
    use CoroutineSingleTon;

    use IdentifyCard;

    protected $host = 'cloudauth.aliyuncs.com';

    protected $method = 'POST';

    protected $regionId = 'cn-hangzhou';

    protected $version = '2019-03-07';

    protected $product = '等Ta实人认证';

    protected $bizType = 'dengta';

    protected $describeVerifyResult = [
        -1 => '未认证：客户端未成功提交',
        1  => '认证通过',
        2  => '认证未通过：实名校验不通过',
        3  => '认证未通过：身份证照片模糊、光线问题造成字体无法识别',
        4  => '认证未通过：身份证照片模糊、光线问题造成字体无法识别',
        5  => '认证未通过：身份证照片有效期已过期（或即将过期）',
        6  => '认证未通过：人脸与身份证头像不一致等可能',
        7  => '认证未通过：人脸与公安网照片不一致等可能',
        8  => '认证未通过：提交的身份证照片非身份证原照片未提交有效身份证照片',
        9  => '认证未通过：非账户本人操作等可能',
        10  => '认证未通过：非同人操作等可能',
        11  => '认证未通过：公安网照片缺失公安网照片格式错误公安网照片未找到人脸',
        12  => '认证未通过：公安网系统异常，无法比对等可能',
    ];

    public function __construct()
    {
        $access = config('AliVerify');
        AlibabaCloud::accessKeyClient($access['accessKeyId'], $access['accessSecret'])
            ->regionId($this->regionId)
            ->asDefaultClient();
    }

    public function DescribeVerifyToken(){
        $data = [
            'BizId' => $this->guid(),
        ];
        return $this->verify('DescribeVerifyToken',$data);
    }

    public function DescribeVerifyResult($data){
        $data = [
            'BizId' => $data['BizId'],
        ];
        return $this->verify('DescribeVerifyResult',$data);
    }

    protected function verify($action,$data){
        try {
            $params = array_merge([
                'RegionId' => $this->regionId,
                'BizType' => $this->bizType,
            ],$data);

            $result = AlibabaCloud::rpc()
                ->product($this->product)
                // ->scheme('https') // https | http
                ->version($this->version)
                ->action($action)
                ->method($this->method)
                ->host($this->host)
                ->options([
                    'query' => $params,
                ])
                ->request();
            $result = $result->toArray();
            if (!$this->checkResult($result)) {
                return $result['errMsg'];
            }
            return array_merge($data,$result);
        } catch (ClientException $e) {
            throw new \Exception('ClientError:'.$e->getErrorMessage());
        } catch (ServerException $e) {
            throw new \Exception('ServerError:'.$e->getErrorMessage());
        }
    }

    public function checkResult(&$result){
        if (isset($result['VerifyStatus']) && $result['VerifyStatus'] != 1) {
            $result['errMsg'] = $this->describeVerifyResult[$result['VerifyStatus']];
            return false;
        }
        return true;
    }

    private function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid =
//                chr(123) .// "{"
                substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
//                .chr(125)// "}"
            ;
            return $uuid;
        }
    }

}