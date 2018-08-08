<?php 
namespace Api\Model;
use Think\Model;
class RechargeModel extends Model{
    protected $tableName = 'recharge';
    protected $tablePrefix = 'jl_';
    
    /**
     * 添加充值记录
     */
    public function adds($data){
        return $this->add($data);
    }
    
    /**
     * 充值订单
     */
    public function get_data($where){
        return $this->where($where)->field("money,ordersn")->find();
    }
    
    /**
     * 修改订单状态
     */
    public function changestate($ordersn,$data){
		$res = $this->where(array("ordersn"=>$ordersn))->save($data);
		if($res){
			return true;
		}else{
			return false;
		}
       
    }
}
?>