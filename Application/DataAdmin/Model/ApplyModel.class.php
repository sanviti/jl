<?php 
namespace DataAdmin\Model;
class ApplyModel extends CommonModel{
    protected $tableName = 'applycash';
    protected $tablePrefix = 'jl_';
    /**
     * 提现列表
     */
    public function lists($where,$page,$limit,$order){
        $lists = $this->where($where)->page($page)->alias("a")->field("a.*,m.userid")->join("jl_members as m on a.uid=m.id")
                 ->limit($limit)->order($order)->select();
        return $lists;
    }
    
    /**
     * 总数
     * @param unknown $where
     * @return unknown
     */
    public function getcount($where){
        $num = $this->where($where)->field("a.*,m.userid")->alias("a")->join("jl_members as m on a.uid=m.id")->count();
        return $num;
    }
    /**
     * 公司账户
     */
    public function company_bank(){
        $banks = M("company_bank")->select();
        return $banks;
    }
    
    /**
     * 提现明细
     */
    public function process($id){
        $process = $this->where(array("a.id"=>$id))->alias("a")
                  ->field("a.*,m.userid,m.phone,m.name")->join("jl_members as m on a.uid=m.id")
                  ->find();
        $admin = M("admin")->where(array("id"=>$process['mgrid']))->find();
        $process['mgrid'] = $admin['username'];
        return $process;
    }
    /**
     * 查询记录
     */
    public function getinfo($field,$where){
        $info = $this->where($where)->field($field)->find();
        return $info;
    }
    
    /**
     * 后台处理提现
     */
    public function to_apply($where,$data){
        return $this->where($where)->save($data);
    }
    
    /**
     * 添加提现记录
     */
    public function add_action($data){
        return M("applycash_action_logs")->add($data);
    }
}
?>