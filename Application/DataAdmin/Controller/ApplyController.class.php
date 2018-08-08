<?php 
namespace DataAdmin\Controller;
use Common\Lib\Constants;
header("Content-Type:text/html;charset=utf-8");
class ApplyController extends  BaseController{
    /**
     * 提现列表
     */
    public function lists(){
        $userid = I("userid");
        $rname = I("rname");
        $state = I("state");
        $role = I("role");
        $btime = I("btime");
        $etime = I("etime");
        $ctime = I("ctime");
        $condi = array();
        if($userid!=""){
            $condi['m.userid'] = $userid; 
        }
        if($rname!=""){
            $condi['m.name'] = $rname;
        }
        if($state!=""){
            $condi['a.state'] = $state;
        }
        if($role!=""){
            $condi['a.role'] = $role;
        }
        if($btime!=""){
            $condi['a.ctime'] = array("EGT",strtotime($btime));
        }
        if($etime!=""){
            $condi['a.ctime'] = array("ELT",strtotime($etime));
        }
        if($btime!="" && $etime!=""){
            $condi['a.ctime'] = array(array("EGT",strtotime($btime)),array("ELT",strtotime($etime)));
        }
        if($ctime!=""){
            $condi['a.ptime'] = array("EGT",strtotime($ctime));
        }
        $page = I("p");
        $lists = D("Apply")->lists($condi,$page,'10','a.ctime DESC');
        //公司账户
        $c_bank = D("Apply")->company_bank();
        $count = D("Apply")->getcount($condi);
        $p = getpage($count, C('PAGE_SIZE'),$condi);
        $show = $p->newshow();
        $this->assign("list",$lists);
        $this->assign("bank",$c_bank);
        $this->assign("page",$show);
        $this->display();
    }
    
    /**
     * 提现明细
     */
    public function process(){
        if($err = admin_require_check('id')) $this->error($err);
        $id = I("id");
        //查找该记录
        $info = D("Apply")->process($id);
        if(IS_POST){
            //查询该记录
            $upd_condi['id'] = $id;
            $field = array('money,uid,ctime,account');
            if(!$applycashData = D("Apply")->getinfo($field,$upd_condi)) {
                $this->error("操作失败[不存在的记录]");
            }
            //开启日志
            M()->startTrans();
            $result = true; 
                       
            //处理记录
            $state = I('state');
            $data['mgrid'] = session("dataAdmin.id");
            $data['ptime'] = time();
            $data['pname'] = '平台处理';
            $data['state'] = $state;
            $data['remark'] = I('remark');

            //修改记录
            $result = $result && D("Apply")->to_apply($upd_condi,$data);
            
            //操作日志
            $actLog = array(
                 'applycashid'=>$id,
                 'reason'=>$data['remark'],
                 'ctime'=>time(),
                 'admin_id'=>session("dataAdmin.id") 
            );
            if($state == 1) { 
                //标识已打款
                $message = array(
                    'uid' => $info['uid'],
                    'title' => '提现成功',
                    'content' => '您于' . date('Y-m-d', $info['ctime']) .'申请的提现'. $info['money'].'系统已处理成功',
                    'staus' => 0, 'type' => 2, 'ctime' => time()
                );
                $result = $result && M('message')->add($message);
                //操作日志
                $actLog['action_type'] = 1;
                $result = $result && D('Apply')->add_action($actLog);
                //memer加提现总额
                //$buy_back = number_format($info['money']/Constants::USDC_PRICE,3);
                $buy_back = $info['money'];
                $result = $result && D('Members')->buy_back($info['uid'],$buy_back);
                
            }elseif($state==-1) { 
                //拒绝
                $merber = D("Members")->profiles($info['uid']);
                //退款金额
                $nowUserMoney = $merber['balance'] + $info['money'];
                
                //回购失败日志
                $logdata = array(
                    'uid' => $info['uid'],
                    'changeform' => 'in',
                    'subtype' => 8,
                    'money' => $info['money'],
                    'ctime' => time(),
                    'describes' => '提现失败,已退回到您的账户,申请回购数量'.$info['money'],
                    'balance' => $nowUserMoney,
                    'money_type'=>2,
                );
                $result = $result && D("MembersLog")->addlog($logdata); 
                
                //操作日志
                $actLog['action_type'] = 2;
                $result = $result && D('Apply')->add_action($actLog);

                //回购失败消息
                $message = array(
                    'uid' => $info['uid'], 
                    'title' => '提现失败',
                    'content' => '您于' . date('Y-m-d', $info['ctime']) .'申请的提现'. $info['money'].'，已被拒绝，拒绝原因：'.$data['remark'],
                    'staus' => 0, 
                    'type' => 2, 
                    'ctime' => NOW_TIME
                );
                $result = $result && M('message')->add($message);

                //当前用户款项回流
                $where = array("id"=>$info['uid']);
                $nowBalance['balance'] = $nowUserMoney;
                $result = $result && D("Members")->save_user($where,$nowBalance);
                
            }
            if($result){
                M()->commit();
                /* $cofig = push_configs();
                $word = '您于' . date('Y-m-d', $data['dateline']) .'申请的回购'. $data['num'] . '颗'.$moneyTypeName.'，系统已处理,请及时查看。';
                if ($data['equipmentid']){
                    sendPushspecific($data['equipmentid'],$word,$cofig[7]);
                } */
                $this->success("操作成功");
            }else{
                M()->rollback();
                $this->error("操作失败");
            }
            exit;
       }
           $this->assign("data",$info);
           $this->display();
    }
}
?>