<?php
/**
 * 手机端用户模型
 */
namespace Api\Model;
use Think\Model;
class MembersModel extends Model{
    protected $tableName = 'members';
    protected $tablePrefix = 'jl_';
    protected $userToken = '';
    private $retain = array(8,88,888,88888,888888,1,9,99,999,999999,99999,6,66,666,66666,666666,6888,100,110);
    public function register($data){
        //生成新的密码和salt
        list($pwd, $salt) = md5password($data['password']);
        $data['password'] = $pwd;
        $data['salt'] = $salt;
		//生成支付密码和paysalt
		list($paypwd, $paysalt) = md5password($data['paypwd']);
		$data['paypwd'] = $paypwd;
        $data['paysalt'] = $paysalt;

        $data['leadid'] = $data['leadid'] ? $data['leadid'] : 5;
        $data['teamid'] = $data['teamid'] ? $data['teamid'] : 0;
        $data['reg_time'] = NOW_TIME;
		if($data['userlevel']==1){
			$data['iscertify'] = 1;
		}
        
        $this->startTrans();
        $reuslt = $userid = $this->add($data);

        $upd['token']  = $this->_create_token();
        //$upd['invite_qrcode']  = '';
        $upd['userid'] = 'JL'.sprintf('%06d',$userid);
        $result = $this->where(array('id' => $userid))->save($upd);

		//创建钱包地址
		 $wallet= array(
            'member_id'=>$userid,
            'wallet_type'=>'1',
            'wallet_address'=>'jl'.$this->_create_wallet_address()
            ); 
		$result = M('wallet')->add($wallet);
		
        if($result){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }
    /**
     * 登录
     * @param  [type] $phone    [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function checklogin($phone, $password ){
        $error = 0;
        $condi['phone'] = $phone;
        $field = "id, password, is_lock, salt, paypwd, token";
        $userdata = $this->field($field)->where($condi)->find();
        //用户名不存在
        if($userdata == NULL){
            $error = -1;
        }
        //密码错误
        elseif(md5password($password,$userdata['salt']) != $userdata['password']){
            $error = -2;
        }
        //账号锁定
        elseif($userdata['is_lock'] != 0){
            $error = -3;
        }
		
        //登录成功
        if($error == 0){
            $this->userToken = $userdata['token'];
			
        }
        return $error;
    }
	public function getTokenByPhone($phone){
        $condi['phone'] = $phone;
        $field = "token";
        $userdata = $this->field($field)->where($condi)->find();
        return $userdata['token'];
    }
    
    public function getMemberByPhone($phone){
        $condi['phone'] = $phone;
        $userdata = $this->field("*")->where($condi)->find();
        return $userdata;
    }
    
    /**
     * 创建token
     * @return [type] [description]
     */
    private function _create_token(){
        $status = 0;
        while($status == 0){
            $token = $this->_str_rand(24);
            $count = $this->where(array('token' => $token))->count();
            if($count == 0){
                $status = 1;
            }
        }
        return $token;
    }

	/**
     * 创建钱包地址
     * @return [type] [description]
     */
    private function _create_wallet_address(){
        $status = 0;
        while($status == 0){
            $token = $this->_str_rand(40);
            $count = $this->where(array('token' => $token))->count();
            if($count == 0){
                $status = 1;
            }
        }
        return $token;
    }
	
    /*
     * 生成随机字符串
     * @param int $length 生成随机字符串的长度
     * @param string $char 组成随机字符串的字符串
     * @return string $string 生成的随机字符串
     */
    private function _str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
        if(!is_int($length) || $length < 0) {
            return false;
        }
        $string = '';
        for($i = $length; $i > 0; $i--) {
            $string .= $char[mt_rand(0, strlen($char) - 1)];
        }
        return $string;
    }
    

    /**
     * 获取当前用户token
     * @return [type] [description]
     */
    public function token(){
        return $this->userToken;
    }


    /**
     * 账户注册检测
     * @param  string  $phone 手机号
     * @param  string  $authtype 1 普通用户 2 社区用户
     * @return boolean        [description]
     */
    public function is_reg($phone,$authtype){
        $condi = array('phone' => $phone,'userlevel'=>$authtype);
        $count = $this->where($condi)->count();
        return $count ? true : false;
    }
    /**
     * 账户注册检测
     * @param  string  $phone 手机号
     * @param  string  $authtype 1 普通用户 2 社区用户
     * @return boolean        [description]
     */
    public function is_reg_vcode($phone){
        $condi = array('phone' => $phone);
        $count = $this->where($condi)->count();
        return $count ? true : false;
    }
    /**
     * 用户信息 token
     * @param  [type] $token [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function profiles($token, $field = '*'){
        return $this->field($field)->where(array('token' => $token))->find();
    }

	/**
     * 更新用户等级
     * @param  [type] $token   [description]
     * @param  [type] $level [description]
     * @return [type]        [description]
     */
    public function memberLevel($token, $level){
         return $this->where(array('token' => $token))->save(array('userlevel'=>$level));
    }


    /**
     * 用户余额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function balance($token, $num, $act = 'in'){
        $condi = array('token' => $token);
        if($act == 'in'){
            return $this->where($condi)->setInc("balance",$num);            
        }else{
            return $this->where($condi)->setDec("balance",$num);
        }
    }

    /**
     * 用户充值总额变动
     */
    public function deposit($token, $num, $act = 'in'){
        $condi = array('token' => $token);
        if($act == 'in'){
            return $this->where($condi)->setInc("deposit",$num);
        }else{
            return $this->where($condi)->setDec("deposit",$num);
        }
    }
    
    /**
     * 用户锁定余额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function balance_lock($token, $num, $act = 'in'){
        $condi = array('token' => $token);
        if($act == 'in'){
            return $this->where($condi)->setInc("balance_lock",$num);            
        }else{
            return $this->where($condi)->setDec("balance_lock",$num);
        }
    }

    /**
     * 查找正常用户
     * @param $where 搜索条件
     */
    public function normalMember($token, $fields = '*'){
        $condi['token'] = $token;
        $condi['is_lock'] = 0;
        $condi['isfreeze'] = 0;
        $res = $this->field($fields)->where($condi)->find();
        return $res;
    }
    
    //我的金链资产
    public function member_wallet($uid){
        $wallet = M('wallet')->field('wallet_number')->where(array('member_id' => $uid))->find();
        $data = array(
            'number'=>$wallet['wallet_number'],//我的资产
        );
        return $data;
    }

    /**
     * 修改用户数据
     * @param  [type] $token [description]
     * @param  [type] $data  [description]
     * @return [type]        [description]
     */
    public function modify($token, $data){
        $condi = array('token' => $token);
        return $this->where($condi)->save($data);
    }

	
    /**
     * 用户金链变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function chainByid($id,$num,$lock=0,$act='in'){
        $condi = array('member_id' => $id);
        $fieldName = $lock == 0 ? 'wallet_number' : 'wallet_lock';
        if($act == 'in'){
            return  M("wallet")->where($condi)->setInc($fieldName, $num);            
        }else{
            return  M("wallet")->where($condi)->setDec($fieldName, $num);
        }
    }
    
		 /**
     * 账户token检测
     * @param  string  $token 用户token
     * @return boolean        [description]
     */
    public function isexists_token(){
        $condi = array('token' => $token);
        $count = $this->where($condi)->count();
        return $count ? true : false;
    }

	public function getUidById($id){
        $condi['userid'] = $id;
        $res = $this->field('id')->where($condi)->find();
        return $res['id'];
    }
	//身份证上传
	public function uploadImg($img){
		$image = base64_decode($img);
		$time = time();
		$img_name = md5(rand(0,9999).microtime());
		$save_path = './public/images/user';
		$today = strval($save_path.'/'.date('ymd',$time));
		if(!is_dir($save_path)){
			mkdir($save_path,0777);
		}
		if(!is_dir($today)){
			mkdir($today,0777);
		}
	   file_put_contents($today.'/'.$img_name.'.jpg',$image);
	   $url = '/public/images/user/'.date('ymd',$time).'/'.$img_name.'.jpg';
	   return $url;
	}
	//非 App 注册提交身份证实名
	public function save_idno($data){
		$res = M('members')->where(array('token' => $data['token'],'id_lock' => 0))->save($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}

	//提交身份证照片
	public function save_certify($data){
		
		$data['idno_front_img'] = $data['frontimg'];
		$data['idno_back_img'] = $data['backimg'];
		$data['idno_hand_img'] = $data['handimg'];
		
		$data['update_time'] = time();
		$res = M('members')->where(array('token' => $data['token']))->save($data);
		$data = $this->field($field)->where(array('token' => $data['token']))->find();
		if($res){
			return $data;
		}else{
			return false;
		}
	}
	//修改身份证认证
	public function correct_certify($data){
		$res = M('members')->where(array('token' => $data['token']))->save($data);
		if($res){
			return true;
		}else{
			return false;
		}
	}
	//web注册
	public function webregister($data){
        //生成新的密码和salt
        list($pwd, $salt) = md5password($data['password']);
        $data['password'] = $pwd;
        $data['salt'] = $salt;
		$condi['userid'] = $data['leadid'];
        $res = $this->field('id,userlevel,leadid,teamid')->where($condi)->find();
		if($res['userlevel']==2){
			$data['leadid'] = $res['id'];
			$data['teamid'] = $res['id'];
		}else{
			$data['leadid'] = $res['id'];
			$data['teamid'] = $res['teamid'];
			
		}
		
		//社区领导人id
		
        $data['reg_time'] = NOW_TIME;
        $this->startTrans();
        $reuslt = $userid = $this->add($data);

        $upd['token']  = $this->_create_token();
        $upd['userid'] = 'JL'.sprintf('%06d',$userid);
        $result = $this->where(array('id' => $userid))->save($upd);

		//创建钱包地址
		 $wallet= array(
            'member_id'=>$userid,
            'wallet_type'=>'1',
            'wallet_address'=>'jl'.$this->_create_wallet_address()
            ); 
		$result = M('wallet')->add($wallet);
		
        if($result){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }
    //我的金链
    public function my_gold_block($token){
       $member = $this->field('id,deposit,balance,chonglian,lprice')->where(array('token' => $token))->find();
       $wallet = M('wallet')->field('wallet_address,wallet_number,wallet_lock')->where(array('member_id' => $member['id']))->find();
       $now_price = D('TradingPrice')->getPrice();
       $trading_buy = M('trading_succ')->field('price,num')->where(array('buy_uid' => $member['id']))->order('id desc')->select();
    
       $total_pay = 0;//充值花的钱
       $totalNumber=0;
       foreach ($trading_buy as $k => $v){
          $totalNumber=bcadd($totalNumber,$v['num'],2);
          $buyMoney=$v['price']*$v['num'];
          $total_pay=bcadd($total_pay,$buyMoney,3);
       }
       if(intval($member['chonglian'])>0){
          $totalNumber=bcadd($totalNumber,$member['chonglian'],2);
          $ico_cost = $member['chonglian'] * $member['lprice'];
          $total_pay=bcadd($total_pay,$ico_cost,3);
       }
    
       $total_cost =$total_pay; //总成本
       $nowTotalMoney=round($now_price['price'] * $totalNumber,3);
       $total_money =bcsub($nowTotalMoney,$total_cost,3);//总盈亏
       
       $data = array(
               'total_cost'=>$total_cost,//总成本
               'now_price'=>$now_price['price'],//当前价格
               'wallet_number'=>$wallet['wallet_number'],//拥有金链数
               'wallet_lock'=>$wallet['wallet_lock'],//冻结金链数
               'total_money'=>$total_money,//总盈亏
          'key_number'=>$member['balance'],//我的资产KEY
           );
       
       
       return $data;
    }

	//我的金链流水
	public function billFlow($token,$page){
		$member = $this->field('id')->where(array('token' => $token))->find();
		$uid = $member['id'];
		$condi['_string'] = "buy_uid='$uid' or sell_uid = '$uid'";
		$data= M("trading_succ")->field('ctime,num,buy_uid,sell_uid')->where($condi)->page($page)->limit(10)->order("ctime desc")->select();
		
		$cond['_string'] = "uid='$uid' and money_type =1 and (subtype=4 or subtype=5 or subtype=3)";//4用户转赠金链  5受赠金链 3充值金链
		$record= M('user_log')->field('ctime,money,changeform,subtype')->where($cond)->page($page)->limit(10)->order("ctime desc")->select();
		
		for($v=0;$v<count($record);$v++){
			//$record[$v]['ctime'] = date("m-d",$record[$v]['ctime']);
		      if($record[$v]['subtype']== 4){
				$record[$v]['title'] = "转赠金链";
				if($record[$v]['changeform']=="in"){
					$record[$v]['num'] = "+".$record[$v]['money'];
				}else{
					$record[$v]['num'] = "-".$record[$v]['money'];
				}
				
			 }if($record[$v]['subtype']== 3){
				$record[$v]['title'] = "充值金链";
				if($record[$v]['changeform']=="in"){
					$record[$v]['num'] = "+".$record[$v]['money'];
				}else{
					$record[$v]['num'] = "-".$record[$v]['money'];
				}
			 }if($record[$v]['subtype']== 5){
				$record[$v]['title'] = "受赠金链";
				if($record[$v]['changeform']=="in"){
					$record[$v]['num'] = "+".$record[$v]['money'];
				}else{
					$record[$v]['num'] = "-".$record[$v]['money'];
				}
			}
			unset($record[$v]['changeform']);
		}
		
		for($v=0;$v<count($data);$v++){
			//$data[$v]['ctime'] = date("m-d",$data[$v]['ctime']);
			if($data[$v]['buy_uid']== $uid){
				$data[$v]['title'] = "买入";
				$data[$v]['num'] = "+".$data[$v]['num'];
			}
			else{
				$data[$v]['title'] = "卖出";
				$data[$v]['num'] = "-".$data[$v]['num'];
			}
			unset($data[$v]['buy_uid']);
			unset($data[$v]['sell_uid']);
		}
		$data = array_merge($record,$data);
		$ctime = array();  
		foreach ($data as $da) {  
		  $ctime[] = $da['ctime'];  
		}
		
		array_multisort($ctime, SORT_DESC, $data);
		
		for($v=0;$v<count($data);$v++){
			$data[$v]['ctime'] = date("m-d",$data[$v]['ctime']);
		}
		$data = array(
            'list' => $data
        );
       
		
		return $data;
		
	}
	//我的钱包
	public function wallet($token){
		$member = $this->field('id,balance')->where(array('token' => $token))->find();
		$wallet = M('wallet')->field('wallet_number')->where(array('member_id' => $member['id']))->find();
		
		$data = array(
            'key_number'=>$member['balance'],//我的资产
        );
		
		return $data;
	}
	

	//我的资产明细
	//1余额提现    2充值余额   3充值金链   4用户转赠金链  5受赠金链 6用户转赠余额 7受赠余额 8余额提现失败 9交易
	public function wallet_bill($token,$page){
		$member = $this->field('id')->where(array('token' => $token))->find();
		$uid = $member['id'];
		//$condi['_string'] = "uid='$uid' and (subtype=1 or subtype=4 or subtype=5 or subtype=6 or subtype=7 or subtype=9 or subtype=2 or subtype=3) and money_type='2'";
		$condi['uid'] = $uid;
		$condi['money_type'] = 2;
		$record= M('user_log')->field('ctime,money,changeform,subtype,money_type')->where($condi)->page($page)->limit(10)->order("ctime desc")->select();
			foreach ($record as $a => $b){
				$record[$a]['ctime'] = date("m-d",$record[$a]['ctime']);
				if(!$record[$a]['money']){
					unset($record[$a]);
				}
				if($record[$a]['subtype'] == 1){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="余额提现";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="余额提现";
						$record[$a]['money'] = "-".$record[$a]['money'];	
					}
				}
				if($record[$a]['subtype'] == 6){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="转赠余额";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="转赠余额";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
				}
				if($record[$a]['subtype'] == 7){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="受赠余额";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="受赠余额";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
				}
				if($record[$a]['subtype'] == 4){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="用户转赠金链";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="用户转赠金链";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}
				if($record[$a]['subtype'] == 5){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="受赠金链";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="受赠金链";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
				}if($record[$a]['subtype'] == 9){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="买卖交易";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="买卖交易";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}if($record[$a]['subtype'] == 2){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="充值余额";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="充值余额";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}if($record[$a]['subtype'] == 3){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="充值金链";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="充值金链";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}if($record[$a]['subtype'] == 11){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="个人推荐";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="个人推荐";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}if($record[$a]['subtype'] == 12){
					if($record[$a]['changeform']=="in"){
						$record[$a]['title'] ="初级社区推荐";
						$record[$a]['money'] = "+".$record[$a]['money'];
					}else{
						$record[$a]['title'] ="初级社区推荐";
						$record[$a]['money'] = "-".$record[$a]['money'];
					}
					
				}
				
				unset($record[$a]['changeform']);
				unset($record[$a]['subtype']);
				unset($record[$a]['money_type']);
			}
		
		
		$data = array(
            'list'=>$record
        );
		
		return $data;
	}
	
	//邀请码
	public function getInviteCode($mobile){
        $condi['phone'] = $mobile;
		if($condi['phone']==""){
			$invitecode['leadid'] = 5;
			$invitecode['teamid'] = 0;
		}else{
			$res = $this->field('id,userlevel,leadid,teamid')->where($condi)->find();
			
			if($res['userlevel']==2){
				$invitecode['leadid'] = $res['id'];
				$invitecode['teamid'] = $res['id'];
			}else{
				$invitecode['leadid'] = $res['id'];
				$invitecode['teamid'] = $res['teamid'];
			}
		}
        return $invitecode;
    }
    
    //通过钱包地址查找用户id
    public function getmemberid($wallet){
        $member = M("wallet")->field("member_id,wallet_number")->where(array("wallet_address"=>$wallet))->find();
        return $member;
    }
    
    //通过用户id查找用户
    public function memberid($uid,$fields = '*'){
        $member = $this->field($fields)->where(array("id"=>$uid,"is_lock"=>0,"isfreeze"=>0))->find();
        return $member;
    }

	//通过ID查找撤单用户
	public function memberRecallId($uid,$fields = '*'){
		$member = $this->field($fields)->where(array("id"=>$uid))->find();
		return $member;
	}
    
    //通过用户id查找系统用户
    public function findAdminuser($uid){
        $member = $this->field("id,token,balance")->where(array("id"=>$uid))->find();
        return $member;
    }
}