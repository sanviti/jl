<?php 
namespace DataAdmin\Model;
class ComBankModel extends CommonModel{
    protected $tableName = 'company_bank';
    protected $tablePrefix = 'jl_';
    
    public function lists(){
        return $this->select();
    }
    
    public function add_combank($data){
        return $this->add($data);
    }
    
    public function getBankcard($card){
        $data = $this->find(array('where'=>array('card'=>$card)));
        return $this->_format($data);
    }
    
    public function getinfo($id){
        return $this->where(array("id"=>$id))->find();
    }
    
    public function edit_combank($data){
        return $this->save($data);   
    }
    
    public function deletes($id){
        return $this->where(array("id"=>$id))->delete();
    }
}
?>