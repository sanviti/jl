<?php
/**
 * 交易中心价格K线及精密价值
 * 2017-4-20
 * ws
 */
namespace Api\Model;
use Think\Model;
class TradingPriceModel extends Model {
    protected $tablename = 'trading_price';

    /**
     * 价格获取
     * @param [type] $data [description]
     */
    public function getPrice(){
        return  $this->order('id desc')->field('price')->find();
    }

}