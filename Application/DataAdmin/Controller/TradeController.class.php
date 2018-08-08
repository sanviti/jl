<?php 
namespace DataAdmin\Controller;
use Common\Lib\Constants;
class TradeController extends BaseController{
    //后台操盘挂买单量
    public  function backStageAdd(){
        $priceModel=D('TradingPrice');
        $buyModel = D('TradingBuy');
        $TradingPrice=$priceModel->getPrice();
        if(!$TradingPrice){
            $this->error('未设置今日大盘价格，请设置后重试!');
        }
        $num=I('num');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            $this->error('卖出数量最多为2位小数');
        }
        $data['price'] = $TradingPrice['price'];
        $data['num'] = $num;
        $data['uid'] = 1;

        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $condi = array(
            'ctime' => array('gt', $date),
            'uid'=>1
        );
        $backStageBuyList = $buyModel ->where($condi)->find();
        if($backStageBuyList){
            $upd = [
                'status' => Constants::ORDER_INTRADING,
                'ptime' => '',
                'isclose' => 0,
                'num'=>$backStageBuyList['num']+$num
            ];

            $result = $buyModel->modify($backStageBuyList['id'], $upd);
        }else{
            $result =$buyModel->add($data);
        }

        if($result){
            $this->success('设置成功，可以进行匹配交易了。');
        }else{
            $this->error('设置失败,请重试。或联系技术人员。');
        }
    }

    //卖出记录
    public function selllists(){
        //操盘后台显示挂单
        $buyModel = D('TradingBuy');
        $succModel = D('TradingSucc');
        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $condi = array(
            'ctime' => array('gt', $date),
            'uid'=>1
        );
        $backStageBuyList = $buyModel ->where($condi)->find();
        $backStageBuyList['succnum']=floatval($succModel->buySuccNum($backStageBuyList['transno']));
        $this->assign('backStageBuyList', $backStageBuyList);
        //总剩余卖单子
        $sellModel = M('TradingSell');
        $condi = array(
            'iscolse' => 0,
            'status' => 1,
            'ctime' => array('gt', $date),
            'uid'=>array('NEQ',1)
        );
        $total=$sellModel->where($condi)->sum('num');
        $this->assign('total',$total);
        $page = I("p");
        $transno = I("transno");
        $btime = I("btime");
        $etime = I("etime");
        $condi = array();
        if($transno){
            $condi['transno'] = $transno;
            $condurl['transno'] = $transno;
        }
        if($btime){
            $condi['ctime'] = array("EGT",strtotime($btime));
            $condurl['ctime'] = array("EGT",strtotime($btime));
        }
        if($etime){
            $condi['ctime'] = array("ELT",strtotime($etime));
            $condurl['ctime'] = array("ELT",strtotime($etime));
        }
        if($btime && $etime){
            $condi['ctime'] = array(array("EGT",strtotime($btime)),array("ELT",strtotime($etime)));
            $condurl['ctime'] = array(array("EGT",strtotime($btime)),array("ELT",strtotime($etime)));
        }
        if(!$btime && !$etime){
            $btime = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $etime = mktime(23,23,23,date('m'),date('d'),date('Y'));
            $condi['ctime'] = array(array("EGT",$btime),array("ELT",$etime));
//            $condurl['ctime'] = array(array("EGT",$btime),array("ELT",$etime));
        }
        $condi['uid']=array('NEQ',1);
        $count = D("Trade")->sell_count($condi);
        $p = getpage($count, 100,$condurl);
        $show = $p->newshow();
        $list = D("Trade")->selllist($condi,$page,100);
        $this->assign("list",$list);
        $this->assign("page",$show);
        $this->display();
    }





    //后台操盘挂卖单量
    public  function backStageAddSell(){
        $priceModel=D('TradingPrice');
        $sellModel = D('TradingSell');
        $TradingPrice=$priceModel->getPrice();
        if(!$TradingPrice){
            $this->error('未设置今日大盘价格，请设置后重试!');
        }
        $num=I('num');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            $this->error('卖出数量最多为2位小数');
        }
        $data['price'] = $TradingPrice['price'];
        $data['num'] = $num;
        $data['uid'] = 1;

        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $condi = array(
            'ctime' => array('gt', $date),
            'uid'=>1
        );
        $backStageSellList = $sellModel ->where($condi)->find();
        if($backStageSellList){
            $upd = [
                'status' => Constants::ORDER_INTRADING,
                'ptime' => 'NULL',
                'isclose' => 0,
                'num'=>$backStageSellList['num']+$num
            ];

            $result = $sellModel->modify($backStageSellList['id'], $upd);
        }else{
            $result =$sellModel->add($data);
        }

        if($result){
            $this->success('设置成功，可以进行匹配交易了。');
        }else{
            $this->error('设置失败,请重试。或联系技术人员。');
        }
    }


    //买入记录
    public function buylists(){

        //操盘后台显示挂单
        $sellModel = D('TradingSell');
        $succModel = D('TradingSucc');
        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $condi = array(
            'ctime' => array('gt', $date),
            'uid'=>1
        );
        $backStageSellList = $sellModel ->where($condi)->find();
        $backStageSellList['succnum']=floatval($succModel->sellSuccNum($backStageSellList['transno']));
        $this->assign('backStageSellList', $backStageSellList);
        //总剩余买单子
        $buyModel = M('TradingBuy');
        $condi = array(
            'iscolse' => 0,
            'status' => 1,
            'ctime' => array('gt', $date),
            'uid'=>array('NEQ',1)
        );
        $total=$buyModel->where($condi)->sum('num');
        $this->assign('total',$total);
        $page = I("p");
        $transno = I("transno");
        $transno_sell = I("transno_sell");
        $transno_buy = I("transno_buy");
        $btime = I("btime");
        $etime = I("etime");
        $condi = array();
        if($transno!=""){
            $condi['transno'] = $transno;
            $conurl['transno']= $transno;
        }
        if($transno_sell!=""){
            $condi['transno_sell'] = $transno_sell;
            $conurl['transno_sell'] = $transno_sell;
        }
        if($transno_buy!=""){
            $condi['transno_buy'] = $transno_buy;
            $conurl['transno_buy'] = $transno_buy;
        }
        if($btime!=""){
            $condi['ctime'] = array("EGT",strtotime($btime));
            $conurl['ctime'] = array("EGT",strtotime($btime));
        }
        if($etime!=""){
            $condi['ctime'] = array("ELT",strtotime($etime));
            $conurl['ctime'] = array("ELT",strtotime($etime));
        }
        if(!$btime && !$etime){
            $btime = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $etime = mktime(23,23,23,date('m'),date('d'),date('Y'));
            $condi['ctime'] = array(array("EGT",$btime),array("ELT",$etime));
//            $condurl['ctime'] = array(array("EGT",$btime),array("ELT",$etime));
        }
        $condi['uid']=array('NEQ',1);
        $count = D("Trade")->buy_count($condi);
        $p = getpage($count,100,$conurl);
        $show = $p->newshow();
        $list = D("Trade")->buylist($condi,$page,100);
        $this->assign("list",$list);
        $this->assign("page",$show);
        $this->display();
    }

    //成交记录
    public function succlists(){
        $page = I("p");

        $transno = I("transno");
        $transno_sell = I("transno_sell");
        $transno_buy = I("transno_buy");
        $btime = I("btime");
        $etime = I("etime");
        $condi = array();
        if($transno!=""){
            $condi['transno'] = $transno;
        }
        if($transno_sell!=""){
            $condi['transno_sell'] = $transno_sell;
        }
        if($transno_buy!=""){
            $condi['transno_buy'] = $transno_buy;
        }
        if($btime!=""){
            $condi['ctime'] = array("EGT",strtotime($btime));
        }
        if($etime!=""){
            $condi['ctime'] = array("ELT",strtotime($etime));
        }
        if($btime!="" && $etime!=""){
            $condi['ctime'] = array(array("EGT",strtotime($btime)),array("ELT",strtotime($etime)));
        }

        $count = D("Trade")->succ_count($condi);
        $p = getpage($count, C('PAGE_SIZE'),$condi);
        $show = $p->newshow();
        $list = D("Trade")->succlist($condi,$page,C('PAGE_SIZE'));
        $this->assign("list",$list);
        $this->assign("page",$show);
        $this->display();
    }



    
    //交易走线图
    public function line(){
        $this->display();
    }
    //交易量走线图
    public function  getnum(){
        $orders = D("Trade")->getnum();
        $array = array(
            'info'=>$orders
        );
        succ($array);
    }
    //交易额走线图
    public function  getmoney(){
        $orders = D("Trade")->getmoney();
        $array = array(
            'info'=>$orders
        );
        succ($array);
    }
}
?>