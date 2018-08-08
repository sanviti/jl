<?php
namespace DataAdmin\Model;
//use Think\Model;
class RoleModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'role';
    protected $tablePrefix = 'jl_';
    protected $token = 'role';
   
    /*角色名称
     */
    public function getRoleName($name){
        $data = $this->find(array('where'=>array('name'=>$name)));
        return $this->_format($data);
    }
    
    /**
     * 角色列表
     */
    public function rolelist($page,$limit,$order){
        return $this->page($page)->limit($limit)->order($order)->select();
    }
    
    /**
     * 角色数量
     */
    public function getCount(){
        return $this->count();
    }
}
