<?php
namespace Api\Controller;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;
class HomeController extends Controller{
    /**
     * 交易中心首页
     * @param  int $page       分页
     * @param  str $request 请求类型 succ 成交 buy 买入 sell卖出
     * @return [type]          [description]
     */
    public function index(){
        $Model = D('TradingSucc');
        $priceModel=D('TradingPrice');
        $TradingPrice = $priceModel->getPrice();
        //$total= $Model->sum('num');
		/*当天成交量START*/
		$trading_amountModel = M("trading_amount");
		$last_time = strtotime(date("Y-m-d"))+3600*24;
		$condi['ctime'] = array('lt',$last_time);;
		$total = $trading_amountModel->field('amount')->where($condi)->order('ctime desc')->find();
		/*当天成交量END*/
        $priceList=$priceModel->field('price,FROM_UNIXTIME(ctime,"%m-%d") as ctime')->order('ctime asc ')->limit(7)->select();
		$data['total']=floatval($total['amount']);
        $data['price'] = $TradingPrice['price'];
        $data['list'] = $priceList;
        $data['buy_fee']=Constants::SCORE_BUY_FEE;
        $data['sell_fee']=Constants::SCORE_SELL_FEE;
        succ($data);
    }

    /**
     * 软件版本号
     * @param sys 操作平台 android ios
     * @return
     * code  版本号
     * describe 更新内容
     * download 下载地址
     * force    强制更新
     * dateline 时间
     */
    public function version(){
        $data = M('version')->field('version as code,name as version,describe,download,force,dateline')->order('dateline DESC')->find();
        $data['download'] = $data['download'];
        succ($data);
    }

}
