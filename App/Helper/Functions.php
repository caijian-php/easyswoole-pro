<?php

use EasySwoole\EasySwoole\Logger;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use Swoole\Coroutine as co;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

//if (function_exists('dump')) {
//    VarDumper::setHandler(function ($var) {
//        $cloner = new VarCloner();
//        //$cloner->setMaxItems(2);  // 设置一个嵌套级别（past the first nesting level)被克隆的元素的最大值
//        $cloner->setMinDepth(1);  // 在深度上的剥离限制。
//        $cloner->setMaxItems(5);
//        $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();
//        $dumper->dump($cloner->cloneVar($var));
//    });
//}

if (! function_exists('console')) {
    function console($data){
        Logger::getInstance()->console(var_export($data));
    }
}

if (! function_exists('getMicroTime')) {
    function getMicroTime(){
        $tmp = explode(' ',microtime());
        return ($tmp[1] + round($tmp[0],3))*1000;
    }
}

if (! function_exists('formatTime')) {
    function formatTime($startTime, $endTime){
        $diffTime = substr($endTime,0,10)  - substr($startTime,0,10);
        if ($diffTime<3600) {
            $min = floor($diffTime/60);
            $sec = ($diffTime - $min * 60);
            return  $min . '分钟' . $sec . '秒';
        }
        $hour = floor($diffTime/3600);
        $min = floor(($diffTime - $hour*3600)/60);
        return $hour . '小时' . $min . '分钟';
    }
}

if (! function_exists('num_format')) {
    function num_format($num, $decimals=2){
        return bcdiv(bcmul($num,100,$decimals),100,$decimals);
    }
}

if (! function_exists('enJson')) {
    function enJson($data)
    {
        if (!$data){
            return new ArrayObject();
        }
        return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}

if (! function_exists('deJson')) {
    function deJson($data, $ArrOrObejct=0)
    {
        if (!$data){
            noneData:
            switch ($ArrOrObejct){
                case 0:
                    $data = [];
                    break;
                case 1:
                    $data = new ArrayObject();
                    break;
            }
            return $data;
        }
        $data = json_decode($data,true);
        if(!$data) {
            goto noneData;
        }
        return $data;
    }
}

if (! function_exists('filePuts')){
    function filePuts($filename, $data, $flags = null){
        $cid = co::getCid();
        if($cid > -1){
            return co::writeFile($filename, $data, $flags);
        }else{
            return file_put_contents($filename, $data, $flags);
        }
    }
}

if (! function_exists('fileGets')) {
    function fileGets($filename)
    {
        $cid = co::getCid();
        if ($cid > -1) {
            return co::readFile($filename);
        } else {
            return file_get_contents($filename);
        }
    }
}

if (! function_exists('coRequest')) {
    function coRequest($url, $params = [], $timeout = 5, $headers = [])
    {
        $info = parse_url($url);
        if ($info['scheme'] == 'https') {
            $cli = new co\Http\Client($info['host'], 443, true);
        } else {
            $cli = new co\Http\Client($info['host']);
        }
        $cli->set(['timeout' =>$timeout]);

        $h = [];
        foreach ($headers as $key => $value) {
            $tmp        = explode(':', $value);
            if(count($tmp) == 2){
                $h[$tmp[0]] = $tmp[1];
            }else{
                $h[$key] = $value;
            }
        }
        if(empty($h)){
            $h['Content-Type'] = 'application/x-www-form-urlencoded';
        }
        $cli->setHeaders($h);
        $path = $info['path'] . (empty($info['query']) ? '' : '?' . $info['query']);
        if ($params) {
            $cli->post($path, $params);
        } else {
            $cli->get($path);
        }
        $result = [
            'code'      => $cli->getStatusCode(),
            'errorCode' => swoole_strerror($cli->errCode),
            'body'      => $cli->getBody(),
        ];
        $cli->close();

        return $result;
    }
}

if (! function_exists('toLog')) {
    function toLog($info,$fileName = '')
    {
        $fileName or $fileName = 'debug';
        $dir = config('LOG_DIR').'/debug/';
        is_dir($dir) OR mkdir($dir);
        $fileName = $dir.addslashes($fileName).'.log';
        return filePuts($fileName,"[".date('Y-m-d H:i:s')."]".PHP_EOL.var_export($info,true).PHP_EOL.PHP_EOL,FILE_APPEND);
    }
}

if (! function_exists('getRealDay')) {
    function getRealDay($y,$m,$d){
        if($y%4==0 && $y%100!=0 || $y%400==0 ){
            $special = 29;
        }else{
            $special = 28;
        }
        if($m<8){
            if($m%2 ==0 && $m != 2){
                $day = 30;
            }else if($m == 2){
                $day = $special;
            }else{
                $day = 31;
            }
        }else{
            if($m%2 ==0){
                $day = 31;
            }else{
                $day = 30;
            }
        }
        if($d<=$day){
            $realDay = $d;
        }else{
            $realDay = $day;
        }
        return $realDay;
    }
}

if (!function_exists('rawSql')){
    /**
     * 可以对结果直接getResult或者使用BaseModel的pagination等等
     * @param $sql
     * @param array $params
     * @param string $connectionName
     * @return \EasySwoole\ORM\Db\Result
     * @throws Throwable
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    function rawSql($sql,$params=[],$connectionName='master') {
        $queryBuild = new QueryBuilder();
        $queryBuild->raw($sql, $params);
        return DbManager::getInstance()->query($queryBuild, true, $connectionName);
    }
}

if (! function_exists('randName')) {
    function randName(){
        $tou= [
            '快乐','冷静','醉熏','潇洒','糊涂','积极','冷酷','深情','粗暴','温柔','可爱','愉快','义气','认真','威武',
            '帅气','传统','潇洒','漂亮','自然','专一','听话','昏睡','狂野','等待','搞怪','幽默','魁梧','活泼','开心',
            '高兴','超帅','留胡子','坦率','直率','轻松','痴情','完美','精明','无聊','有魅力','丰富','繁荣','饱满','炙热',
            '暴躁','碧蓝','俊逸','英勇','健忘','故意','无心','土豪','朴实','兴奋','幸福','淡定','不安','阔达','孤独',
            '独特','疯狂','时尚','落后','风趣','忧伤','大胆','爱笑','矮小','健康','合适','玩命','沉默','斯文','香蕉',
            '苹果','鲤鱼','鳗鱼','任性','细心','粗心','大意','甜甜','酷酷','健壮','英俊','霸气','阳光','默默','大力',
            '孝顺','忧虑','着急','紧张','善良','凶狠','害怕','重要','危机','欢喜','欣慰','满意','跳跃','诚心','称心',
            '如意','怡然','娇气','无奈','无语','激动','愤怒','美好','感动','激情','激昂','震动','虚拟','超级','寒冷',
            '精明','明理','犹豫','忧郁','寂寞','奋斗','勤奋','现代','过时','稳重','热情','含蓄','开放','无辜','多情',
            '纯真','拉长','热心','从容','体贴','风中','曾经','追寻','儒雅','优雅','开朗','外向','内向','清爽','文艺',
            '长情','平常','单身','伶俐','高大','懦弱','柔弱','爱笑','乐观','耍酷','酷炫','神勇','年轻','唠叨','瘦瘦',
            '无情','包容','顺心','畅快','舒适','靓丽','负责','背后','简单','谦让','彩色','缥缈','欢呼','生动','复杂',
            '慈祥','仁爱','魔幻','虚幻','淡然','受伤','雪白','高高','糟糕','顺利','闪闪','羞涩','缓慢','迅速','优秀',
            '聪明','含糊','俏皮','淡淡','坚强','平淡','欣喜','能干','灵巧','友好','机智','机灵','正直','谨慎','俭朴',
            '殷勤','虚心','辛勤','自觉','无私','无限','踏实','老实','现实','可靠','务实','拼搏','个性','粗犷','活力',
            '成就','勤劳','单纯','落寞','朴素','悲凉','忧心','洁净','清秀','自由','小巧','单薄','贪玩','刻苦','干净',
            '壮观','和谐','文静','调皮','害羞','安详','自信','端庄','坚定','美满','舒心','温暖','专注','勤恳','美丽',
            '腼腆','优美','甜美','甜蜜','整齐','动人','典雅','尊敬','舒服','妩媚','秀丽','喜悦','甜美','彪壮','强健',
            '大方','俊秀','聪慧','迷人','陶醉','悦耳','动听','明亮','结实','魁梧','标致','清脆','敏感','光亮','大气',
            '老迟到','知性','冷傲','呆萌','野性','隐形','笑点低','微笑','笨笨','难过','沉静','火星上','失眠','安静',
            '纯情','要减肥','迷路','烂漫','哭泣','贤惠','苗条','温婉','发嗲','会撒娇','贪玩','执着','眯眯眼','花痴',
            '想人陪','眼睛大','高贵','傲娇','心灵美','爱撒娇','细腻','天真','怕黑','感性','飘逸','怕孤独','忐忑','高挑',
            '傻傻','冷艳','爱听歌','还单身','怕孤单','懵懂'];
        $do = ["的","爱","","与","给","扯","和","用","方","打","就","迎","向","踢","笑","闻","有","等于","保卫","演变"];
        $wei= ['嚓茶','凉面','便当','毛豆','花生','可乐','灯泡','哈密瓜','野狼','背包','眼神','缘分','雪碧','人生','牛排',
            '蚂蚁','飞鸟','灰狼','斑马','汉堡','悟空','巨人','绿茶','自行车','保温杯','大碗','墨镜','魔镜','煎饼','月饼',
            '月亮','星星','芝麻','啤酒','玫瑰','大叔','小伙','哈密瓜，数据线','太阳','树叶','芹菜','黄蜂','蜜粉','蜜蜂',
            '信封','西装','外套','裙子','大象','猫咪','母鸡','路灯','蓝天','白云','星月','彩虹','微笑','摩托','板栗','高山',
            '大地','大树','电灯胆','砖头','楼房','水池','鸡翅','蜻蜓','红牛','咖啡','机器猫','枕头','大船','诺言','钢笔',
            '刺猬','天空','飞机','大炮','冬天','洋葱','春天','夏天','秋天','冬日','航空','毛衣','豌豆','黑米','玉米','眼睛',
            '老鼠','白羊','帅哥','美女','季节','鲜花','服饰','裙子','白开水','秀发','大山','火车','汽车','歌曲','舞蹈','老师',
            '导师','方盒','大米','麦片','水杯','水壶','手套','鞋子','自行车','鼠标','手机','电脑','书本','奇迹','身影','香烟',
            '夕阳','台灯','宝贝','未来','皮带','钥匙','心锁','故事','花瓣','滑板','画笔','画板','学姐','店员','电源','饼干',
            '宝马','过客','大白','时光','石头','钻石','河马','犀牛','西牛','绿草','抽屉','柜子','往事','寒风','路人','橘子',
            '耳机','鸵鸟','朋友','苗条','铅笔','钢笔','硬币','热狗','大侠','御姐','萝莉','毛巾','期待','盼望','白昼','黑夜',
            '大门','黑裤','钢铁侠','哑铃','板凳','枫叶','荷花','乌龟','仙人掌','衬衫','大神','草丛','早晨','心情','茉莉','流沙',
            '蜗牛','战斗机','冥王星','猎豹','棒球','篮球','乐曲','电话','网络','世界','中心','鱼','鸡','狗','老虎','鸭子','雨',
            '羽毛','翅膀','外套','火','丝袜','书包','钢笔','冷风','八宝粥','烤鸡','大雁','音响','招牌','胡萝卜','冰棍','帽子',
            '菠萝','蛋挞','香水','泥猴桃','吐司','溪流','黄豆','樱桃','小鸽子','小蝴蝶','爆米花','花卷','小鸭子','小海豚','日记本',
            '小熊猫','小懒猪','小懒虫','荔枝','镜子','曲奇','金针菇','小松鼠','小虾米','酒窝','紫菜','金鱼','柚子','果汁','百褶裙',
            '项链','帆布鞋','火龙果','奇异果','煎蛋','唇彩','小土豆','高跟鞋','戒指','雪糕','睫毛','铃铛','手链','香氛','红酒','月光',
            '酸奶','银耳汤','咖啡豆','小蜜蜂','小蚂蚁','蜡烛','棉花糖','向日葵','水蜜桃','小蝴蝶','小刺猬','小丸子','指甲油','康乃馨',
            '糖豆','薯片','口红','超短裙','乌冬面','冰淇淋','棒棒糖','长颈鹿','豆芽','发箍','发卡','发夹','发带','铃铛','小馒头','小笼包',
            '小甜瓜','冬瓜','香菇','小兔子','含羞草','短靴','睫毛膏','小蘑菇','跳跳糖','小白菜','草莓','柠檬','月饼','百合','纸鹤','小天鹅',
            '云朵','芒果','面包','海燕','小猫咪','龙猫','唇膏','鞋垫','羊','黑猫','白猫','万宝路','金毛','山水','音响','尊云','西安'];
        $tou_num=rand(0,count($tou)-1);
        $do_num=rand(0,count($do)-1);
        $wei_num=rand(0,count($wei)-1);
        $type = rand(0,1);
        $number = rand(0,99);
        if($type==0){
            $name=$tou[$tou_num].$do[$do_num].$wei[$wei_num];
        }else{
            $name=$wei[$wei_num].$tou[$tou_num];
        }
        return $name.$number;
    }
}