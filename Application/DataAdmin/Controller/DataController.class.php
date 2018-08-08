<?php 
namespace DataAdmin\Controller;
use Common\Lib\Constants;
class DataController extends BaseController{
    //用户操作日志查询
    public function loglists(){
        $page = I("p");
        $role = I("role");
        $keyword = I("keyword");
        $where = array();
        if($keyword!=""){
            $where['m.userid|m.phone'] = $keyword;
        }
        if($role!=""){
            $where['m.userlevel'] = $role;
        }
        $count = D("Data")->getcount($where);
        $p = getpage($count, C('PAGE_SIZE'),$where);
        $show = $p->newshow();
        $lists = D("Data")->getlog($where,$page,C('PAGE_SIZE'));
        $this->assign("list",$lists);
        $this->assign('page',$show);
        $this->display();
    }
    
   //用户资产查询
   public function moneylists(){
       $page = I("p");
       $where = array();
       $role = I("role");
       $keyword = I("keyword");
       $where = array();
       if($keyword!=""){
           $where['m.userid|m.phone'] = $keyword;
       }
       if($role!=""){
           $where['m.userlevel'] = $role;
       }
       $count = D("Members")->memberCount($where);
       $p = getpage($count, C('PAGE_SIZE'),$where);
       $show = $p->newshow();
       $lists = D("Members")->memberAccount($where,$page);
       
       //查询用户总金链
       $sumchains = D("Members")->getsumchain("wallet_number");
       
       //查询用户总冻结金链
       $sumlockchain = D("Members")->getsumchain("wallet_lock");
       
       $sumchain = bcadd($sumchains, $sumlockchain,3);
       $this->assign('sumchain',$sumchain);
       
       $this->assign("list",$lists);
       $this->assign('page',$show);
       $this->display();
   }

    //强制卖出机制
    public  function  forceSell(){
        $data=I();
        $uid=$data['uid'];
        $num=$data['num'];
        $priceModel=D('TradingPrice');
        $sellModel = D('TradingSell');
        $succModel = D('TradingSucc');
        $walletModel = D('Wallet');
        $membersModel = D('members');
        $logModel = D("MembersLog");
        $TradingPrice=$priceModel->getPrice();
        if(!$TradingPrice){
            $this->error('未设置今日大盘价格，请设置后重试!');
        }
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            $this->error('卖出数量最多为2位小数');
        }
        if($uid==4631 || $uid==4160 || $uid==4708){
            $this->error('卖出失败');
        }
        $sellWallet=$walletModel->WalletProfiles($uid,'wallet_number');//钱包账户
        //判断当前用户金链是否足够
        if($sellWallet['wallet_number']<$num){
            $this->error('当前用户货币不足，无法强制卖出');
        }
        //判断后台操盘买单是否足够
        if(TradLockCheck(Constants::REDIS_PLANLOCK_NAME)){
            $this->error('【in execute】，交易匹配中，无法强制卖出，稍后重试。');
        }
        $buyModel = D('TradingBuy');
        $buyList = $buyModel ->backStageBuyList();
        $buy = $buyModel->findById($buyList['id']);
        $succNum = $succModel -> buySuccNum($buy['transno']);
        $buyNum = $buy['num'] - $succNum;
        if($num > $buyNum){
            $this->error('挂单不足，请增加挂单量。');
        }
        $stransno=tradingOrderSN('S');
        $data['num'] = $num;
        $data['price'] = floor3($TradingPrice['price']);
        $data['ctime'] = NOW_TIME;
        $data['status']=Constants::ORDER_SUCCESS;
        $data['isclose']=1;
        $data['transno'] =$stransno;
        $data['uid'] =$data['uid'];
        $data['ptime']=NOW_TIME;
        M()->startTrans();
        $result=true;
        $result = $result && M('trading_sell')->add($data);
        $result = $result && $walletModel->walletNumber($data['uid'],$num,'out');
        ###成交
        //成交订单号
        $transno = tradingOrderSN('T');

        //KEY新增
        $total = floor3($TradingPrice['price'] * $num);
        $sellFee=floor3($total * Constants::SCORE_SELL_FEE);
        $InBalance = floor3($total - $sellFee);

        $result = $result && $membersModel->balance($uid, $InBalance);
        //新增买卖手续费
        $result = $result && $membersModel->balance(2, $sellFee);

        //余额计算
        $sellBalance=$membersModel->profiles($uid,'balance,balance_lock');//KEY操作余额
        $sellWallet=$walletModel->WalletProfiles($uid,'wallet_number,wallet_lock');//钱包操作余额
        $walletBalance=bcadd($sellWallet['wallet_number'],$sellWallet['wallet_lock'],2);
        $balance=bcadd($sellBalance['balance'],$sellBalance['balance_lock'],3);

        //KEY新增日志
        $log = [
            'uid' =>$uid,
            'changeform' => 'in',
            'subtype' => 9,
            'money' => $InBalance,
            'ctime' => NOW_TIME,
            'balance' => $balance,
            'extends' => $transno,
            'money_type'=>2
        ];
        $result = $result && $logModel->add($log);

        //金链卖出日志
        $log = [
            'uid' => $uid,
            'changeform' => 'out',
            'subtype' => 9,
            'money' => $num,
            'ctime' => NOW_TIME,
            'balance' => $walletBalance,
            'extends' => $transno,
            'money_type'=>1
        ];
        $result = $result && $logModel->add($log);

        //后台操盘用户添加金链以及买入手续费
        $result=$result && $walletModel->WalletNumber(1,$num);
        $buyTotal =$TradingPrice['price'] * $num;
        $buyFee=round($buyTotal * Constants::SCORE_BUY_FEE,3);
        //后台操盘日志日志
        $log = [
            'uid' =>1,
            'changeform' => 'in',
            'subtype' => 9,
            'money' => $num,
            'ctime' => NOW_TIME,
            'extends' => $transno,
            'money_type'=>1
        ];
        $result = $result && $logModel->add($log);
        //生成成交单
        $order = [
            'num' => $num,
            'price' => $buy['price'],
            'ctime' => NOW_TIME,
            'transno_sell' => $stransno,
            'transno_buy' => $buy['transno'],
            'buy_uid' => $buy['uid'],
            'sell_uid' => $uid,
            'sell_fee' => $sellFee,
            'buy_fee' => $buyFee,
            'transno' => $transno,
        ];
        $result = $result && $succModel->add($order);
        if($result){
            M()->commit();
            $this->success('强制卖出成功');
        }else{
            M()->rollback();
            $this->success('强制卖出失败');
        }

    }
   
   
   /**
    * 数据统计
    */
   public function statistic(){
       //时实数据
       //1.兑换key总数
       $start = mktime(0,0,0,date('m'),date('d'),date('Y'));
       $end = mktime(24,0,0,date('m'),date('d'),date('Y'));
       $where['ptime'] = array(array("EGT",$start),array("ELT",$end));
       $where['state'] = 1;
       $exchangeKey = M("recharge")->where($where)->sum("money");
       $this->assign('exchangekey',$exchangeKey);
       //2.金链数量
       $chainNum = M("wallet")->sum("wallet_number");
       $this->assign("chainnum",$chainNum);
       //3.今日金链价格
       $pwhere['ctime'] = array(array("EGT",$start),array("ELT",$end)); 
       $todayPrice = M("trading_price")->where($pwhere)->find();
       $todayPrice = $todayPrice['price'];
       $this->assign("chainprice",$todayPrice);
       //4.买入成交数量
       $condi['ctime'] = array(array("EGT",$start),array("ELT",$end));
       $buyNum = M("trading_succ")->where($condi)->sum("num"); 
       $this->assign('buynum',$buyNum);
       //5.账户余额总数
       $balance = M("members")->sum("balance");
       $balance_lock = M("members")->sum("balance_lock");
       $this->assign("balance",$balance);
       $this->assign("balance_lock",$balance_lock);
       
       //每日提现数量
       $appwhere['ptime'] = array(array("EGT",$start),array("ELT",$end));
       $appwhere['state'] = 1;
       $applynum = M("applycash")->where($appwhere)->sum("money");
       $this->assign("applynum",$applynum);
       
       //每日提现支给数量
       $appwhere['ptime'] = array(array("EGT",$start),array("ELT",$end));
       $appwhere['state'] = 1;
       $applydis = M("applycash")->where($appwhere)->sum("disburse");
       $this->assign("applydis",$applydis);
       
       //每日提现手续费数量
       $appwhere['ptime'] = array(array("EGT",$start),array("ELT",$end));
       $appwhere['state'] = 1;
       $applyaccount = M("applycash")->where($appwhere)->sum("account");
       $this->assign("applyaccount",$applyaccount);
       
       
       //查询历史数据
       $his = M("hisdata")->select();
       $this->assign("his",$his);
       $this->display();
   }
   
   /** 
    * 查找历史数据
    */
   public function hisdata(){
       $id = I("id");
       $data = M("hisdata")->where(array("id"=>$id))->find();
       $data['time'] = date("Y-m-d",$data['time']);
       succ($data);
   }
}

?>