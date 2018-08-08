<?php
/**
 * 用户登录/注册
 */
namespace Api\Controller;
use Think\Log;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;

class WebController extends Controller{
	
	public function index(){
		$this->assign("invitecode",I('invitecode'));
		$this->display();
	}
	
	//下载
	public function download(){
		$this->display();
	}
	//下载
	public function appdownload(){
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		$this->display();
	}
	//下载
	public function dlqrcode(){
		$this->display();
	}
	
	//交易规则
	public function trading_rule(){
		$this->display();
	}
	//推广规则
	public function share_rule(){
		$this->display();
	}
	
	//注册协议
	public function reg_rule(){
		$this->display();
	}
	
	//卖出协议
	public function sale_rule(){
		$this->display();
	}
	/*
	*
	*注册
	*/
	public function register(){
		$moblie = I('post.phone');
		$password = I('post.pwd');
		$vcode = I('post.vcode');
		$lid = I('post.invitecode');
		$authtype = I('post.authtype');
		$username = I('post.username');
		$member = D("Members");
		if($member->is_reg_vcode($moblie)){
            err("手机号已经注册");
        }
        $smscode = I('vcode/s');
        //短信验证码
        $codeModel = D('Validcode');
        if($codeModel -> expires($moblie,$smscode,Constants::SMS_REGISTER_CODE)){
            err('短信验证码错误或已失效');
        } 
		$data = array(
			'phone' => $moblie,
			'name' => $username,
			'password' => $password,
			'userlevel' => $authtype,
			'leadid' => $lid
		);
		

        if($member->webregister($data)){
            succ('注册成功');
        }else{
            err('注册失败，稍后重试');
        }
		
	}
	public function newindex(){
		$this->assign("invitecode",I('invitecode'));
		$this->display();
	}
	/**
     * 图形验证码
     * @param  string $signcode 机器码
     * @return [type]           [description]
     */
    public function image(){
		$signcode ='regsigncode';
        $redis = ConnRedis();
        $vcode = gen_vcode(4);
        $key  = 'img:'.md5($signcode);
        $data = $redis -> set( $key , strval($vcode) , Constants::IMAGE_CODE_EXPIRE_TIME);
        getAuthImage($vcode);;
    }
	/*
	*
	*注册
	*/
	public function new_register(){
		$moblie = I('post.phone');
		$password = I('post.pwd');
		$vcode = I('post.vcode');
		$lid = I('post.invitecode');
		$authtype = I('post.authtype');
		$username = I('post.username');
		$member = D("Members");
		if($member->is_reg_vcode($moblie)){
            err("手机号已经注册");
        }
        $smscode = I('vcode/s');
        //短信验证码
        $codeModel = D('Validcode');
        if($codeModel -> expires($moblie,$smscode,Constants::SMS_REGISTER_CODE)){
            err('短信验证码错误或已失效');
        } 
		$data = array(
			'phone' => $moblie,
			'name' => $username,
			'password' => $password,
			'userlevel' => $authtype,
			'leadid' => $lid
		);
		

        if($member->webregister($data)){
            succ('注册成功');
        }else{
            err('注册失败，稍后重试');
        }
		
	}

	public function code(){
		header("Content-type:text/html;charset=utf-8");
        $list = M("validcode")->field('code')->order('codeid desc')->limit(1)->find();
		$code =$list['code'];
		echo "<h1>$code</h1>";
	}
}