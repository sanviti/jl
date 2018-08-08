<?php 
namespace DataAdmin\Model;
class AddchainModel extends CommonModel{
    protected $tableName = 'chain';
    protected $tablePrefix = 'jl_';
    
    public function adds($data){
        return $this->add($data);
    }
    
    public function lists($where,$page,$limit,$order){
        return $this->where($where)->alias("c")->page($page)->field("c.*,m.userid,m.name,m.idno")
               ->join("left join jl_members as m on c.uid=m.id")
               ->limit($limit)->order($order)->select();
    }
    /**
     * 总数
     * @param unknown $where
     * @return unknown
     */
    public function getcount($where){
        $num = $this->where($where)->alias("c")->join("jl_members as m on c.uid=m.id")->count();
        return $num;
    }
    
    /**
     * 判断该手机号是否充值
     */
    public function findorderByphone($phone){
        $info = $this->where(array("phone"=>$phone))->find();
        return $info;
    }
} 
 
?>