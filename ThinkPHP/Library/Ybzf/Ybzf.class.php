<?php
namespace Ybzf;
use Ybzf\CryptAES;
class Ybzf{
    /**
     * 主账户编号
     * @var string
     */
    private static  $customerNumber = '10015469830';

    /**
     * 商户秘钥
     * @var string
     */
    private static $keyValue = '8863566Po4z5m768FrF6BF1y1K43V1Gn6i8204173Cgj914cXqtxdx0Zd485';

    /**
     * 商户秘钥前16位
     * @var string
     */
    private static $keyValueFirst= '8863566Po4z5m768';

    function encrpt($needRequestHmac,$needRequest,$queryData,$requestURL,$uploadFile){

        $hmacData = array();
        $hmacData["customernumber"] =self::$customerNumber;
        foreach ( $needRequestHmac as $hKey => $hValue ) {

            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if (self:: isViaArray($queryData, $hValue) && $queryData[$hValue] ) {

                $v = $queryData[$hValue];
            }

            //取得对应加密的明文的值
            $hmacData[$hValue] = $v;
        }
        $hmac =self:: getHmac($hmacData);

        $dataMap["customernumber"] =self::$customerNumber;
        foreach ( $needRequest as $rKey => $rValue ){
            $v = "";
            //判断$queryData中是否存在此索引并且是否可访问
            if ( self::isViaArray($queryData, $rValue) && $queryData[$rValue] ) {

                $v = $queryData[$rValue];
            }

            //取得对应加密的明文的值
            $dataMap[$rValue] = $v;
        }

        $dataMap["hmac"] = $hmac;
        $dataJsonString =self:: cn_json_encode($dataMap);
        $data = strtoupper(bin2hex(openssl_encrypt($dataJsonString, "aes-128-ecb", self::$keyValueFirst,OPENSSL_PKCS1_PADDING)));
        $postfields = array("customernumber" =>self::$customerNumber, "data" => $data);
        $data =self::post($requestURL,$postfields,$uploadFile);
        $responseJsonArray = json_decode($data, true);
        $data=hex2bin($responseJsonArray["data"]);
        $responseData = openssl_decrypt($data,'aes-128-ecb',self::$keyValueFirst,OPENSSL_PKCS1_PADDING);
        return $responseData;
    }

    /**
     * @取得hmac签名
     * @$dataArray 明文数组或者字符串
     * @$key 密钥
     * @return string
     *
     */
    static function getHmac(array $dataArray) {
        $key=self::$keyValue;
        if ( !self::isViaArray($dataArray) ) {

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
  private static function cn_json_encode($array) {
        $array =self:: cn_url_encode($array);
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
    private static function cn_url_encode($array) {
        self::arrayRecursive($array, "urlencode", true);
        return $array;
    }
    /**
     * @使用特定function对数组中所有元素做处理
     * @&$array 要处理的字符串
     * @$function 要执行的函数
     * @$apply_to_keys_also 是否也应用到key上
     *
     */
    private static function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::arrayRecursive($array[$key], $function, $apply_to_keys_also);
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
        private static function isViaArray($checkArray, $arrayKey = null) {

        if ( !$checkArray || empty($checkArray) ) {

            return false;
        }

        if ( !$arrayKey ) {

            return true;
        }

        return array_key_exists($arrayKey, $checkArray);
    }
    private  static  function post($url, $postfields = array(),$uploadFile = array()) {
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

        curl_close ($ci);
        return $response;
    }

    function getDeAes($data) {
        $aesKey=self::$keyValueFirst;
        $aes = new CryptAES();
        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $text = $aes->decrypt($data);
        return $text;
    }
}
