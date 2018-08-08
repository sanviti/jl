<?php
/**
 * 用户账户日志
 * 2018-3-3
 * lxy
 */
namespace Api\Model;
use Think\Model;
class MembersLogModel extends Model {
	protected $tableName = 'user_log';
	protected $tablePrefix = 'jl_';
	/**
	 * 获取最后一条日志
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function getLastLog($id){
		$condi = [
			'uid' => $id,
		];
		$last = $this->where($condi)->order('id DESC')->find();
		return $last;
	}
	/**
     * 列表
	 * @param $where 搜索条件
	 * @param $page  当前页
     */
	public function getList($where,$page,$field = '*'){
		$list = $this->field($field)
					 ->where($where)
					 -> page($page)
					 -> order('id desc')
					 -> limit(C('PAGE_SIZE'))
					 -> select();
		return $list;
	}
	
	/**
	 * 添加日志
	 */
	public function adds($data){
	    return  $this->add($data);
	}
}