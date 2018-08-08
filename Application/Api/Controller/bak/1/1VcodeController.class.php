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
class VcodeController extends Controller{

    /**
     * 注册验证码
     * @return [type] [description]
     */
    public function reg_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg_vcode($mobile);
		
        //图片验证
		$this->_valid();
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_REGISTER_CODE, Constants::SMSTEMPLATE_REG_VCODE, $datas);

    }
	
    /**
     * 提现验证码
     * @return [type] [description]
     */
    public function apply_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg_vcode($mobile);
    
    
        $code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_APPLY_CODE, Constants::SMS_APPLY_CODE, $datas);
    
    }
    
    
	 /**
     * Web注册验证码
     * @return [type] [description]
     */
    public function web_reg_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg_vcode($mobile);
		//图片验证
		$this->_web_valid();
		
        if($is_reg){
            err("注册失败,手机号已注册");
        }
		
		$code = create_code(4);
        $datas = array($code,'5分钟');
        $this->_send_sms(Constants::SMS_REGISTER_CODE, Constants::SMSTEMPLATE_REG_VCODE, $datas);

    }
	//web验证图形
	private function _web_valid(){
        //extract(require_check_post("signcode/s, validcode/s"));
		$signcode = I('signcode', '', 'addslashes');
		$validcode = I('validcode', '', 'addslashes');
		
        /* $redis = ConnRedis();
        $key = 'img:'.md5($signcode);
        $val = $redis -> get($key); */
        // dump($val);
        // dump($validcode);
        if(strval($val) !== strval($validcode)){
            err('图形验证码错误或已失效,请刷新重试');
        }
        //验证过自动删除
        $redis -> del($key);
    }
    /**
     * 找回密码
     * @return [type] [description]
     */
    public function findpwd_sms(){
        $mobile = I('mobile', '', 'addslashes');
        $is_reg = D('Members')->is_reg_vcode($mobile);
        if(!$is_reg){
            err("手机号未注册");
        }
		//图片验证
		$this->_valid();
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
        extract(require_check("signcode"));
        $redis = ConnRedis();
        $vcode = gen_vcode(4);
        $key  = 'img:'.md5($signcode);
        $data = $redis -> set( $key , strval($vcode) , Constants::IMAGE_CODE_EXPIRE_TIME);
        getAuthImage($vcode);;
    }
	
	public function getImgCode($vcode) {  
		$code = $vcode;  
		$img = imagecreatetruecolor(100, 30);  
		$black = imagecolorallocate($img, 0x00, 0x00, 0x00);  
		$green = imagecolorallocate($img, 0x00, 0xFF, 0x00);  
		$white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);  
		imagefill($img,2,2,$white);   

		imagestring($img, 5, 30, 10, $code, $black);  
		//加入噪点干扰  
		for($i=0;$i<50;$i++) {  
		  imagesetpixel($img, rand(0, 100) , rand(0, 100) , $black);   
		  imagesetpixel($img, rand(0, 100) , rand(0, 100) , $green);  
		}  
		//输出验证码  
		header("content-type: image/png");  
		imagepng($img);  
		imagedestroy($img);  
		
	}


	
	
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
        }
        //验证过自动删除
        $redis -> del($key);
    }
	

    

}