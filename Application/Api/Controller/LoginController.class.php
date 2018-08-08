<?php
/**
 * 用户登录/注册
 */
namespace Api\Controller;
use Think\Log;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;

class LoginController extends Controller{

	//用户登录
	public function login(){
		extract(require_check_post("mobile/s,password/s"));
		$password = I('post.password/s');
		$mobile = I('post.mobile/s');
		$member  = D("Members");
		//校验图形验证码
        //$this->_valid();
		$code = $member->checklogin($mobile,$password);
		if($code == 0){ //登录成功
			$session_token = $this->_session_token( $member->token() );
			$info = $member->profiles($member->token());
			$data = array(
				'authtoken' => $session_token,
				'name' => $info['name'],
				'mobile' => $info['phone'],
				'authtype' => $info['userlevel'],
				'iscertify' => $info['iscertify']
			);
			
            succ("登陆成功",$data);
		}else{ //登录失败
			if($code == -1){
				err("用户名不存在");
			}elseif($code == -2){
				err("密码错误");
			}elseif($code == -3){
				err("账号被禁用");
			}
		}
	}

    /**
     * 退出登录
     * @return [type] [description]
     */
    public function  loginout(){
        extract(require_check_post("session_token/s"));
        $redis = ConnRedis();
        $redis -> del($key);
        succ('退出成功');
    }

    /**
     * 创建session
     * @return [type] [description]
     */
    private function _session_token($userToken){
        $redis = ConnRedis();
        //清空该账户其他token
        $clearKeys = $redis -> keys('*'. $userToken);
        $redis -> del($clearKeys);
        //添加新token
        $key = sha1(time() . $userToken . Constants::PUB_SALT);
        $val = $userToken;
        $redis -> set($key .'-'. $val, $val, Constants::AUTH_TOKEN_TIME);
        return $key;
    }

    /**
     * 用户注册
     * @param  string $mobile   手机号
     * @param  string $password md5密码
     * @param  int    $invitemobile      mobile
     * @param  string $name 真实姓名
     * @param  string $paypwd    交易密码
     * @param  string $idno        身份证号
     * @param  string $smscode  手机验证码
     * @return [type]           [description]
     */
    public function reg(){
		//require_check_post("mobile/s,password/s,name/s,smscode/s,paypwd/s,idno/s,authtype/s"); 
		$model = D('Members');
		$phone = I('post.mobile/s');
		$password = I('post.password/s');
		$idno = I('post.idno/s');
		$paypwd = I('post.paypwd/s');
		$authtype = I('post.authtype/s');
		$invitemoblie = I('post.invitemobile/s');
		
		if(trim($invitemoblie)!=""){
			if(!$model->is_reg_vcode($invitemoblie)){
			err("邀请人手机号不存在");
			}
		}

		
		if($model->is_reg_vcode($phone)){
			err("手机号已经注册");
		}
		
		if(strlen($idno)<15 || strlen($idno)>18){
			err("身份证号输入有误");
		}

		$smscode = I('smscode/s');
		//短信验证码
		$codeModel = D('Validcode');
		if($codeModel -> expires($phone,$smscode,Constants::SMS_REGISTER_CODE)){
			err('短信验证码错误或已失效');
		}

		$password = I('post.password/s');
		$lid = intval(str_ireplace('m', '', addslashes(I('post.lid/s'))));
		$name = I('post.name/s');

		$member  = D("Members");
		$lid = $member->getInviteCode($invitemoblie);
		 $data = array(
			'phone' => $phone,
			'password' => $password,
			'paypwd' => $paypwd,
			'idno' => $idno,
			'leadid' => $lid['leadid'],
			'teamid' => $lid['teamid'],
			'name' => $name,
			'userlevel' => $authtype,
		);
		
		 /* $data = array(
			'phone' => 13439466199,
			'password' => '111456',
			'paypwd' => '111456',
			'idno' => '13439466199124567',
			'leadid' => $lid['leadid'],
			'teamid' => $lid['teamid'],
			'name' => '注册'
		);  */

        if($model->register($data)){
            succ('注册成功');
        }else{
            err('注册失败，稍后重试');
        }
    }
	
	/**
     * 未登录 忘记密码
	 * @param  string $mobile 手机号
	 * @param  string $password md5密码
	 * @param  string $vcode 短信验证码
     * @return [type] [description]
     */
    public function forgetPwd(){
        require_check_post("mobile,password,vcode");
        $mobile = I('mobile/s');
        $password = I('password/s');
        $vcode = I('vcode/s');
        $model  = D("Members");

        //用户名检测
        if(!$model->is_reg_vcode($mobile)){
            err("用户不存在");
        }

        //验证码检测
        $codeModel = D('Validcode');
        if($codeModel -> expires($mobile,$vcode,Constants::SMS_FINDPWD_CODE)){
            err('短信验证码错误或已失效');
        }

        //重置密码
        list($pwd, $salt) = md5password($password);
        $data['password'] = $pwd;
        $data['salt'] = $salt;
        $result = $model->where(array('phone' => $mobile))->save($data);
        if($result){
            succ('重置密码成功');
        }else{
            err('操作失败');
        }
    }
	
	    /**
     * 验证图形
     * @param  string $signcode  机器码
     * @param  string $validcode 验证码
     * @return [type]            [description]
     */
    private function _valid(){
        //extract(require_check_post("signcode/s, validcode/s"));
        extract(require_check_post("validcode/s"));
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