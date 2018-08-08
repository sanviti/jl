<?php
/**
 * 后台挂卖单，卖出所有卖单
 * 2017-12-08
 * lxy
 */
namespace DataAdmin\Model;
use Think\Model;
class TradingSellModel extends Model {
	protected $tablename = 'trading_sell';

	/**
	 * 新增买入
	 * @param [type] $data [description]
	 */
	public function add($data){
		$data['price'] = floor3($data['price']);
		$data['transno'] = tradingOrderSN('S');
		$data['ctime'] = NOW_TIME;
		return parent::add($data);
	}
	
	/**
	 * 获取卖出队列
	 * @param  float $price 价格
	 */
	public function sellList($price){
		$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$condi = array(
			'iscolse' => 0,
			'status' => 1,
			'ctime' => array('gt', $date),
			'price'=>array('ELT', $price),
			'uid'=>array('NEQ',1)
		);
		return $this->field('id')->where($condi)->order('ctime ASC')->select();
	}
	/**
	 * 获取一条卖出
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function findById($id){
		$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$condi = [
			'id' => $id,
			'iscolse' => 0,
			'status' => 1,
			'ctime' => array('gt', $date)
		];
		return $this->where($condi)->find();
	}

	/**
     * 更新数据
     */
    public function modify($id, $data){
        $condi = array('id' => $id);
        return $this->where($condi)->save($data);
    }


	/**
	 * 获取后台挂单
	 *  @return [type] [description]
	 */
	public function backStageSellList(){
		$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$condi = array(
			'iscolse' => 0,
			'status' => 1,
			'ctime' => array('gt', $date),
			'uid'=>1
		);
		return $this->field('id')->where($condi)->find();
	}
}