<?php 
namespace Api\Controller;
use Common\Lib\Constants;
class FaceController extends BaseController{
    //生成二维码
    public function wallet_address(){
         err("暂未开放");
         $chain_address = D("Face")->chain_address($this->uid);
         $result = array(
             'address'=>$chain_address
         );
         succ($this->output($result));
    }
    
    /**进行交易
     * 金链钱包地址  wallet_address
     * 数量  num
     * 交易密码 paypwd
     * 转赠类型 option  1金链  2余额
     */
    public function transfer_chains(){
        err("暂未开放");
        require_check_api('wallet_address,num,paypwd,option', $this->post);
        $option = $this->post['option'];
        $num = $this->post['num'];
        //查询用户
        $userdata = D("Members")->normalMember($this->userToken);
        if(empty($userdata)) err("账户异常");
        //查询受赠用户
        $touser = D("Members")->getmemberid($this->post['wallet_address']);
        $touserinfo = D("Members")->memberid($touser['member_id']);
        
        if(empty($touserinfo)) err("受赠用户异常");
        //查询是否给自己转赠
        if($this->uid==$touserinfo['id']) err("您不能跟自己交易");
        M()->startTrans();
        $pass = true;
        if($option==1){
            //查用户金链数量
            $chain = D("Members")->member_wallet($this->uid);
            $chain_number = $chain['number'];
            if($chain_number<0 || $chain_number<$num){
                err("您的金链数量不足");
            }
            //现在用户金链数量
            $nowChain = bcsub($chain_number,$num,2);
        }elseif ($option==2){
            //查询用户余额
            $account = D("Members")->profiles($this->userToken);
            $balance = $account['balance'];
            if($balance<0 || $balance<$num){
                err("您的账户余额不足");
            }
            //现在用户余额数量
            $nowBalance = bcsub($balance,$num,2);
        }
        //核对交易密码
        if(md5password($this->post['paypwd'],$userdata['paysalt'])!= $userdata['paypwd']){
            err("交易密码错误");
        }
        if($option==1){
            //减用户金链
            $pass = $pass && D("Members")->chainByid($this->uid,$num,0,'out');
            //加用户扣链日志
            $userlog = array(
                'uid' => $this->uid,
                'changeform' => 'out',
                'subtype' => 4,
                'money_type' =>1, //1金链  2余额
                'money' => $num,
                'ctime' => time(),
                'balance' => $nowChain,
                'describes' => '面对面交易金链扣除'.$num,
                'extends'=>$touser['member_id'],
            );
            $pass = $pass && D("MembersLog")->adds($userlog);
            //加受赠用户金链
            $pass = $pass && D("Members")->chainByid($touser['member_id'],$num,0,'in');
            $to_nowChain = bcadd($touser['wallet_number'],$num,2);
            //加受赠用户日志
            $toserlog = array(
                'uid' => $touser['member_id'],
                'changeform' => 'in',
                'subtype' => 5,
                'money_type' =>1, //1金链  2余额
                'money' => $num,
                'ctime' => time(),
                'balance' => $to_nowChain,
                'describes' => '面对面交易金链增加'.$num,
                'extends'=>$this->uid,
            );
            $pass = $pass && D("MembersLog")->adds($toserlog);
            //获取当前金链价格
            $getPrice = D("TradingPrice")->getPrice();
            $price = $getPrice['price'];
            //查询受赠用户账户余额
            $reduceBalance = $num*(1+Constants::SCORE_BUY_FEE)*$price;
            if($touserinfo['balance']<$reduceBalance) err("您的账户余额不足");
            //扣受赠用户余额
            $pass = $pass && D("Members")->balance($touserinfo['token'],$reduceBalance,'out');
            //目前余额
            $to_now_balance = bcsub($touserinfo['balance'],$reduceBalance,3);
            //加受赠用户余额日志
            $reducelog = array(
                'uid' => $touser['member_id'],
                'changeform' => 'out',
                'subtype' => 6,
                'money_type' =>2, //1金链  2余额
                'money' => $reduceBalance,
                'ctime' => time(),
                'balance' => $to_now_balance,
                'describes' => '面对面交易金链扣除余额'.$reduceBalance,
                'extends'=>$this->uid,
            );
            $pass = $pass && D("MembersLog")->adds($reducelog);
            //给转赠用户加余额
            $addBalance = $num*(1-Constants::SCORE_SELL_FEE)*$price;
            $pass = $pass && D("Members")->balance($this->userToken,$addBalance,'in');
            //目前余额
            $my_now_balance = bcadd($userdata['balance'],$addBalance,3);
            //加转赠用户余额日志
            $Addlog = array(
                'uid' => $this->uid,
                'changeform' => 'in',
                'subtype' => 7,
                'money_type' =>2, //1金链  2余额
                'money' => $addBalance,
                'ctime' => time(),
                'balance' => $my_now_balance,
                'describes' => '面对面交易金链增加余额'.$addBalance,
                'extends'=>$touser['member_id'],
            );
            $pass = $pass && D("MembersLog")->adds($Addlog);
            //给系统给用户加面对面手续费
            //给提现手续费
            $facemember = D("Members")->findAdminuser(Constants::FACE_UID);
            $facetoken = $facemember['token'];
            $fee = (Constants::SCORE_SELL_FEE+Constants::SCORE_BUY_FEE)*$num*$price;
            $pass = $pass && D("Members")->balance($facetoken,$fee,'in');
        }elseif ($option==2){
            //减用户余额
            $pass = $pass && D("Members")->balance($this->userToken,$num,'out');
            //加用户日志
            $userlog = array(
                'uid' => $this->uid,
                'changeform' => 'out',
                'subtype' => 6,
                'money_type' =>2, //1金链  2余额
                'money' => $num,
                'ctime' => time(),
                'balance' => $nowBalance,
                'describes' => '面对面转赠余额'.$num,
                'extends'=>$touser['member_id'],
            );
            $pass = $pass && D("MembersLog")->adds($userlog);
            //加受赠用户余额
            $pass = $pass && D("Members")->balance($touserinfo['token'],$num,'in');
            $to_nowBalance = bcadd($touserinfo['balance'],$num,2);
            //加受赠用户日志
            $toserlog = array(
                'uid' => $touser['member_id'],
                'changeform' => 'in',
                'subtype' => 7,
                'money_type' =>2, //1金链  2余额
                'money' => $num,
                'ctime' => time(),
                'balance' => $to_nowBalance,
                'describes' => '面对面受赠余额'.$num,
                'extends'=>$this->uid,
            );
            $pass = $pass && D("MembersLog")->adds($toserlog);
        }
        if($pass){
            M()->commit();
            succ("操作成功");
        }else{
            M()->rollback();
            err("操作失败");
        }
    }
}
?>