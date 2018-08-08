<?php
/**
 * 手机端用户模型
 */
namespace Api\Model;
use Think\Model;
class ComBankModel extends Model{
    protected $tableName = 'company_bank';
    protected $tablePrefix = 'jl_';
    
    public function combanklist(){
        return $this->field("name,id")->where(array("type"=>2))->select();
    }
    

    /**
     * 公司充值的银行卡
     */
    public function com_bank(){
        return $this->where(array("type"=>1))->find();
    }
}
?>