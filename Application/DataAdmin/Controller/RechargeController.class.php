<?php 
namespace DataAdmin\Controller;
header("Content-Type:text/html;charset=utf-8");
class RechargeController extends BaseController{
    /**
           充值列表
    */
    public function lists(){
        $page = I("p");
        $userid = I("userid");
        $name = I("name");
        $state = I("state");
        $btime = I("btime");
        $etime = I("etime");
        $phone = I("phone");
        $condi = array();
        if($userid!=""){
            $condi['userid'] = $userid;
        }
        if($state!=""){
            $condi['state'] = $state;
        }
        if($name!=""){
            $condi['r.name'] = $name;
        }
        if($phone!=""){
            $condi['r.phone'] = $phone;
        }
        if($btime!=""){
            $condi['ctime'] = array("EGT",strtotime($btime));
        }
        if($etime!=""){
            $condi['ctime'] = array("ELT",strtotime($etime));
        }
        if($btime!="" && $etime!=""){
            $condi['ctime'] = array(array("EGT",strtotime($btime)),array("ELT",strtotime($etime)));
        }
        $page = I("p");
        $count = D("Recharge")->counts($condi);
        $p = getpage($count, C('PAGE_SIZE'),$condi);
        $show = $p->newshow();
        $data = D("Recharge")->lists($condi,$page,C('PAGE_SIZE'),'ctime DESC');
        //查询充值总额
        $summoney = D("Recharge")->getrechargesum();
        if($summoney==""){
            $summoney=0;
        }
        $this->assign("summoney",$summoney);
        $this->assign("list",$data);
        $this->assign("page",$show);
        $this->display();
    }
    
    /**
     * 充值详情
     */
    public function detail(){
        $id = I("id");
        $info = D("Recharge")->detail($id);
        $this->assign("data",$info);
        $this->display();
    }
    
    /**
     * 确认充值
     */    
    public function sureorder(){
        admin_require_check("orderid");
        $orderid = I("orderid");
        $info = D("Recharge")->detail($orderid);
        $member = D("Members")->profiles($info['uid']);
        if(empty($info)){
            err("该订单不存在");
        }else{
            if($info['state']!="0"){
                err("订单状态不符");
            }
            $pass = true;
            M()->startTrans();
            $where['id'] = $orderid;
            //改状态
            $pass = $pass && D("Recharge")->editstatus($where,array("state"=>1,"ptime"=>time()));
            //加日志
            $log = array(
                "uid"=>$info['uid'],
                "changeform"=>"in",
                "subtype"=>2,
                "money"=>$info['money'],
                "ctime"=>time(),
                "describes"=>"您于".date("Y-m-d H:i",$info['ctime'])."充值的账户余额".$info['money']."已到账",
                "balance"=>$member['balance']+$info['money'],
                "money_type"=>2,
            );
            $pass = $pass && D("MembersLog")->addlog($log);
            //改账户充值
            $pass = $pass && D("Members")->deposit($info['uid'],$info['money']);
            //充余额
            $pass = $pass && D("Members")->balance($info['uid'],$info['money']);
            if($pass){
                M()->commit();
                succ("充值成功");
            }else{
                M()->rollback();
                err("充值失败");
            }
        }
    }
    
    /**
     * 拒绝充值
     */
    public function refuseorder(){
        admin_require_check("orderid","remark");
        $orderid = I("orderid");
        $remark = I("remark");
        $info = D("Recharge")->detail($orderid);
        if(empty($info)){
            err("该订单不存在");
        }else{
            if($info['state']!="0"){
                err("订单状态不符");
            }
            if($remark==""){
                err("请填写拒绝原因");
            }
            //改状态
            $where['id'] = $orderid;
            $rtn = D("Recharge")->editstatus($where,array("state"=>-1,"ptime"=>time(),"remark"=>$remark));
            if($rtn){
                succ("操作成功");
            }else{
                err("操作失败");
            }
        }
    }
}
?>