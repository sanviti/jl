<?php 
namespace DataAdmin\Model;
class MembersModel extends  CommonModel{
    protected $tableName = 'members';
    protected $tablePrefix = 'jl_';
    /**
     * 用户信息 uid
     * @param  [type] $token [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function profiles($uid, $field = 'balance,balance_lock,token,id,userid,is_lock,leadid,teamid,userlevel,idno,name,phone'){
        return $this->field($field)->where(array('id' => $uid))->find();
    }
    
    /**
     * 用户余额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function save_user($where,$data){
        return $this->where($where)->save($data);
    }
    
    /**
     * 用户充值金额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function deposit($uid,$num,$act = 'in'){
        $condi = array('id' => $uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("deposit",$num);
        }else{
            return $this->where($condi)->setDec("deposit",$num);
        }
    }
    
    /**
     * 用户余额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function balance($uid,$num,$act = 'in'){
        $condi = array('id'=>$uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("balance",$num);
        }else{
            return $this->where($condi)->setDec("balance",$num);
        }
    }

    /**
     * 用户锁定余额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function balance_lock($uid, $num, $act = 'in'){
        $condi = array('id' => $uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("balance_lock",$num);
        }else{
            return $this->where($condi)->setDec("balance_lock",$num);
        }
    }
    /**
     * 用户提现金额变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function buy_back($uid,$num,$act = 'in'){
        $condi = array('id' => $uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("buy_back",$num);
        }else{
            return $this->where($condi)->setDec("buy_back",$num);
        }
    }
    
    /**
     * 用户信息 phone
     * @param  [type] $token [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function userinfo($where, $field = '*'){
        return $this->field($field)->where($where)->find();
    }
    
    
    /**
     * 用户信息 金链
     * @param  [type] $token [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function userchain($uid,$field='wallet_number'){
        return M("wallet")->field($field)->where(array("member_id"=>$uid,"wallet_type"=>1))->find();
    }
    
    /**
     * 加用户金链
     * @param  [type] $token [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function editchain($uid,$change){
        return M("wallet")->where(array("member_id"=>$uid,"wallet_type"=>1))->save(array("wallet_number"=>$change));
    }
    
    /**
     * 用户资产统计
     */
    public function memberAccount($where,$page){
        $lists =  $this->alias("m")->page($page)->where($where)
               ->field("m.id,m.userid,m.phone,m.name,m.balance,m.balance_lock,m.userlevel,w.wallet_number,w.wallet_lock")
               ->join("left join jl_wallet as w on m.id=w.member_id")->limit(C('PAGE_SIZE'))->select();
        foreach ($lists as $k=>$v){
            $lists[$k]['full_balance'] = bcadd($v['balance'],$v['balance_lock'],3);
            $lists[$k]['full_wallet'] = bcadd($v['wallet_number'],$v['wallet_lock'],3);
        }
        return $lists;
    }
    /**
     * 用户资产统计数量
     */
    public function memberCount($where){
        return $this->alias("m")->where($where)
                ->join("left join jl_wallet as w on m.id=w.member_id")->count();
    }
    
    //查询用户总金链
    public function getsumchain($field){
        return M("wallet")->sum($field);
    }
}

?>