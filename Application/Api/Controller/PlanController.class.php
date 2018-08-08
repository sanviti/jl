<?php
/**
 * 计划任务
 */
namespace Api\Controller;
use Think\Log;
use Think\Controller;
use Think\Model;
use DataAdmin\Common\Lib\Constants;

class PlanController extends Controller{
   
    /**
     * 自动匹配交易-crontab
     * @return [type] [description]
     */
    public function autoTransact(){
        set_time_limit(0);
        //任务锁
        $val = TradLockCheck(Constants::REDIS_PLANLOCK_NAME);
        if(TradLockCheck(Constants::REDIS_PLANLOCK_NAME)){
            $fail = PlanFail(Constants::REDIS_PLANFAIL_NAME);
            if($fail > 5 && ($fail % 10 == 0)){
                $this->p('-----------------------发送短信---------------------');
            }
            PlanFail(Constants::REDIS_PLANLOCK_NAME,'inc');
            exit('in execute');
        }

        TradLock(Constants::REDIS_PLANLOCK_NAME, 'add', Constants::REDIS_PLANLOCK_TIME);
        // sleep(10);
        $buyModel = D('TradingBuy');
        $buyList = $buyModel -> buyList();
        if(0 < count($buyList)){
            foreach($buyList as $buy){
                //买单锁
                TradLock(Constants::REDIS_BUYLOCK_PREFIX . $buy['id']);

                $this->p('start');
                $this->_cal_transact($buy['id']);
                $this->p('end');

                //买单锁
                TradLock(Constants::REDIS_BUYLOCK_PREFIX . $buy['id'], 'del');

            }
        }else{
            echo "none\r\n";
        }
        

        //任务锁
        TradLock(Constants::REDIS_PLANLOCK_NAME, 'del');
        //重置错误次数
        PlanFail(Constants::REDIS_PLANFAIL_NAME,'reset');
    }

    /**
     * 匹配交易
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    private function _cal_transact($id){
        $this->p('Info:处理-BuyID:'.$id);

        $buyModel  = D('TradingBuy');
        $sellModel = D('TradingSell');
        $succModel = D('TradingSucc');
        $membersModel = D('members');
        $bLogModel = D('UserBalanceLog');
        $sLogModel = D('UserScoreLog');

        $buy = $buyModel->findById($id);
        if(!$buy){
            $this->p('Error:买入单不存在');
            return ;
        }

        $succNum = $succModel -> buySuccNum($buy['transno']);
        //可买数量
        $buyNum = $buy['num'] - $succNum;
        if(0 >= $buyNum){
            $this->p('Error:可买数量错误（'. $buyNum .'）');
            return ;
        }

        //卖出队列
        $sellList = $sellModel -> sellList($price);

        if($sellList){
            $result = true;
            $membersModel->startTrans();

            foreach($sellList as $v){
                $sell = $sellModel -> findById($v['id']);
                $this->p('Info:匹配SellID:'.$sell['id']);

                //已卖出
                $sellSuccNum = $succModel -> sellSuccNum($buy['transno']);
                //可卖数量
                $sellNum = $sell['num'] - $sellSuccNum;
                if(0 >= $sellNum){
                    echo 'Error:可卖数量错误（'. $sellNum .'）';
                    continue ;
                }

                //卖出锁
                TradLock(Constants::REDIS_SELLLOCK_PREFIX . $sell['id']);
                //成交数量
                $transactNum = 0;
                if($buyNum > $sellNum){
                    $transactNum = $sellNum;
                }elseif($buyNum < $sellNum){
                    $transactNum = $buyNum;
                }else{
                    $transactNum = $sellNum;
                }

                ###成交
                    //成交订单号
                $transno = tradingOrderSN('T');
                $this->p('Info:成交订单号（'. $transno .'）');
                //买入用户操作
                    //扣款
                $buyUser = $membersModel->account($buy['uid']);
                $sellUser = $membersModel->account($sell['uid']);
                    

                $DecBalance = floor4($buy['price'] * $transactNum);
                $buyUserBalance = $buyUser['balance'] + $buyUser['balance_lock'] - $DecBalance;
                $result = $result && $membersModel->balanceByid($buy['uid'], $DecBalance, 1, 'out');

                    //扣款日志
                $log = [
                    'uid' => $buy['uid'],
                    'changeform' => 'out',
                    'subtype' => Constants::BALANCE_LOG_BUYSCORE,
                    'money' => $DecBalance,
                    'ctime' => NOW_TIME,
                    'balance' => $buyUserBalance,
                    'extends' => $transno
                ];
                $result = $result && $bLogModel->add($log);
                    //债金券结算
                $IncScore = $transactNum;
                $buyUserScore = $buyUser['score'] + $buyUser['score_lock'] + $IncScore;
                $result = $result && $membersModel->scoreByid($buy['uid'], $IncScore, 0, 'in');

                    //债金券日志
                $log = [
                    'uid' => $buy['uid'],
                    'changeform' => 'in',
                    'subtype' => Constants::SCORE_SUBTYPE_BUY,
                    'money' => $IncScore,
                    'ctime' => NOW_TIME,
                    'balance' => $buyUserScore,
                    'extends' => $transno
                ];
                $result = $result && $sLogModel->add($log);

                //卖出用户操作

                    //结款
                $total = floor4($sell['price'] * $transactNum);
                $fee = floor4($total * Constants::SCORE_SELL_FEE);
                $IncBalance = floor4($total - $fee);
                $sellUserBalance = $sellUser['balance'] + $sellUser['balance_lock'] + $IncBalance;
                $result = $result && $membersModel->balanceByid($sell['uid'], $IncBalance, 0, 'in');
                    //结款日志
                $log = [
                    'uid' => $sell['uid'],
                    'changeform' => 'in',
                    'subtype' => Constants::BALANCE_LOG_SELLSCORE,
                    'money' => $IncBalance,
                    'ctime' => NOW_TIME,
                    'balance' => $sellUserBalance,
                    'extends' => $transno
                ];
                $result = $result && $bLogModel->add($log);
                    //债金券扣除
                $DecScore = $transactNum;
                $sellUserScore = $sellUser['score'] + $sellUser['score_lock'] - $DecScore;
                $result = $result && $membersModel->scoreByid($sell['uid'], $DecScore, 1, 'out');

                    //债金券日志
                $log = [
                    'uid' => $sell['uid'],
                    'changeform' => 'out',
                    'subtype' => Constants::SCORE_SUBTYPE_SELL,
                    'money' => $DecScore,
                    'ctime' => NOW_TIME,
                    'balance' => $sellUserScore,
                    'extends' => $transno
                ];
                $result = $result && $sLogModel->add($log);

                //生成成交单
                $order = [
                    'num' => $transactNum,
                    'price' => $buy['price'],
                    'ctime' => NOW_TIME,
                    'transno_sell' => $sell['transno'],
                    'transno_buy' => $buy['transno'],
                    'buy_uid' => $buy['uid'],
                    'sell_uid' => $sell['uid'],
                    'fee' => $fee,
                    'transno' => $transno,
                ];
                $result = $result && $succModel->add($order);
            

                //买入数量递减
                $buyNum -= $transactNum;

                //如果卖出数量为零标记卖出单 成功
                if(($sellNum - $transactNum) <= 0){
                    $this->p('Info:卖出单完成（SellID: '. $sell['id'] .', 数量：'. $transactNum .', 价格：'. $sell['price'] .'）');
                    $upd = [
                        'status' => Constants::ORDER_SUCCESS,
                        'ptime' => NOW_TIME,
                        'isclose' => 1,
                    ];

                    $result = $result && $sellModel->modify($sell['id'], $upd);
                }

                //清除卖出锁
                TradLock(Constants::REDIS_SELLLOCK_PREFIX . $sell['id'], 'del');

                //买入数量为零标记买入单 成功
                if(0 >= $buyNum){
                    $this->p('Info:买入单完成（'. $buy['id'] .'）');
                    $upd = [
                        'status' => Constants::ORDER_SUCCESS,
                        'ptime' => NOW_TIME,
                        'isclose' => 1,
                    ];

                    $result = $result && $buyModel->modify($buy['id'], $upd);
                    break; 
                }

            }
            if($result){
                $this->p('Info:匹配完成（'. $buy['id'] .'）');
                $membersModel->commit();
                return true;
            }else{
                $this->p('Info:匹配失败（'. $buy['id'] .'）');
                $membersModel->rollback();
                return false;
            }
            

        }else{
            return false;
        }

    }

    /**
     * 自动撤销过期订单-crontab
     * @return [type] [description]
     */
    public function autoRecall(){
        //任务锁
        //撤单买入

        //撤单卖出
    }

    private function _recall(){

    }

    private function p($str){
        if($str == 'start'){
            echo '###################################### START #######################################<br/>';
        }elseif($str == 'end'){
            echo '###################################### END #######################################<br/>';
        }else{
            echo $str."<br/>";
        }
    }
}