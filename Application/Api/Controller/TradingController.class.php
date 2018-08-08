<?php
namespace Api\Controller;
use Think\Controller;
use Think\Model;
use Common\Lib\Constants;

class TradingController extends BaseController{
    /**
     * 发布买入
     * @return [type] [description]
     */
    public function buy(){
        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
//        err('非交易时间');
        if(time()<$date+Constants::ORDER_TRANDING_STIME || time()>$date+Constants::ORDER_TRANDING_ETIME){
            err('非交易时间');
        }
        require_check_api('num,paypwd', $this->post);
        $members = D('members');
        $buyModel = D('TradingBuy');
        $priceModel=D('TradingPrice');
        $TradingPrice=$priceModel->getPrice();
        $price = $TradingPrice['price'];
        $num   = floatval($this->post['num']);
        //数据检查
        if(0 >= $num) err('购入数量错误');
        if(10 > $num)err('单笔购买数量区间为10-2000枚');
        if(2000 < $num)err('单笔购买数量区间为10-2000枚');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            err('卖出数量最少为2位小数');
        }
        if(0.1>$price)err('买入失败');
        $total = $price * $num*(1+Constants::SCORE_BUY_FEE);
        //用户余额检查
        $user = $members->normalMember($this->userToken,'balance,balance_lock,paysalt,paypwd,iscertify');
        if($user['balance'] < $total) err('账户余额不足');
        if($user['paypwd']!=md5password($this->post['paypwd'],$user['paysalt'])){
            err('交易密码错误');
        }
        $data['price'] = $price;
        $data['num'] = $num;
        $data['uid'] = $this->uid;

        $buyModel->startTrans();
        //用户余额锁定
        $upd = [
            'balance' => $user['balance'] - $total,
            'balance_lock' =>  $user['balance_lock'] + $total,
        ];
        $result = $members->modify($this->userToken, $upd);
        //发布订单
        $result = $result && $buyModel->add($data);
        if($result){
            $buyModel->commit();
            succ('发布成功');
        }else{
            $buyModel->rollback();
            err('发布失败');
        }
    }
    /**
     * 发布卖出
     * @return [type] [description]
     */
    public function sell(){
//        err('非交易时间');
        $date = mktime(0,0,0,date('m'),date('d'),date('Y'));
        if(time()<$date+Constants::ORDER_TRANDING_STIME || time()>$date+Constants::ORDER_TRANDING_ETIME){
            err('非交易时间');
        }
        require_check_api('num,paypwd', $this->post);
        $sellModel = D('TradingSell');
        $priceModel=D('TradingPrice');
        $walletModel=D('Wallet');
        $members = D('members');
        $TradingPrice=$priceModel->getPrice();
        $price = $TradingPrice['price'];
        $num   = floatval($this->post['num']);
        //数据检查
        //数据检查
        if(0 >= $num) err('卖出数量错误');
        if(10 > $num)err('单笔卖出数量区间为10-2000枚');
        if(2000 < $num)err('单笔卖出数量区间为10-2000枚');
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $num)) {
            err('卖出数量最多为2位小数');
        }
        $total = $num;
        //用户余额检查
        $user=$walletModel->WalletProfiles($this->uid);
        if($user['wallet_number'] < $total) err('货币不足');

        $user = $members->normalMember($this->userToken,'paysalt,paypwd,iscertify');
        if($user['paypwd']!=md5password($this->post['paypwd'],$user['paysalt'])){
            err('交易密码错误');
        }


        $data['price'] = $price;
        $data['num'] = $num;
        $data['uid'] = $this->uid;

        $sellModel->startTrans();
        //用户余额锁定
        $result= $walletModel->walletNumber($this->uid,$num,'out');
        $result= $result && $walletModel->WalletNumberLock($this->uid,$num,'in');
        //发布订单

        $result = $result && $sellModel->add($data);
        if($result){
            $sellModel->commit();
            succ('发布成功');
        }else{
            $sellModel->rollback();
            err('发布失败');
        }
    }
    /**
     * 交易历史
     * @param  int $page       分页
     * @param  str $request 请求类型 succ 成交 buy 买入 sell卖出
     * @return [type]          [description]
     */

    public function history(){
        $page = intval($this->post['p']);
        $where['buy_uid|sell_uid']=$this->uid;
        $Model = D('TradingSucc');
        $list = $Model -> field('buy_uid,sell_uid,num, (price*num) as amount,buy_fee,sell_fee')->where($where)->page($page)->limit(10)->order('ctime DESC')->select();
        foreach($list as $key=>$value){
            if($value['buy_uid']==$this->uid){
                $list[$key]['state']='买入';
                $list[$key]['amount']=bcadd($value['amount'],$value['buy_fee'],3);
            }else{
                $list[$key]['state']='卖出';
                $list[$key]['amount']=bcsub($value['amount'],$value['sell_fee'],3);
            }
            unset($list[$key]['buy_uid']);
            unset($list[$key]['sell_uid']);
        }
        $data['list'] = $list;
        succ($this->output($data));
    }



    /**
     * 我的订单
     * @param  int $page    分页
     * @param  str $request 请求类型 buy 买入 sell卖出
     * @return [type]          [description]
     */
    public function order(){
        $page = intval($this->post['p']);
        $request = addslashes($this->post['request']);
        $succModel = D('TradingSucc');
        $condi = ['uid' => $this->uid,'status' => 1];
        switch ($request) {
            case 'sell':
                $Model = D('TradingSell');
                $list = $Model -> where($condi) -> field('FROM_UNIXTIME(ctime) as ctime,price,num,status,transno')
                    -> page($page) -> limit(10) -> order('ctime DESC') -> select();
                foreach($list as  $key=>$value){
                    $list[$key]['succ']=floatval($succModel->sellSuccNum($value['transno']));
                }
                break;
            case 'buy':
            default:
                $Model = D('TradingBuy');
                $list = $Model -> where($condi) -> field('FROM_UNIXTIME(ctime) as ctime,price,num, status,transno')
                    -> page($page) -> limit(10) -> order('ctime DESC') -> select();
                foreach($list as  $key=>$value){
                    $list[$key]['succ']=floatval($succModel->buySuccNum($value['transno']));
                }
                break;
        }
        $data['list'] = $list;
        succ($this->output($data));
    }

    /**
     * 合并我的订单
     * @param  int $page    分页
     * @param  str $request 请求类型 buy 买入 sell卖出
     * @return [type]          [description]
     */
    public function MergeOrder(){
        $succModel = D('TradingSucc');
        $condi = [
            'uid' => $this->uid,
            'status'=>1
        ];
        $list=array();
        //卖
        $Model = D('TradingSell');
        $sellList = $Model
            -> where($condi)
            -> field('ctime,price,num,transno,"sell" as state')
            ->where($condi)
            ->order('ctime DESC')
            -> select();
        foreach($sellList as  $key=>$value){
            $sellList[$key]['succ']=floatval($succModel->sellSuccNum($value['transno']));
        }
        if($sellList){
            $list=array_merge($list,$sellList) ;
        }
        $Model = D('TradingBuy');
        $buyList = $Model
            -> where($condi)
            -> field('ctime,price,num,transno,"buy" as state')
            -> order('ctime DESC')
            -> select();
        foreach($buyList as  $key=>$value){
            $buyList[$key]['succ']=floatval($succModel->buySuccNum($value['transno']));
        }
        if($buyList){
            $list=array_merge($list,$buyList) ;
        }
       foreach($list as $k=>$item){
           $datetime[]=$item['ctime'];
           array_multisort($datetime,SORT_DESC,$list);
       }
       foreach($list as $key=>$item){
           $list[$key]['ctime']=date("Y-m-d H :i:s",$item['ctime']);
       }
        $data['list'] = $list;
        succ($this->output($data));
    }



    /**
     * 手动撤单
     * @param  str $sn 订单号
     * @return [type]     [description]
     */
    public function recall(){
        require_check_api('sn', $this->post);
        $sn = addslashes($this->post['sn']);
        $act = substr($sn, 0, 1) == 'B' ? 'buy' : 'sell';
        $members = D('members');
        $succModel = D('TradingSucc');
        $walletModel=D('Wallet');
        $condi = [
            'uid' => $this->uid,
            'transno' => $sn,
            'status' => 1,
        ];
        if($act == 'buy'){
            $buyModel = D('TradingBuy');
            $order = $buyModel->where($condi)->find();
            if(!$order) err('订单不足');
            //检查锁
            if(TradLockCheck(Constants::REDIS_BUYLOCK_PREFIX . $order['id'])){
                err('撤销失败请稍后重试');
            }
            //已成交分数
            $succNum = $succModel -> buySuccNum($order['transno']);
            //退回数量
            $backNum = $order['num'] - $succNum;
            if(0 >= $backNum){
                err('份数不足');
            }

            $members->startTrans();
            //标记订单状态
            $upd = [
                'status' => 3,
                'isclose' => 1,
                'ptime'=>NOW_TIME
            ];
            $result = $buyModel->where($condi)->save($upd);
            //解除余额锁定
            $user = $members->normalMember($this->userToken,'balance,balance_lock');
            $total=$backNum*$order['price']*(1+Constants::SCORE_BUY_FEE);
            $total=round($total,3);
            if($user['balance_lock'] - $total<0){
                err('撤销错误，请稍后重试');
            }
            $userupd = [
                'balance' => $user['balance'] + $total,
                'balance_lock' =>  $user['balance_lock'] - $total,
            ];
            $result = $result && $members->modify($this->userToken, $userupd);
            if($result){
                $members->commit();
                succ('撤单成功');
            }else{
                $members->rollback();
                err('撤单失败');
            }

        }else{
            $sellModel = D('TradingSell');
            $order = $sellModel->where($condi)->find();
            if(!$order) err('订单不足');
            //检查锁
            if(TradLockCheck(Constants::REDIS_SELLLOCK_PREFIX . $order['id'])){
                err('撤销失败请稍后重试');
            }
            //已成交分数
            $succNum = $succModel -> sellSuccNum($order['transno']);
            //退回数量
            $backNum = $order['num'] - $succNum;
            if(0 >= $backNum){
                err('份数不足');
            }
            $members->startTrans();
            //标记订单状态
            $upd = [
                'status' => 3,
                'isclose' => 1,
                'ptime'=>NOW_TIME
            ];
            $result = $sellModel->where($condi)->save($upd);

            //解除积分锁定
            $user=$walletModel->WalletProfiles($this->uid,'wallet_number,wallet_lock');

           if( $user['wallet_lock'] - $backNum<0){
               err('撤销错误，请稍后重试');
           }
            $result= $result && $walletModel->walletNumber($this->uid,$backNum,'in');
            $result= $result && $walletModel->WalletNumberLock($this->uid,$backNum,'out');
            if($result){
                $members->commit();
                succ('撤单成功');
            }else{
                $members->rollback();
                err('撤单失败');
            }
        }
    }

}