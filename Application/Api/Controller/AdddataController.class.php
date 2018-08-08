<?php 
namespace Api\Controller;
use Think\Controller;
header("Content-Type:text/html;charset=utf-8");
class adddataController extends Controller{
    public function datas(){
        //历史数据
        $start = date("Y-m-d 0:0:0",strtotime("-1 day"));
        $end = date("Y-m-d 24:0:0",strtotime("-1 day"));
        $where['ptime'] = array(array("EGT",strtotime($start)),array("ELT",strtotime($end)));
        $where['state'] = 1;
        $exchangeKey = M("recharge")->where($where)->sum("money");
        //2.金链数量
        $chainNum = M("wallet")->sum("wallet_number");
        //3.今日金链价格
        $todayPrice = M("trading_price")->order("id desc")->find();
        
        $todayPrice = $todayPrice['price'];
        //4.买入成交数量
        $condi['ctime'] = array(array("EGT",$start),array("ELT",$end));
        $buyNum = M("trading_succ")->where($condi)->sum("num");
        //5.账户余额总数
        $balance = M("members")->sum("balance");

        $log = array(
             'time'=>strtotime($start),
             'exchangekey'=>$exchangeKey,
             'chainnum' =>$chainNum,
             'chainprice'=>$todayPrice,
             'buynum'=>$buyNum,
             'balance'=>$balance 
        );
        $dates = M("hisdata")->where(array("time"=>strtotime($start)))->find();
        if(empty($dates)){
            M("hisdata")->add($log);
        }
    }

}