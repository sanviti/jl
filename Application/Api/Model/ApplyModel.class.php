<?php 
namespace Api\Model;
use Think\Model;
class ApplyModel extends Model{
    protected $tableName = 'applycash';
    protected $tablePrefix = 'jl_';
    
    /**
     * 提现
     */
    public function adds($data){
        return $this->add($data);
    }
}

?>