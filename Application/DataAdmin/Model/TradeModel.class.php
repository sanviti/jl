<?php 
namespace DataAdmin\Model;
class TradeModel extends CommonModel{
    protected $tableName = 'trading_succ';
    protected $tablePrefix = 'jl_';
    
    //成交记录
    public  function succlist($where,$page,$limit){
        $data = $this->where($where)->page($page)->limit($limit)->order("ctime asc")->select();
        foreach ($data as $k=>$v){
            $bmember = D("Members")->profiles($v['buy_uid']);
            $data[$k]['buy_uid'] = $bmember['userid'];
            $data[$k]['buyname'] = $bmember['name'];
            
            $smember = D("Members")->profiles($v['sell_uid']);
            $data[$k]['sell_uid'] = $smember['userid'];
            $data[$k]['sellname'] = $smember['name'];
        }
        return $data;
    }
    
    //成交记录数量
    public function succ_count($where){
        return $this->where($where)->count();
    }
 
    //卖出记录
    public  function selllist($where,$page,$limit){
        $data = M("trading_sell")->where($where)->page($page)->limit($limit)->order("ctime ASC")->select();
        foreach ($data as $k=>$v){
            $member = D("Members")->profiles($v['uid']);
            $data[$k]['uid'] = $member['userid'];
            $data[$k]['name'] = $member['name'];
        }
        return $data;
    }

    //卖出记录数量
    public function sell_count($where){
        return M("trading_sell")->where($where)->count();
    }
    
    //买入记录
    public  function buylist($where,$page,$limit){
        $data = M("trading_buy")->where($where)->page($page)->limit($limit)->order("ctime asc")->select();
        foreach ($data as $k=>$v){
            $member = D("Members")->profiles($v['uid']);
            $data[$k]['uid'] = $member['userid'];
            $data[$k]['name'] = $member['name'];
        }
        return $data;
    }
    
    //卖出记录数量
    public function buy_count($where){
        return M("trading_buy")->where($where)->count();
    }
    
    //交易量走线图
    public function getnum(){
        $orders = $this->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') as cc,sum(num) as num")->group("cc")->select();
        return $orders;
    }
    
    //交易额走线图
    public function getmoney(){
        $orders = $this->field("FROM_UNIXTIME(ctime,'%Y-%m-%d') as cc,sum(num*price) as num")->group("cc")->select();
        return $orders;
    }
    
}

?>