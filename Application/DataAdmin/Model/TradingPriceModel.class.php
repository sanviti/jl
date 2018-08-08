<?php
/**
 * 交易中心价格K线及精密价值
 * 2017-4-20
 * ws
 */
namespace DataAdmin\Model;
use Think\Model;
class TradingPriceModel extends Model {
    protected $tablename = 'trading_price';

    /**
     * 价格获取
     * @param [type] $data [description]
     */
    public function getPrice(){
        $sdate = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $edate = mktime(23,59,59,date('m'),date('d'),date('Y'));
        $condi['ctime'] =array(array('EGT',$sdate),array('ELT',$edate));
        $this->where($condi)->order('id desc')->field('price')->find();
        return  $this->where($condi)->order('id desc')->field('price')->find();
    }

}