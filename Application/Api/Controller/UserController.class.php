<?php
/**
 * 用户登录/注册
 */
namespace Api\Controller;
use Think\Log;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;

class UserController extends BaseController{

	   /**
     * 修改登录密码
	 * @param  string $mobile 手机号
	 * @param  string $password md5密码
	 * @param  string $vcode 短信验证码
	 * @param  string $token 用户token
     * @return [type] [description]
     */
    public function findPwd(){
		require_check_api("vcode", $this->post);
        $vcode = addslashes($this->post['vcode']);
        $password = addslashes($this->post['password']);
        $mobile = addslashes($this->post['mobile']);
        
        $model  = D("Members");

        //验证码检测
        $codeModel = D('Validcode');
        if($codeModel -> expires($mobile,$vcode,Constants::SMS_FINDPWD_CODE)){
            err('短信验证码错误或已失效');
        }

        //重置密码
        list($pwd, $salt) = md5password($password);
        $data['password'] = $pwd;
        $data['salt'] = $salt;
        $result = $model->where(array('token' => $this->userToken))->save($data);
        if($result){
            succ('重置密码成功');
        }else{
            err('操作失败');
        }
    }
	
	/**
     * 修改交易密码
	 * @param  string $mobile 手机号
	 * @param  string $paypwd 交易密码
	 * @param  string $vcode 短信验证码
	 * @param  string $token 用户token
     * @return [type] [description]
     */
    public function findPayPwd(){
		require_check_api("vcode,password,mobile", $this->post);
        $vcode = addslashes($this->post['vcode']);
        $password = addslashes($this->post['password']);
        $mobile = addslashes($this->post['mobile']);
		
        $model  = D("Members");


        //验证码检测
        $codeModel = D('Validcode');
        if($codeModel -> expires($mobile,$vcode,Constants::SMS_FINDPWD_CODE)){
            err('短信验证码错误或已失效');
        }

        //重置密码
        list($pwd, $salt) = md5password($password);
        $data['paypwd'] = $pwd;
        $data['paysalt'] = $salt;
        $result = $model->where(array('token' => $this->userToken))->save($data);
        if($result){
            succ('重置密码成功');
        }else{
            err('操作失败');
        }
    }
	
	//上传图片
	public function uploadimg(){
        $upload = new \Think\Upload();// 实例化上传类
        $key = array_shift(array_keys($_FILES));
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->savePath = '/images/user/';
        $info=$upload->upload();
        $path = "/Uploads".$info[$key]['savepath'].''.$info[$key]['savename'];

        $data = array(
            'path'=>$path,
        );
        succ($this->output($data));
    }
	
	 /**
	 * 社区账户实名认证
	 * @param  string  $frontimg 身份证正面照
	 * @param  string  $backimg 身份证反面面照
	 * @param  string  $handimg 身份证手持签名照
	 * @return boolean        [description]
	 */
    public function certify(){
        $member  = D("Members");
		require_check_api("name,idno,frontimg,backimg,handimg", $this->post);
        $name = addslashes($this->post['name']);
        $idno = addslashes($this->post['idno']);
        $frontimg = addslashes($this->post['frontimg']);
        $backimg = addslashes($this->post['backimg']);
        $handimg = addslashes($this->post['handimg']); 

		
        $data = array(
            'token' => $this->userToken,
            'name' => $name,
            'idno' => $idno,
            'frontimg' => $frontimg,
            'backimg' => $backimg,
            'handimg' => $handimg,
            'iscertify' => '2'
			
        );

        if($member->save_certify($data)){
			$info = $member->profiles($this->userToken);
			$userinfo = array(
				'name' => $info['name'],
				'mobile' => $info['phone'],
				'authtype' => $info['userlevel'],
				'iscertify' => $info['iscertify']
			);
            succ($this->output($userinfo));
        }else{
            err('失败，稍后重试');
        }
    }
	 /**
	 * 添加或更正账户身份证号
	 * @param  string  $idno 身份证号码
	 * @return boolean        [description]
	 */
	public function correct_certify(){
        $member  = D("Members");
		require_check_api("name,idno", $this->post);
        $name = addslashes($this->post['name']);
        $idno = addslashes($this->post['idno']);
		$data = array(
            'token' => $this->userToken,
            'name' => $name,
            'idno' => $idno,
			'iscertify' => '1'
        );
		
		if($member->correct_certify($data)){
			$info = $member->profiles($this->userToken);
			$userinfo = array(
				'name' => $info['name'],
				'mobile' => $info['phone'],
				'authtype' => $info['userlevel'],
				'iscertify' => $info['iscertify']
			);
            succ($this->output($userinfo));
        }else{
            err('修改失败，稍后重试');
        }
	}
	
	/**
	 * 我的信息
	 * @param  string  
	 * @return boolean        [description]
	 */
	public function mycertify(){
        $member  = D("Members");
		$info = $member->profiles($this->userToken);
		
		if($info){
			$data['iscertify'] = $info['iscertify'];
            succ($this->output($data));
        }else{
            err('查找失败，稍后重试');
        }
	}
	
	//我的金链
	public function myKey(){
		$member  = D("Members");
		$data = $member->my_gold_block($this->userToken);
        succ($this->output($data));
	}
	
	//金链明细
	public function keyBill(){
		require_check_api("p", $this->post);
        $page = addslashes($this->post['p']);
		
		$member  = D("Members");
		$data = $member->billFlow($this->userToken,$page);
        succ($this->output($data));
        //succ($data);
	}
	//钱包-我的资产
	public function myWallet(){
		$member  = D("Members");
		$data = $member->wallet($this->userToken);
        succ($this->output($data));
	}
	//钱包-我的资产明细
	public function walletBill(){
		require_check_api("p", $this->post);
        $page = addslashes($this->post['p']);
		$member  = D("Members");
		$data = $member->wallet_bill($this->userToken,$page);
        succ($this->output($data));
	}

	
}