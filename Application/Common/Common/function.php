<?php
/**
 * JSON 返回失败数据
 */
function err($msg = ""){
    return_json($msg, "FAIL");
}
/**
 * JSON 返回成功数据
 * @param  string $param1    提示信息
 * @param  string $param2    返回数据
 */
function succ($param1 = "", $param2 = ""){
    if(is_array($param1)){
        return_json("", "SUCCESS", $param1);
    }else{
        return_json($param1, "SUCCESS", $param2);
    }
}
/**
 * JSON 返回数据
 * @param  string $msg       提示信息
 * @param  string $code      状态码
 * @param  array $result     返回数据
 */
function return_json($msg = '', $code = "SUCCESS", $result = ""){
    if(empty($msg)) $msg = $code == "SUCCESS" ? "返回成功" : "返回失败";
    $response = array(
                        "returnMsg"   => $msg,
                        "returnCode"  => $code,
                        );
    $result && $response["result"] = $result;
    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";
    echo json_encode($response);
    exit();
}
/**
 * 返回微信高级接口对象
 * @Author 刘晓雨    2016-05-31
 * @return Object
 */
function getWechatAuth(){
    $wxcfg = C('WECHAT_CONFIG');
    empty($wxcfg) && exit('请在后台设置微信相关参数');
    if((NOW_TIME-200) > $wxcfg['expires_in'] || empty($wxcfg['access_token'])){
        $wechatAuth = new \Com\WechatAuth($wxcfg['appid'], $wxcfg['appsecret']);
        $result = $wechatAuth->getAccessToken();

        if(isset($result['errcode'])) err($result);
        $wxcfg['expires_in']   = NOW_TIME + $result['expires_in'];
        $wxcfg['access_token'] = $result['access_token'];
        update_config(array('WECHAT_CONFIG'=>$wxcfg), 'wechat.php') || exit('更新微信配置缓存失败');
        unset($wechatAuth);
    }
    return new \Com\WechatAuth($wxcfg['appid'], $wxcfg['appsecret'], $wxcfg['access_token']);
}

function getJsApiTicket(){
    $ticket = '';
    $wxcfg = C('WECHAT_CONFIG');
    empty($wxcfg) && exit('请在后台设置微信相关参数');
    if((NOW_TIME-200) > $wxcfg['JsApiTicket_expires_in'] || empty($wxcfg['JsApiTicket'])){
        $wechatAuth = getWechatAuth();
        $result = json_decode($wechatAuth->getJsApiTicket(),true);
        if($result['errcode'] !== 0) err($result);
        $wxcfg['JsApiTicket_expires_in']   = NOW_TIME + $result['expires_in'];
        $wxcfg['JsApiTicket'] = $ticket = $result['ticket'];
        update_config(array('WECHAT_CONFIG'=>$wxcfg), 'wechat.php') || exit('更新微信配置缓存失败');
        unset($wechatAuth);
    }else{
        $ticket = $wxcfg['JsApiTicket'];
    }

    return $ticket;
}

/**
 * 更新缓存文件
 * @Author 刘晓雨    2016-05-31
 * @param  array  $cfg       配置数据
 * @param  string $filename  配置文件名
 * @return boolean           操作结果
 */
function update_config($cfg,$filename) {
    $file = CONF_PATH . $filename;
    if (is_file($file) && is_writable($file)) {
        $config = require $file;
        $config = array_merge($config, $cfg);
        file_put_contents($file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
        @unlink(RUNTIME_FILE);
        return true;
    } else {
        return false;
    }
}

/**
 * 生成MD5密码
 * @Author 刘晓雨    2016-03-03
 * @param  string $password  密码
 * @param  string $salt      混淆字符串
 * @return array or string
 */
function md5password($password, $salt=''){
    if($salt == ''){
        $salt = strtoupper(substr(md5(create_code(4)),0,8));
        $password = md5(md5($password).$salt);
        return array($password,$salt);
    }else{
        return md5(md5($password).$salt);
    }
}

/**
 * 生成验证码
 * @Author 刘晓雨     2016-03-02
 * @param  integer $length    验证码长度
 * @return String             验证码
 */
function create_code($length = 6){
    $code = '';
    for($i = 0; $i < $length; $i++){
        $code .= mt_rand(0,9);
    }
    return $code;
}

/**
 * 必填项检测
 * @Author 刘晓雨    2016-03-03
 * @param  String $keys      "key,name,code"
 * @return Array
 */
function require_check($keys){
    $request = array();
    $keys = explode(",",$keys);
    foreach($keys as $key){
        $key = trim($key);
        $value = I($key);
        if(stripos($key,"/")) $key = substr($key,0,stripos($key,"/"));
        if(empty($value)){
            err("缺少必填项:".$key);
        }else{
            $request[$key] = $value;
        }
    }
    return $request;
}

/**
 * 必填项检测
 * @Author 刘晓雨    2017-11-27
 * @param  String $keys      "key,name,code"
 * @return Array
 */
function require_check_post($keys){
    $request = array();
    $keys = explode(",",$keys);
    foreach($keys as $key){
        $key = trim($key);
        $value = I("post.".$key);
        if(stripos($key,"/")) $key = substr($key,0,stripos($key,"/"));
        if(empty($value)){
            err("缺少必填项:".$key);
        }else{
            $request[$key] = $value;
        }
    }
    return $request;
}

/**
 * 必填项检测
 * @Author 刘晓雨    2017-11-27
 * @param  String $keys      "key,name,code"
 * @return Array
 */
function require_check_api($keys, $data){
    $keys = explode(",",$keys);
    foreach($keys as $key){
        $key = trim($key);
        $value = $data[$key];
        if(empty($value)){
            err("缺少必填项:".$key);
        }
    }
}

/**
 * 必填项检测
 * @Author 刘晓雨    2016-03-03
 * @param  String $keys      "key,name,code"
 * @return Array
 */
function admin_require_check($keys){
    $request = array();
    $keys = explode(",",$keys);
    foreach($keys as $key){
        $value = I($key);
        if(stripos($key,"/")) $key = substr($key,0,stripos($key,"/"));
        if(empty($value)){
            return $key."不能为空";
        }
    }
    return false;
}

/**
 * 用户等级
 * @Author 刘晓雨    2016-03-31
 * @param  int    $exp       经验值
 * @return int    $level     等级
 */
function userLevel($exp){
    $rule = C("USER_LEVEL_RULE");
    $level = 0;
    foreach($rule as $item){
        if($exp >= $item['minexp']){
            $level++;
        }else{
            break;
        }
    }
    return $level;
}

/**
 * 获取有效期
 * @Author 刘晓雨    2016-04-13
 * @param  int $day  天
 */
function expires_in($day){
    return NOW_TIME + 60*60*24*$day;
}

/**
 * 生成订单号
 * @Author 刘晓雨    2016-04-13
 * @return [type] [description]
 */
function build_order_no(){
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
}

/**
 * 关键词过滤
 * @Author 刘晓雨    2016-04-12
 * @param  str $text      原字符串
 * @param  str $rpStr     替换后的字符
 */
function wordFilter($text,$rpStr="*"){
    $l1 = explode("\r\n",file_get_contents("./Public/wordFilter/baidu_guolv.txt",true));
    $l2 = explode("\r\n",file_get_contents("./Public/wordFilter/baidu_mingan.txt",true));
    $arr = array_merge($l1,$l2);
    foreach($arr as $v){
        $v = trim($v);
        if($v == '') continue;
        $replace = '';
        $length = abslength($v);
        for($i=0;$i<$length;$i++){
            $replace .= $rpStr;
        }

        $text = str_replace($v,$replace,$text);
    }
    return $text;
}

/**
* 统计中文字符串长度
* @param $str 要计算长度的字符串
*/
function abslength($str)
{
    if(empty($str)){
        return 0;
    }
    if(function_exists('mb_strlen')){
        return mb_strlen($str,'utf-8');
    }
    else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}
/**
 * 判断点是否多边形内部
 * @param  array  $polygon   多边形经纬数组
 * @param  array  $lnglat    点经纬度数组
 * @return boolean
 * $polygon = array(
        array(
            "lat" => 31.027666666667,
            "lng" => 121.42277777778
        ),
        array(
            "lat" => 31.016361111111,
            "lng" => 121.42797222222
        ),
        array(
            "lat" => 31.023666666667,
            "lng" => 121.45088888889
        ),
        array(
            "lat" => 31.035027777778,
            "lng" => 121.44575
        )
    );
    $lnglat = array(
        "lat" => 31.037666666667,
        "lng" => 121.43277777778
    );
 */
function isPointInPolygon($polygon,$lnglat){
    $count = count($polygon);
    $px = $lnglat['lat']; //纬度
    $py = $lnglat['lng']; //经度

    $flag = FALSE;

    for ($i = 0, $j = $count - 1; $i < $count; $j = $i, $i++) {
        $sy = $polygon[$i]['lng'];
        $sx = $polygon[$i]['lat'];
        $ty = $polygon[$j]['lng'];
        $tx = $polygon[$j]['lat'];

        if ($px == $sx && $py == $sy || $px == $tx && $py == $ty)
            return TRUE;

        if ($sy < $py && $ty >= $py || $sy >= $py && $ty < $py) {
            $x = $sx + ($py - $sy) * ($tx - $sx) / ($ty - $sy);
            if ($x == $px)
                return TRUE;
            if ($x > $px)
                $flag = !$flag;
        }
    }
    return $flag;
}

function getpage($count, $pagesize = 8, $urlParam = '') {
    $p = new Think\Page($count, $pagesize);
    if($urlParam){
        foreach($urlParam as $key=>$val) {
            if(strpos($key, '.')){
                $key = substr($key, strpos($key, '.') + 1);
            }
            $p->parameter[$key]   =   $val;
        }
    }
    $p -> setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
    $p -> setConfig('prev', '上一页');
    $p -> setConfig('next', '下一页');
    $p -> setConfig('last', '末页');
    $p -> setConfig('first', '首页');
    $p -> setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p -> lastSuffix = false;
    return $p;
}

function getpage2($count, $pagesize = 8, $urlParam = '') {
    $p = new Think\Page($count, $pagesize);
    if($urlParam){
        foreach($urlParam as $key=>$val) {
            if(strpos($key, '.')){
                $key = substr($key, strpos($key, '.') + 1);
            }
            $p->parameter[$key]   =   $val;
        }
    }
    $p -> setConfig('header', '<span class="rows">共<b>%TOTAL_ROW%</b>条记录&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</span>');
    $p -> setConfig('prev', '上一页');
    $p -> setConfig('next', '下一页');
    $p -> setConfig('last', '末页');
    $p -> setConfig('first', '首页');
    $p -> setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');
    $p -> lastSuffix = false;
    return $p;
}

function tranTime2($time) {
    $rtime = date("d/m/Y", $time);
    $htime = date("H:i", $time);
    $time = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时前 ';
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1)
            $str = '昨天 ';
        else
            $str = '前天 ';
    }
    else {
        $str = $rtime;
    }
    return $str;
}

function tranTime($time) {
    $rtime = date("Y-m-d H:i", $time);
    $htime = date("H:i", $time);
    $time = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    } elseif ($time < 60 * 60) {
        $min = floor($time / 60);
        $str = $min . '分钟前';
    } elseif ($time < 60 * 60 * 24) {
        $h = floor($time / (60 * 60));
        $str = $h . '小时前 ' . $htime;
    } elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time / (60 * 60 * 24));
        if ($d == 1)
            $str = '昨天 ' . $htime;
        else
            $str = '前天 ' . $htime;
    }
    else {
        $str = $rtime;
    }
    return $str;
}

function ClientIp(){
    //strcasecmp 比较两个字符，不区分大小写。返回0，>0，<0。
    if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $ip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $ip = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $res =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    return $res;
}

// 获取ip地址
function getIpaddr($ip,$newIP){
    if(!isset($newIP)){
        $newIP = new \Org\Util\IP();
    }
    if ($ip == '127.0.0.1' || $ip == '0.0.0.0')
        $data = '本机地址';
    else
    {
        $ip = $newIP -> find($ip);
        for ($i=1; $i < count($ip) ; $i++) {
            if($ip[$i] != $ip[$i-1])$data  = $data .$ip[$i];
        }
    }
    return $data;
}
/**
 * 获取客户端IP地址
 * @return [type] [description]
 */
function getClientIP()
{
    if (getenv("HTTP_CLIENT_IP")){
        $ip = getenv("HTTP_CLIENT_IP");
    }
    else if(getenv("HTTP_X_FORWARDED_FOR")){
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    }
    else if(getenv("REMOTE_ADDR")){
        $ip = getenv("REMOTE_ADDR");
    }else{
        $ip = "Unknow";
    }
    return $ip;
}

// 获取操作系统
function getOS() {
    $os = '';
    $Agent = $_SERVER['HTTP_USER_AGENT'];
    if (eregi('win', $Agent) && strpos($Agent, '95')) {
        $os = 'Win 95';
    } elseif (eregi('win 9x', $Agent) && strpos($Agent, '4.90')) {
        $os = 'Win ME';
    } elseif (eregi('win', $Agent) && ereg('98', $Agent)) {
        $os = 'Win 98';
    } elseif (eregi('win', $Agent) && eregi('nt 5.0', $Agent)) {
        $os = 'Win 2000';
    } elseif (eregi('win', $Agent) && eregi('nt 6.0', $Agent)) {
        $os = 'Win Vista';
    } elseif (eregi('win', $Agent) && eregi('nt 6.1', $Agent)) {
        $os = 'Win 7';
    } elseif (eregi('win', $Agent) && eregi('nt 5.1', $Agent)) {
        $os = 'Win XP';
    } elseif (eregi('win', $Agent) && eregi('nt 6.2', $Agent)) {
        $os = 'Win 8';
    } elseif (eregi('win', $Agent) && eregi('nt 6.3', $Agent)) {
        $os = 'Win 8.1';
    } elseif (eregi('win', $Agent) && eregi('nt 10', $Agent)) {
        $os = 'Win 10';
    } elseif (eregi('win', $Agent) && eregi('nt', $Agent)) {
        $os = 'Win NT';
    } elseif (eregi('win', $Agent) && ereg('32', $Agent)) {
        $os = 'Win 32';
    } elseif (ereg('Mi', $Agent)) {
        $os = '小米';
    } elseif (eregi('Android', $Agent) && ereg('LG', $Agent)) {
        $os = 'LG';
    } elseif (eregi('Android', $Agent) && ereg('M1', $Agent)) {
        $os = '魅族';
    } elseif (eregi('Android', $Agent) && ereg('MX4', $Agent)) {
        $os = '魅族4';
    } elseif (eregi('Android', $Agent) && ereg('M3', $Agent)) {
        $os = '魅族';
    } elseif (eregi('Android', $Agent) && ereg('M4', $Agent)) {
        $os = '魅族';
    } elseif (eregi('Android', $Agent) && ereg('Huawei', $Agent)) {
        $os = '华为';
    } elseif (eregi('Android', $Agent) && ereg('HM201', $Agent)) {
        $os = '红米';
    } elseif (eregi('Android', $Agent) && ereg('KOT', $Agent)) {
        $os = '红米4G版';
    } elseif (eregi('Android', $Agent) && ereg('NX5', $Agent)) {
        $os = '努比亚';
    } elseif (eregi('Android', $Agent) && ereg('vivo', $Agent)) {
        $os = 'Vivo';
    } elseif (eregi('Android', $Agent)) {
        $os = 'Android';
    } elseif (eregi('linux', $Agent)) {
        $os = 'Linux';
    } elseif (eregi('unix', $Agent)) {
        $os = 'Unix';
    } elseif (eregi('iPhone', $Agent)) {
        $os = '苹果';
    } else if (eregi('sun', $Agent) && eregi('os', $Agent)) {
        $os = 'SunOS';
    } elseif (eregi('ibm', $Agent) && eregi('os', $Agent)) {
        $os = 'IBM OS/2';
    } elseif (eregi('Mac', $Agent) && eregi('PC', $Agent)) {
        $os = 'Macintosh';
    } elseif (eregi('PowerPC', $Agent)) {
        $os = 'PowerPC';
    } elseif (eregi('AIX', $Agent)) {
        $os = 'AIX';
    } elseif (eregi('HPUX', $Agent)) {
        $os = 'HPUX';
    } elseif (eregi('NetBSD', $Agent)) {
        $os = 'NetBSD';
    } elseif (eregi('BSD', $Agent)) {
        $os = 'BSD';
    } elseif (ereg('OSF1', $Agent)) {
        $os = 'OSF1';
    } elseif (ereg('IRIX', $Agent)) {
        $os = 'IRIX';
    } elseif (eregi('FreeBSD', $Agent)) {
        $os = 'FreeBSD';
    } elseif ($os == '') {
        $os = 'Unknown';
    }
    return $os;
}

/**
 * 验证码检查
 */
function check_verify($code, $id = "") {
    $verify = new \Think\Verify();
    return $verify -> check($code, $id);
}

/**
 * 获取当前日期
 */
function getNowDate() {
    return date("Y-m-d");
}

/**
 * 手机号码
 */
function isPhone($val) {
    if (ereg("^1[1-9][0-9]{9}$", $val))
        return true;
    return false;
}

/*
 * 获取浏览器信息
 */
function getUserBrowser() {
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Maxthon')) {
        $browser = 'Maxthon';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 12.0')) {
        $browser = 'IE12.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 11.0')) {
        $browser = 'IE11.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 10.0')) {
        $browser = 'IE10.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 9.0')) {
        $browser = 'IE9.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8.0')) {
        $browser = 'IE8.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7.0')) {
        $browser = 'IE7.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0')) {
        $browser = 'IE6.0';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'NetCaptor')) {
        $browser = 'NetCaptor';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape')) {
        $browser = 'Netscape';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx')) {
        $browser = 'Lynx';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera')) {
        $browser = 'Opera';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
        $browser = 'Chrome';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox')) {
        $browser = 'Firefox';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari')) {
        $browser = 'Safari';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'iphone') || strpos($_SERVER['HTTP_USER_AGENT'], 'ipod')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'ipad')) {
        $browser = 'iphone';
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'android')) {
        $browser = 'android';
    } else {
        $browser = 'other';
    }
    return $browser;
}

function getAgent() {
    $agent = $_SERVER['HTTP_USER_AGENT'];
    return $agent;
}

function is_ip($str) {
    $ip = explode('.', $str);
    for ($i = 0; $i < count($ip); $i++) {
        if ($ip[$i] > 255) {
            return false;
        }
    }
    return preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/', $str);
}

/**
 * 字符串截取，支持中文和其他编码
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = '...') {
    if (function_exists("mb_substr")){
        $slice = mb_substr($str, $start, $length, $charset);
    } else if (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    if($slice == $str){
        return $slice;
    }else{
        return $suffix ? $slice . $suffix : $slice;
    }
}

/*
 * 删除缓存方法
 */
function delFileByDir($dir) {
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {

            $fullpath = $dir . "/" . $file;
            if (is_dir($fullpath)) {
                delFileByDir($fullpath);
            } else {
                unlink($fullpath);
            }
        }
    }
    closedir($dh);
}

/**
 * 获取网站域名
 */
function getDomain(){
    $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
    $domain = empty($_SERVER['HTTP_HOST']) ? $scheme. '://' .$_SERVER['SERVER_NAME'] : $scheme. '://' .$_SERVER['HTTP_HOST'];
    return $domain;
}
/**
 * 获取当前URL
 */
function getUrl(){
    return "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

function gen_order_id($prefix=''){
    return $prefix.date("ymdHis",time()).gen_vcode(6);
}
function gen_vcode($size){
    $code = "";
    for($i=0;$i<$size;$i++){
        $code = $code.mt_rand(0, 9);
    }
    return $code;
}

/**
 * 直辖市
 * @return [type] [description]
 */
function zx_city($cityname){
    $citys = array('北京市','上海市','重庆市','天津市');
    return in_array($cityname, $citys);
}

function money_order_status($status){
    switch ($status) {
        case 0:
            return '收益中';
            break;
        case 1:
            return '已结束';
            break;
        case 2:
            return '已撤回';
            break;
        default:
            break;
    }
    return '';
}

function shop_order_status($status){
    switch ($status) {
        case 0:
            return '待付款';
            break;
        case 1:
            return '待发货';
            break;
        case 2:
            return '待收货';
            break;
        case 3:
            return '已完成';
            break;
        case -1:
            return '已取消';
            break;
        case -3:
            return '已删除';
            break;
        case -10:
            return '退款中';
            break;
        case -11:
            return '退款失败';
            break;
        case -12:
            return '退款成功';
            break;
        case -20:
            return '退货退款中';
            break;
        case -21:
            return '退货退款失败';
            break;
        case -22:
            return '退货退款成功';
            break;
        default:
            break;
    }
    return '';
}
/**
 * 人人公益网订单状态
 */
function renren_order_status($status){
    switch ($status) {
        case -2:
            return '审核失败';
            break;
        case -1:
            return '已取消';
            break;
        case 0:
            return '审核中';
            break;
        case 1:
            return '已审核';
            break;
        case 2:
            return '已确认';
            break;
        case 3:
            return '已激励';
            break;
        default:
            break;
    }
    return '';
}
//用户身份 1信使 2商家 3服务商 4管理中心 5业务员 6省级管理中心 7市级管理中心 8联合服务中心
function user_role_name($role){
    switch ($role) {
        case 1:
            return '天使消费者';
            break;
        case 2:
            return '联盟商家';
            break;
        case 3:
            return '服务中心';
            break;
        case 5:
            return '业务员';
            break;
        case 6:
            return '省级管理中心';
            break;
        case 7:
            return '市级管理中心';
            break;
        case 8:
            return '联合服务中心';
            break;
        default:
            break;
    }
    return '';
}
    /**
     * 发送HTTP请求方法，目前只支持CURL发送请求
     * @param  string $url    请求URL
     * @param  array  $param  GET参数数组
     * @param  array  $data   POST的数据，GET请求时该参数无效
     * @param  string $method 请求方法GET/POST
     * @return array          响应数据
     */
    function http($url, $param, $data = '', $method = 'GET'){
        $opts = array(
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        );

        /* 根据请求类型设置特定参数 */
        $opts[CURLOPT_URL] = $url . '?' . http_build_query($param);
        if(strtoupper($method) == 'POST'){
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $data;

            if(is_string($data)){ //发送JSON数据
                $opts[CURLOPT_HTTPHEADER] = array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($data),
                );
            }
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        //发生错误
        if($error) return $error;
        return  $data;
    }

    /**
     * 获取分表 表名
     * @param  [type] $table 表名
     * @param  [type] $id    ID
     */
    function get_hash_table($table,$id) {
        $len = strlen($id);
        if($len == 1) {
            $t = '0'.substr($id,0,1);
        } else {
            $t = substr($id,$len-2,2);
        }
        return $table.'_'.$t;
    }




    function la($num) {
        if(is_numeric($num) && $num < 999999) {
            //生成数字
            $randStr = numRand($num);
        } else {
            //字母开头或结尾
            $randStr = is_numeric($num) ? 'a00000' : charRand($num);
        }
        return $randStr;
    }

    function charRand($char = '') {
        $char_array = array('a','b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 's', 'y', 'z'
        );
        $pix = $char[0];
        //一个字母开头
        if(!is_numeric($pix) && is_numeric($char[1])) {
            $num = intval(substr($char,1));
            if($num < 99999) {
                $num++;
                $len = strlen($num);
                for ($i=0; $i < (5-$len); $i++) {
                    $num = 0 . $num;
                }
                $num = $pix . $num;
            } else {
                if($pix == 'z') {
                    $num = '00000a';
                } else {
                    foreach ($char_array as $key => $v) {
                        if($v == $pix) {
                            $pix = $char_array[$key+1];
                            break;
                        }
                    }
                    $num = $pix . '00000';
                }
            }
        } else {
            $pix = substr($char,-1);
            $num = intval(substr($char,0,-1));
            if($num < 99999) {
                $num++;
                $len = strlen($num);
                $str = '';
                for ($i=0; $i < (5-$len); $i++) {
                    $str = 0 . $str;
                }
                $num = $str . $num;
            } else {
                if($pix == 'z') {

                } else {
                    foreach ($char_array as $key => $v) {
                        if($v == $pix) {
                            $pix = $char_array[$key+1];
                            break;
                        }
                    }
                    $num = '00000';
                }
            }
            $num = $num.$pix;
        }
        return $num;
    }

    //数字随机
    function numRand($num = 0) {
        if($num < 999999) {
            $num++;
            $len = strlen($num);
            for ($i=0; $i < (6-$len); $i++) {
                $num = 0 . $num;
            }
            return $num;
        } else {
            return false;
        }
    }



    function get_mod_table($table,$id) {

        $t=fmod($id,5);

        return $table.'_'.$t;
    }
    //模版的截取字段省略显示
    function subtext($text, $length)
    {
        if(mb_strlen($text, 'utf8') > $length)
            return mb_substr($text, 0, $length, 'utf8').'...';
        return $text;
    }


    function getAuthImage($text) {
        $im_x = 160;
        $im_y = 40;
        $im = imagecreatetruecolor($im_x, $im_y);
        $text_c = ImageColorAllocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
        $tmpC0 = mt_rand(240, 255);
        $tmpC1 = mt_rand(240, 255);
        $tmpC2 = mt_rand(240, 255);

        $buttum_c = ImageColorAllocate($im, $tmpC0, $tmpC1, $tmpC2);
        imagefill($im, 16, 30, $buttum_c);

		
		
        $font = 'Public/Font/t1.ttf';
		$font = 'php_verify/ttfs/t' . mt_rand(1, 32) . '.ttf';     

        for ($i = 0; $i < strlen($text); $i++) {
            $tmp = substr($text, $i, 1);
            $array = array(-1, 1);
            $p = array_rand($array);
            $an = $array[$p] * mt_rand(1, 10); //角度
            $size = 28;
            imagettftext($im, $size, $an, 15 + $i * $size, 35, $text_c, $font, $tmp);
        }


        $distortion_im = imagecreatetruecolor($im_x, $im_y);

        imagefill($distortion_im, 5, 13, $buttum_c);
        for ($i = 0; $i < $im_x; $i++) {
            for ($j = 0; $j < $im_y; $j++) {
                $rgb = imagecolorat($im, $i, $j);
                if ((int) ($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) <= imagesx($distortion_im) && (int) ($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) >= 0) {
                    imagesetpixel($distortion_im, (int) ($i + 10 + sin($j / $im_y * 2 * M_PI - M_PI * 0.1) * 4), $j, $rgb);
                }
            }
        }
        //加入干扰象素;
        $count = 20; //干扰像素的数量
        for ($i = 0; $i < $count; $i++) {
            $randcolor = ImageColorallocate($distortion_im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($distortion_im, mt_rand() % $im_x, mt_rand() % $im_y, $randcolor);
        }
        $rand = mt_rand(5, 30);
        $rand1 = mt_rand(15, 25);
        $rand2 = mt_rand(5, 10);
        for ($yy = $rand; $yy <= +$rand + 2; $yy++) {
            for ($px = -80; $px <= 80; $px = $px + 0.1) {
                $x = $px / $rand1;
                if ($x != 0) {
                    $y = sin($x);
                }
                $py = $y * $rand2;

                imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
                imagesetpixel($distortion_im, $px - 40, $py + $yy, $text_c);
				//imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
				imagesetpixel($distortion_im, $px + 120, $py + $yy, $text_c);
            }
        }

        //设置文件头;
        Header("Content-type: image/JPEG");
        //以PNG格式将图像输出到浏览器或文件;
        ImagePNG($distortion_im);
        //销毁一图像,释放与image关联的内存;
        ImageDestroy($distortion_im);
        ImageDestroy($im);
    }

    function make_rand($length = "32") {//验证码文字生成函数
        $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $result = "";
        for ($i = 0; $i < $length; $i++) {
            $num[$i] = rand(0, 25);
            $result.=$str[$num[$i]];
        }
        return $result;
    }
    function p($data){
        echo "<pre>";
        print_r($data);die;
    }



function post($url, $postfields = array(),$uploadFile = array()) {
    $http_info = array();
    $header = array(
        'Content-Type: multipart/form-data',
    );
    $ci = curl_init();
    curl_setopt($ci, CURLOPT_URL, $url);
    curl_setopt($ci, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ci, CURLOPT_BINARYTRANSFER,true);
    curl_custom_postfields($ci, $postfields, $uploadFile);
    curl_setopt($ci, CURLOPT_USERAGENT, "Yeepay ZGT PHPSDK v1.1x");
    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ci, CURLOPT_HEADER, false);
    curl_setopt($ci, CURLOPT_POST, true);


    $response = curl_exec($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    $http_info = array_merge($http_info, curl_getinfo($ci));
    //print_r($http_info);
    //echo "<br/>";
    curl_close ($ci);
    return $response;
}
/**
 * 重写POST 参数body
 *
 * @param resource $ch cURL resource
 * @param array $assoc name => value
 * @param array $files name => path
 * @return bool
 */
function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {

    // invalid characters for "name" and "filename"
    static $disallow = array("\0", "\"", "\r", "\n");

    // build normal parameters
    foreach ($assoc as $k => $v) {
        $k = str_replace($disallow, "_", $k);
        $body[] = implode("\r\n", array(
            "Content-Disposition: form-data; name=\"{$k}\"",
            "",
            filter_var($v),
        ));
    }

    // build file parameters
    foreach ($files as $k => $v) {
        switch (true) {
            case false === $v = realpath(filter_var($v)):
            case !is_file($v):
            case !is_readable($v):
                continue; // or return false, throw new InvalidArgumentException
        }
        $data = file_get_contents($v);
        $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
        $k = str_replace($disallow, "_", $k);
        $v = str_replace($disallow, "_", $v);
        $body[] = implode("\r\n", array(
            "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
            "Content-Type: application/octet-stream",
            "",
            $data,
        ));
    }

    // generate safe boundary
    do {
        $boundary = "---------------------" . md5(mt_rand() . microtime());
    } while (preg_grep("/{$boundary}/", $body));

    // add boundary for each parameters
    array_walk($body, function (&$part) use ($boundary) {
        $part = "--{$boundary}\r\n{$part}";
    });

    // add final boundary
    $body[] = "--{$boundary}--";
    $body[] = "";

    // set options
    return @curl_setopt_array($ch, array(
        CURLOPT_POST       => true,
        CURLOPT_POSTFIELDS => implode("\r\n", $body),
        CURLOPT_HTTPHEADER => array(
            "Expect: 100-continue",
            "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
        ),
    ));
}

   function encrpt($queryData){
       //请求明文参数，hmac需求参数数组
       $needRequestHmac=array(0 => "requestid", 1 => "amount", 2 => "assure", 3 => "productname", 4 => "productcat", 5 => "productdesc", 6 => "divideinfo", 7 => "callbackurl", 8 => "webcallbackurl", 9 => "period", 10 => "memo");
       $hmacData = array();
       $hmacData["customernumber"] ="10015469830";
       foreach ( $needRequestHmac as $hKey => $hValue ) {

           $v = "";
           //判断$queryData中是否存在此索引并且是否可访问
           if ( isViaArray($queryData, $hValue) && $queryData[$hValue] ) {

               $v = $queryData[$hValue];
           }

           //取得对应加密的明文的值
           $hmacData[$hValue] = $v;
       }
       $hmac = getHmac($hmacData, '8863566Po4z5m768FrF6BF1y1K43V1Gn6i8204173Cgj914cXqtxdx0Zd485');
       $needRequest=array(0 => "requestid", 1 => "amount", 3 => "productname", 4 => "productcat", 7 => "callbackurl", 8 => "webcallbackurl", 10 => "memo",11=>'payproducttype',12 =>"directcode");
       $dataMap["customernumber"] ="10015469830";
       foreach ( $needRequest as $rKey => $rValue ){
           $v = "";
           //判断$queryData中是否存在此索引并且是否可访问
           if ( isViaArray($queryData, $rValue) && $queryData[$rValue] ) {

               $v = $queryData[$rValue];
           }

           //取得对应加密的明文的值
           $dataMap[$rValue] = $v;
       }

       $dataMap["hmac"] = $hmac;
       $dataJsonString = cn_json_encode($dataMap);
       $data = strtoupper(bin2hex(openssl_encrypt($dataJsonString, "aes-128-ecb", '8863566Po4z5m768',OPENSSL_PKCS1_PADDING)));
       $postfields = array("customernumber" =>10015469830, "data" => $data);
       $requestURL="https://o2o.yeepay.com/zgt-api/api/pay";
       $data = post($requestURL,$postfields);
       $responseJsonArray = json_decode($data, true);
       $data=hex2bin($responseJsonArray["data"]);
       $responseData = openssl_decrypt($data,'aes-128-ecb','8863566Po4z5m768',OPENSSL_PKCS1_PADDING);
       return $responseData;
    }

/**
 * @取得hmac签名
 * @$dataArray 明文数组或者字符串
 * @$key 密钥
 * @return string
 *
 */
function getHmac(array $dataArray, $key) {

    if ( !isViaArray($dataArray) ) {

        return null;
    }

    if ( !$key || empty($key) ) {

        return null;
    }

    if ( is_array($dataArray) ) {

        $data = implode("", $dataArray);
    } else {

        $data = strval($dataArray);
    }

    //print_r($data);

//    if ( getLocaleCode() != "UTF-8" ) {

//        $key = iconv(getLocaleCode(), "UTF-8", $key);
//        $data = iconv(getLocaleCode(), "UTF-8", $data);
//    }


    $b = 64; // byte length for md5
    if (strlen($key) > $b) {

        $key = pack("H*",md5($key));
    }

    $key = str_pad($key, $b, chr(0x00));
    $ipad = str_pad('', $b, chr(0x36));
    $opad = str_pad('', $b, chr(0x5c));
    $k_ipad = $key ^ $ipad ;
    $k_opad = $key ^ $opad;

    return md5($k_opad . pack("H*",md5($k_ipad . $data)));
}

/**
 *
 * @将数组转换为JSON字符串（兼容中文）
 * @$array 要转换的数组
 * @return string 转换得到的json字符串
 *
 */
function cn_json_encode($array) {
    $array = cn_url_encode($array);
    $json = json_encode($array);
    return urldecode($json);
}
/**
 *
 * @将数组统一进行urlencode（兼容中文）
 * @$array 要转换的数组
 * @return array 转换后的数组
 *
 */
function cn_url_encode($array) {
    arrayRecursive($array, "urlencode", true);
    return $array;
}
/**
 * @使用特定function对数组中所有元素做处理
 * @&$array 要处理的字符串
 * @$function 要执行的函数
 * @$apply_to_keys_also 是否也应用到key上
 *
 */
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
}
/**
 * @检查一个数组是否是有效的
 * @$checkArray 数组
 * @$arrayKey 数组索引
 * @return boolean
 * 如果$arrayKey传参，则不止检查数组，
 * 而且检查索引是否存在于数组中。
 *
 */
function isViaArray($checkArray, $arrayKey = null) {

    if ( !$checkArray || empty($checkArray) ) {

        return false;
    }

    if ( !$arrayKey ) {

        return true;
    }

    return array_key_exists($arrayKey, $checkArray);
}

	function pay_configs(){
	    $config = array(
	        'money' => 50,
	        'loves' => 50,
	        'money1'=> 50,
	        'gold'  => 50,
	        'service' => 6
	    );
	    return $config;
	}
	/**
	 * 日志表-配置
	 */
	function get_user_log($type){
        if(!$type){
            return false;
        }
        $table = 'user_log_';
        $date = date('Ym');
        if (time() > 1506787200){
            $date = 201709;
            $t = M($table.$type.'_'.$date);
        }else{
            $t = M($table.$type.'_'.$date);
        }
	    
	    return $t;
	}
	
	/**
	 * 遍历日志表
	 */
	function all_user_log($type,$field,$where,$order,$page,$limit){
	    set_time_limit(0);
        $date = date('Y',time());
        $date_m = date('m',time());
        $date_m = 10;
        
        if (date('Y',time()) == 2017){
            $star = 9;
        }else{
            $star = 1;
        }
        for ($i=$star;$i<=$date_m;$i++){
            if ($i < 10){
                $i = '0'.$i;
            }
            $time = $date . $i;
        
            if ($type == all){
                $sql = "select $field from (select $field from app_user_log_in_$time union all select $field from app_user_log_out_$time) as A where $where order by $order limit $page,$limit";
            }elseif($type == in){
                $sql = "select DISTINCT $field from (select $field from app_user_log_in_$time union all select $field from app_user_log_in_$time) as A where $where order by $order limit $limit,10";
            }elseif($type == out){
                $sql = "select DISTINCT $field from (select $field from app_user_log_out_$time union all select $field from app_user_log_out_$time) as A where $where order by $order limit $limit,10";
            }
        }
        $Model = M()->query($sql);
	    
        return $Model;
	}
	/**
	 * 签名验证/生成
	 * @param  array  $member 用户信息
	 * @param  string $act    操作 生成gen 验证check
	 * @return [type]         [description]
	 */
	function sign($member, $act = 'check'){
	
	    $member['score1'] = number_format($member['score1'], 2, '.', '');
	    $member['score2'] = number_format($member['score2'], 2, '.', '');
	    $member['score3'] = number_format($member['score3'], 2, '.', '');
	    $member['balance'] = number_format($member['balance'], 2, '.', '');
	    $member['balance_tx'] = number_format($member['balance_tx'], 2, '.', '');
	
	    if($act === 'check'){
	        $logModel  = D('Api/MembersLog');
	        $signModel = D('Api/membersSign');
	        $str  = $member['score1'] . $member['score2'] . $member['score3'] . $member['balance'] . $member['balance_tx'] . $member['token'];
	        $lastLog  = $logModel->getLastLog($member['id']);
	        $lastSign = $signModel->getLastSign($member['token']);
	        // dump($str);
	        //兼容注册
	        // dump(sha1($str) . Constants::PUB_SALT . $member['reg_time']);
	        // dump($str);
	        // dump($lastLog);
	        $sign = empty($lastLog)
	        ? md5(sha1($str) . Constants::PUB_SALT . $lastSign['upd_time'])
	        : md5(sha1($str) . Constants::PUB_SALT . $lastLog['ctime']);
	        // dump($sign);
	        // exit;
	        // dump($lastSign);
	        if((empty($lastLog) && $sign === $lastSign['last_sign'])||($sign === $lastLog['sign'] && $lastLog['sign'] === $lastSign['last_sign'])){
	            return true;
	        }else{
	            //记录失败
	            M('members_sign_err')->add(array('uid' => $member['id'], 'ctime' => NOW_TIME));
	            M('members')->where(array('id' => $member['id']))->save(array('is_lock' => 1));
	            return false;
	        }
	    }else{
	        $str  = $member['score1'] . $member['score2'] . $member['score3'] . $member['balance'] . $member['balance_tx'] . $member['token'];
	        // echo 'gen';
	        // dump($str);
	        // dump(sha1($str) . Constants::PUB_SALT . NOW_TIME);
	        $sign = md5(sha1($str) . Constants::PUB_SALT . NOW_TIME);
	        // dump($sign);
	        return $sign;
	    }
	}




?>