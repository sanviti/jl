<?php
/**
 * 验证码
 */
namespace Api\Controller;
use Think\Log;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;
use Common\Lib\RestSms;
class TestVcodeController extends Controller{

    /**
     * 注册验证码
     * @return [type] [description]
     */
    public function reg_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg($mobile);
        if($is_reg){
            err("注册失败,手机号已注册");
        }
		
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_REGISTER_CODE, Constants::SMSTEMPLATE_REG_VCODE, $datas);

    }
	
	 /**
     * Web注册验证码
     * @return [type] [description]
     */
    public function web_reg_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg($mobile);
        if($is_reg){
            err("注册失败,手机号已注册");
        }
		
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_REGISTER_CODE, Constants::SMSTEMPLATE_REG_VCODE, $datas);

    }
	
    /**
     * 找回密码
     * @return [type] [description]
     */
    public function findpwd_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg($mobile);
        if(!$is_reg){
            err("手机号未注册");
        }
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_FINDPWD_CODE, Constants::SMSTEMPLATE_FINDPWD_VCODE, $datas);

    }
	
    /**
     * 修改登录密码
     * @return [type] [description]
     */
    public function forgetpwd_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg($mobile);
        if(!$is_reg){
            err("手机号未注册");
        }
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_FINDPWD_CODE, Constants::SMSTEMPLATE_FINDPWD_VCODE, $datas);

    }
	
    /**
     * 找回交易密码
     * @return [type] [description]
     */
    public function findpaypwd_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg($mobile);
        if(!$is_reg){
            err("手机号未注册");
        }
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_FINDPWD_CODE, Constants::SMSTEMPLATE_FINDPWD_VCODE, $datas);

    }
    /**
     * 发送短信验证码
     * @return [type] [description]
     */
    private function _send_sms($range, $tempid, $datas){
        extract(require_check_post("mobile/s"));
        //校验图形验证码
        //$this->_valid();
        $this->_check_mobile($mobile, $range);
        $model = D('Validcode');
        $ip = ip2long(ClientIp());
        $expires_in = NOW_TIME + Constants::SMS_EXPIRE_TIME; //过期时间(5分钟)
        $data = array(
            "mobile" => $mobile,
            "code"   =>  $datas[0],
            "range"  =>  $range,
            "expires_in" => $expires_in,
            'ip' => $ip
        );
        $rest = new RestSms();
         $result = $rest->sendTemplateSMS($mobile, $datas, $tempid);
        if($model->add($data)){
            succ("发送成功");
        }else{
            err("发送失败");
        }
    }

    /**
     * 验证手机号
     * @param  [type] $mobile [description]
     * @param  [type] $range  [description]
     * @return [type]         [description]
     */
    private function _check_mobile($mobile, $range){
        if(!isPhone($mobile)){
            err("请输入有效手机号");
        }
        $model = D('Validcode');
        //同一手机号限制80s内不允许发送
        $last = $model->lastSMS($mobile, $range);
        if($last) {
            if( ( NOW_TIME - ($last['expires_in'] - Constants::SMS_EXPIRE_TIME) ) < Constants::SMS_INTERVAL_TIME ) {
                err("发送频繁");
            }
        }
    }

    /**
     * 图形验证码
     * @param  string $signcode 机器码
     * @return [type]           [description]
     */
    public function image(){
        $vcode = gen_vcode(4);
		$text = 1234;
        getAuthImage($text);
        //$this->getImgCode($vcode);
    }
	
	public function getImgCode($vcode) {
		 $code = $vcode;  
		$img = imagecreatetruecolor(100, 30);  
		$black = imagecolorallocate($img, 0x00, 0x00, 0x00);  
		$green = imagecolorallocate($img, 0x00, 0xFF, 0x00);  
		$white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
		$fontfile = 'Public/DataAdmin/Font/glyphicons-halflings-regular.ttf';
		imagefill($img,2,2,$white);   

		//imagestring($img, 5, 30, 10, $code, $black); 
		imagettftext ($img , 50 , 1 , 30 , 10 , $black , $fontfile ,$vcode );	
		//@imagefttext($img, 1000 , 0, 5, $size+3, $text_color, 'c://WINDOWS//Fonts//simsun.ttc',$code);  
		//加入噪点干扰  
		for($i=0;$i<50;$i++) {  
		  imagesetpixel($img, rand(0, 100) , rand(0, 100) , $black);   
		  imagesetpixel($img, rand(0, 100) , rand(0, 100) , $green);  
		}  
		//输出验证码  
		header("content-type: image/png");  
		imagepng($img);  
		imagedestroy($img);   
		
		
		
	}//DataAdminfonts/glyphicons-halflings-regular.ttf


	
	
    /**
     * 验证图形
     * @param  string $signcode  机器码
     * @param  string $validcode 验证码
     * @return [type]            [description]
     */
    private function _valid(){
        extract(require_check_post("signcode/s, validcode/s"));
        $redis = ConnRedis();
        $key = 'img:'.md5($signcode);
        $val = $redis -> get($key);
        // dump($val);
        // dump($validcode);
        if(strval($val) !== strval($validcode)){
            err('图形验证码错误或已失效,请刷新重试');
        }else{
			succ("验证通过");
		}
        //验证过自动删除
        $redis -> del($key);
    }

    

}