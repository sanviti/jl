<?php 
namespace Api\Model;
use Think\Model;
class NoticeModel extends Model{
    protected $tableName = 'notice';
    protected $tablePrefix = 'jl_';
    /**
     * 资讯列表
     */
    public function lists($page,$limit){
        $data = $this->page($page)->limit($limit)->field('title,id,info,add_time')->order("add_time DESC")->select();
        foreach ($data as $k=>$v){
            $data[$k]['add_time'] = date("Y-m-d",$v['add_time']);
            $data[$k]['info'] = strip_tags(html_entity_decode($v['info']));
        }
        return $data;   
    }
    
    /**
     * 资讯详情
     */
    public function detail($id){
        $data = $this->field('title,info,add_time')->where(array("id"=>intval($id)))->find();
        $data['add_time'] = date("Y-m-d",$data['add_time']);
        return $data;
    }
}

?>