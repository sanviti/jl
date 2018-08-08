<?php 
namespace DataAdmin\Model;
header("Content-Type:text/html;charset=utf-8");
class RechargeModel extends CommonModel{
    protected $tableName =  'recharge';
    protected $tablePrefix = 'jl_';
    
    public function lists($where,$page,$limit,$order){
        $list = $this->field("r.*,m.userlevel,m.userid")->alias("r")->where($where)->join("jl_members as m on r.uid=m.id")
                ->page($page)->limit($limit)->order($order)->select();
        foreach ($list as $k=>$v){
            //汇款人银行
            if($v['cardid']!=""){
                $bankinfo = D("Bank")->getBankinfo($v['cardid']);
                $list[$k]['bank'] = $bankinfo['bankname'];
                $list[$k]['truename'] = $bankinfo['truename'];
                $list[$k]['card'] = $bankinfo['card'];
                $list[$k]['branch'] = $bankinfo['branchname'];
            }
            //汇入yinhang
            $company_bank = D("Bank")->getcomBank($v['com_bankid']);
            $list[$k]['com_bank'] = $company_bank['bankname'];
            $list[$k]['com_card'] = $company_bank['card'];
            $list[$k]['com_sub'] = $company_bank['subbank'];
            $list[$k]['com_truename'] = $company_bank['truename'];
        }
        return $list;
    }
    
    public  function counts($where){
       return $this->field("r.*,m.userlevel,m.userid")->alias("r")->where($where)->join("jl_members as m on r.uid=m.id")
              ->count();
    }
  //详情
  public function detail($id){
        $info = $this->field("r.*,m.userlevel,m.userid,m.balance")->alias("r")->where(array("r.id"=>$id))
                ->join("jl_members as m on r.uid=m.id")->find();
        //汇款人银行
        if($info['cardid']!=""){
            $bankinfo = D("Bank")->getBankinfo($info['cardid']);
            $info['bank'] = $bankinfo['bankname'];
            $info['truename'] = $bankinfo['truename'];
            $info['card'] = $bankinfo['card'];
            $info['branch'] = $bankinfo['branchname'];
        }
        
        //汇入yinhang
        $company_bank = D("Bank")->getcomBank($info['com_bankid']);
        $info['com_bank'] = $company_bank['bankname'];
        $info['com_card'] = $company_bank['card'];
        $info['com_sub'] = $company_bank['subbank'];
        $info['com_truename'] = $company_bank['truename'];
        return $info;
  }
  
  //修改充值订单
  public function editstatus($where,$data){
      return $this->where($where)->save($data);
  }
  
  
  //增加充值记录
  public function add_recharge($data){
      return $this->add($data);
  }
  
  //通过订单号查询订单
  public function findbyorder($ordersn){
      $log = $this->where(array("ordersn"=>$ordersn))->find();
      return $log;
  }
  
  //查询充值总额
  public function getrechargesum(){
      return $this->where(array("state"=>1))->sum("money");
  }
}

?>