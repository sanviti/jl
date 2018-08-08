<?php 
namespace Api\Controller;
use Common\Lib\Constants;
//use Think\Controller;
class RechargeController extends BaseController{
    /**
     * 充值
     * last_name  汇款人姓氏
     * name       名字
     * cardid     汇款人卡号id
     * money      充值金额
     */
    public function recharge_one(){
        require_check_api("last_name,name,cardid,money",$this->post);
        $last_name = $this->post['last_name']; 
        $name = $this->post['name']; 
        $cardid = $this->post['cardid']; //卡号id
        $money = $this->post['money']; //充值金额
        
        //查询用户是否正常
        $member = D("Members")->profiles($this->userToken);
        if(empty($member)) err("用户不存在");
        if($member['is_lock']==1) err("账户锁定");
        if($member['isfreeze']==1) err("账户冻结");
        
        //查询银行卡
        $condi['uid'] = $this->uid;
        $condi['cardid'] = $cardid;
        $bank = D("Bank")->get_bankinfo($condi);
        if(empty($bank)) err("银行卡不存在");        
        
        //查询公司充值的卡
        $com_bank = D("ComBank")->com_bank();
        
        $data = array(
            "last_name"=>$last_name, //姓氏
            "name"=>$name,  //名字
            "cardid"=>$cardid,
            "money"=>$money, 
            "inid" =>$com_bank['id'],
            "inaccount"=>$com_bank['card'],
            "inbank"=>$com_bank['bankname'],
            "insubbank"=>$com_bank['subbank'],
            "inname"=>$com_bank['truename'],
        );
        //succ($data);
        succ($this->output($data));
    }
    
    /** 
     * inaccount  汇入银行账户
     * inbank     汇入银行
     * inname     收款人
     **/
    public function recharge_two(){
        require_check_api("last_name,name,cardid,money,com_bankid",$this->post);
        $last_name = $this->post['last_name'];//姓氏
        $name = $this->post['name'];//姓名
        $cardid = $this->post['cardid']; //卡号id
        $money = $this->post['money']; //充值金额
        $com_bankid = $this->post['com_bankid']; //公司充值账号
        
        //查询用户是否正常
        $member = D("Members")->profiles($this->userToken);
        if(empty($member)) err("用户不存在");
        if($member['is_lock']==1) err("账户锁定");
        
        //查询银行卡
        $condi['uid'] = $this->uid;
        $condi['cardid'] = $cardid;
        $bank = D("Bank")->get_bankinfo($condi);
        if(empty($bank)) err("银行卡不存在");
        
        //$usdc = number_format($money/Constants::USDC_PRICE,3);
        $ordersn = getOrderSn();
        $data = array(
            "last_name"=>$last_name,
            "name"=>$name,
            "cardid"=>$cardid,
            "money"=>$money,
            "com_bankid"=>$com_bankid,
            "ordersn"=>$ordersn,
            "ctime"=>time(),
            "uid"=>$this->uid,
        );
        $rtn = D("Recharge")->adds($data);
        if($rtn){
            succ("充值成功");
        }else{
            err("充值失败");
        }
    }
}

?>