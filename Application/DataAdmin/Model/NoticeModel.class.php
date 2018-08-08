<?php 
namespace DataAdmin\Model;
use Think\Model;
class NoticeModel extends CommonModel {
    protected $tableName = 'notice';
    protected $tablePrefix = 'jl_';
    /**
     * 列表
     * @param unknown $page
     * @param unknown $limit
     * @return \Think\mixed
     */
    public function notice_list($page,$limit,$field){
        $lists = $this->page($page)->field($field)->limit($limit)->select();
        return $lists;
    }
    
    public function getcount(){
        $count = $this->count();
        return $count;
    }
    /**
     * 详情
     */
    public function notice_detail($id){
        $detail = $this->where(array("id"=>$id))->find();
        return $detail;
    }
    
    /**
     * 添加公告
     */
    public function add_notice(){
        
    }
    

    public function getNoticeTitle($title){
        $data = $this->find(array('where'=>array('title'=>$title)));
        return $this->_format($data);
    }
    
}

?>