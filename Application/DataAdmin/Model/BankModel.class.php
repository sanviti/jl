<?php 
namespace DataAdmin\Model;
class BankModel extends CommonModel{
    protected $tableName = 'bankcard';
    protected $tablePrefix = 'jl_';
    
    //个人银行卡信息
    public function getBankinfo($bankid){
        return $this->where(array("id"=>$bankid))->find();
    }
    
    //公司银行卡信息
    public function getcomBank($bankid){
        return M("company_bank")->where(array("id"=>$bankid))->find();
    }
}

?>