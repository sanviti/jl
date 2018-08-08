<?php 
namespace DataAdmin\Model;
class DataModel extends CommonModel{
    protected $tableName = 'user_log';
    protected $tablePrefix = 'jl_';
    
    //日志
    public function getlog($where,$page,$limit){
        $lists = $this->where($where)->field("u.*,m.userid,m.phone,m.userlevel,m.name")->alias("u")->join("left join jl_members as m on u.uid=m.id")
                 ->page($page)->limit($limit)->order("u.ctime desc")->select();
        return $lists;
    }
    //日志数量
    public function getcount($where){
        return $this->alias("u")->join("left join jl_members as m on u.uid=m.id")->where($where)->count();
    }
   
}

?>