<?php 
namespace Api\Controller;
use Think\Controller;
header("Content-Type:text/html;charset=utf-8");
class AdminRechargeController extends Controller{
	public function addchains(){
	    $data = I();
	    $ordersn = $data['order'];
	    $phone = $data['phone'];
	    $key = $data['key'];
	    
	    //根据手机号查询用户
	    $member = D("Members")->getMemberByPhone($phone);
	    //查询该订单号是否已充值
	    $findwhere['ordersn'] = $ordersn;
	    M()->startTrans();
	    $pass = true;
	    $findorder = D("Recharge")->get_data($findwhere);
	    if(empty($member) || !empty($findorder)){
	        $array = array(
	            'state'=>-1,
	            'order'=>$ordersn,
	        );
	        succ($array);
	    }else{
	        //加充值记录
	        $order = array(
	            "uid" => $member['id'],
	            "phone" =>$member['phone'],
	            "last_name"=>$member['name'],
	            "name"=>$member['name'],
	            "money"=>$key,
	            "ctime"=>time(),
	            "ordersn"=>$ordersn,
	            "state"=>1,
	            "remark"=>"bydr",
	            'ptime'=>time(),
	        );
	        $pass = $pass && D("Recharge")->adds($order);
	        //充值余额
	        $addbalance['balance'] = $member['balance']+$key;
	        $pass = $pass && D("Members")->modify($member['token'],$addbalance);
	        //加用户充值总额
	        $adddeposit['deposit'] = $member['deposit']+$key;
	        $pass = $pass && D("Members")->modify($member['token'],$adddeposit);
	        $log = array(
	            "uid"=>$member['id'],
	            "changeform"=>"in",
	            "subtype"=>2,
	            "money"=>$key,
	            "ctime"=>time(),
	            "describes"=>"您于".date("Y-m-d H:i",time())."充值的账户余额".$key."已到账",
	            "balance"=>$addbalance['balance'],
	            "money_type"=>2,
	        );
	        $pass = $pass && D("MembersLog")->adds($log);
	        if($pass){
	            M()->commit();
	            $array = array(
	                'state'=>1,
	                'order'=>$ordersn,
	            );
	            succ($array);
	        }else{
	            $array = array(
	                'state'=>-1,
	                'order'=>$ordersn,
	            );
	            succ($array);
	            M()->rollback();
	        }
	    }
	}
}
?>