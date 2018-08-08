<?php
/**
 * 钱包模型
 */
namespace DataAdmin\Model;
use Think\Model;
class WalletModel extends Model{
    protected $tableName = 'wallet';
    protected $userToken = '';
    /**
     * 用户金链变动
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function walletNumber($uid, $num, $act = 'in'){
        $condi = array('member_id' => $uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("wallet_number",$num);
        }else{
            return $this->where($condi)->setDec("wallet_number",$num);
        }
    }


    /**
     * 用户钱包锁定货币修修改
     * @param  [type] $num [description]
     * @return [type]      [description]
     */
    public function WalletNumberLock($uid, $num, $act = 'in'){
        $condi = array('member_id' => $uid);
        if($act == 'in'){
            return $this->where($condi)->setInc("wallet_lock",$num);
        }else{
            return $this->where($condi)->setDec("wallet_lock",$num);
        }
    }




    /**
     * 用户金链信息
     * @param  [type] $uid [description]
     * @param  string $field [description]
     * @return [type]        [description]
     */
    public function WalletProfiles($uid, $field = 'wallet_number'){
        return $this->field($field)->where(array('member_id' => $uid))->find();
    }


}