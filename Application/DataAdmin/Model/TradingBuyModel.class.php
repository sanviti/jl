<?php
/**
 * 后台挂买入单，买入所有卖单
 * 2018-5-06
 *
 */
namespace DataAdmin\Model;
use Think\Model;
class TradingBuyModel extends Model {
	protected $tablename = 'trading_buy';

	/**
	 * 新增买入
	 * @param [type] $data [description]
	 */
	public function add($data){
		$data['price'] = floor3($data['price']);
		$data['transno'] = tradingOrderSN('B');
		$data['ctime'] = NOW_TIME;
		return parent::add($data);
	}

	/**
	 * 获取买入队列
	 * @return [type] [description]
	 */
	public function buyList($prcie){
		$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$condi = array(
			'iscolse' => 0,
			'status' => 1,
			'ctime' => array('gt', $date),
			'uid'=>array('NEQ',1),
			'prcie'=>array('EGT',$prcie)
		);
		return $this->field('id')->where($condi)->order('ctime ASC')->select();
	}
	/**
	 * 获取一条买入
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function findById($id){
		$date = mktime(0,0,0,date('m'),date('d'),date('Y'));
		$condi = [
			'id' => $id,
			'iscolse' => 0,
			'status' => 1,
			'ctime' => array('gt', $date),
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
	 * @return [type] [description]
	 */
	public function backStageBuyList(){
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