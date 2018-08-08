<?php 
namespace DataAdmin\Controller;
class AddchainController extends BaseController{
    //充值金链列表
    public function lists(){
        $userid = I("userid");
        $rname = I("rname");
        $role = I("role");
        $btime = I("btime");
        $etime = I("etime");
        $condi = array();
        if($userid!=""){
            $condi['m.userid'] = $userid;
        }
        if($rname!=""){
            $condi['m.name'] = $rname;
        }
        if($role!=""){
            $condi['role'] = $role;
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
        $count = D("Addchain")->getcount($condi);
        $p = getpage($count, C('PAGE_SIZE'),$condi);
        $show = $p->newshow();
        $data = D("Addchain")->lists($condi,$page,C('PAGE_SIZE'),'id desc');
        $this->assign("list",$data);
        $this->assign("page",$show);
        $this->display();
    }
    
    //充值金链
    public function index(){
        $this->display();
    }
    
    //执行充值
    public function adds(){
        err('关闭');
        $data['phone'] = trim(I('phone', '', 'htmlspecialchars'));
        $data['num'] = number_format(I('num'));
        $data['note'] = trim(I('note', '', 'htmlspecialchars'));
        if (empty($data['phone'])) {
            err('手机号不能为空');
        }
        if (empty($data['num'])) {
            err('充值数量不能为空');
        }
        $obj = D('Members');
        $where['phone'] = $data['phone'];
        $info = $obj->userinfo($where);
        if(empty($info)) err("该账户不存在");
        if($info['is_lock']==1) err("该账户锁定");
        if($info['userlevel']==1){
            $role = "普通会员";
        }elseif($info['userlevel']==2){
            $role = "社区会员";
        }
        $content = array(
            "phone"=>$data['phone'],
            "num"=>$data['num'],
            "role"=>$role, 
            "name"=>$info['name'],
            "idno"=>$info['idno'],
        );
        succ($content);
    }
    
    public function add_chains(){
        err('关闭');
        $data['phone'] = trim(I('phone', '', 'htmlspecialchars'));
        $data['num'] = I('num');
        $data['note'] = trim(I('note', '', 'htmlspecialchars'));
        $data['ctime'] = time();
        if (empty($data['phone'])) {
            err('手机号不能为空');
        }
        if (empty($data['num'])) {
            err('充值数量不能为空');
        }
        if($data['num']<0){
            err('充值数量错误');
        }
        M()->startTrans();
        $pass = true;
        //查询该手机号是否充值过
        // $phoneinfo = D("Addchain")->findorderByphone($data['phone']);
        // if(!empty($phoneinfo)) err("充值失败,该手机号已充值过金链！"); 
        $obj = D('Members');
        $where['phone'] = $data['phone'];
        $info = $obj->userinfo($where);
        
        //加金链userchain
        $userchain = D("Members")->userchain($info['id']);
        $nowChain['wallet_number'] =bcadd($userchain['wallet_number'],$data['num'],2); 
        $pass = $pass && D("Members")->editchain($info['id'],$nowChain['wallet_number']);
        //记录表
        $chainArr = array(
            "phone"=>$data['phone'],
            "num"=>$data['num'],
            "uid"=>$info['id'],
            "ctime"=>time(),
            "role"=>$info['userlevel'],
            "adminid"=>session("dataAdmin.id"),
            "balance"=>$nowChain['wallet_number'],
            "note"=>$data['note']
        );
        $pass = $pass && D("Addchain")->adds($chainArr);
        //日志表
        $logdata = array(
            'uid' => $info['id'],
            'changeform' => 'in',
            'subtype' => 3,
            'money' => $data['num'],
            'ctime' => time(),
            'describes' => '您认购的'.$data['num'].'金链已到账',
            'balance' => $nowChain['wallet_number'],
            'money_type'=>1
        );
        $pass = $pass && D("MembersLog")->addlog($logdata);
        //给私募人加数量跟价格
        $chonglian = $info['chonglian']+$data['num'];
        $pass = $pass && D("Members")->save_user(array("id"=>$info['id']),array("chonglian"=>$chonglian,"lprice"=>0.4));
        if($pass){
            M()->commit();
            succ("金链充值成功");
        }else{
            M()->rollback();
            err("充值失败");
        }
    }
}
?>