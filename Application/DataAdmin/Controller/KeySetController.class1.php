<?php 
namespace DataAdmin\Controller;
use Think\Controller;
class KeySetController extends BaseController{
	

 //添加KEY价格
    public function addCoinPrice() {
        $KeySetPriceModel = M("trading_price");
        $count = $KeySetPriceModel->count(); // 查询满足要求的总记录数
        $list = $KeySetPriceModel->field('id,price,ctime')->page(I('get.p'))->order('ctime desc')->limit(10)->select();

		$zero_time = strtotime(date("Y-m-d"));
		for($i=0;$i<count($list);$i++){
			$list[$i]['price'] =round($list[$i]['price'],2);
			$list[$i]['editable'] =0;
			if($list[$i]['ctime']+1>$zero_time){
				$list[$i]['editable'] = 1;
			}
		}

        $p = getpage($count, C('PAGE_SIZE'));;
        $show = $p->show();

        $adminInfo=session("KEY_role");
        $KEYusername=$adminInfo['name'];
        $this->assign("KEYusername",$KEYusername);
        $this->assign("tablename","设置KEY价格");
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //保存KEY
    public function savePrice(){
		$price = trim(I('coinPrice'));
		if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
			$this->error('价格最多为2位小数');
		}
        $coinData = array(
            'price'=>$price,
            'ctime'=>time()
        );
		$start_time = strtotime(date("Y-m-d"));
		$end_time = strtotime(date("Y-m-d"))+3600*24;
		$condi['_string'] = " ctime > $start_time and ctime < $end_time";
		$cond['_string'] = " ctime >= $start_time and ctime <= $end_time";
		$count =  M('trading_price')->field('id,price,ctime')->where($condi)->count();
		$find =  M('trading_price')->field('id,price,ctime')->where($cond)->find();
		
		if($find['ctime'] == $start_time){//修改昨天设置的明日价格
					$savedata['ctime'] = time();
					$savedata['price'] = $price;
					M('trading_price')->where($cond)->save($savedata);  
		}else if($count==0){//每天只能添加一次
			if(trim(I('coinPrice')) > 0){
					$res = M('trading_price')->add($coinData);  
			}
		}else{
			$this->error('每天价格只能设置一次');
		}
			
        
        $this->redirect('KeySet/addCoinPrice');
    }
	//修改价格
	public function editprice(){
		$editprice = trim(I('editprice'));
		$priceid = trim(I('priceid'));
		if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $editprice)) {
			$this->error('价格最多为2位小数');
		}
		$condi['id'] = $priceid;
		$find = M('trading_price')->where($condi)->find();
		$end_time = strtotime(date("Y-m-d"))+3600*24;
		$time = time();
		if($find){
			if($end_time== $find['ctime']){
				$time = $find['ctime'];
			}
		}
		$data = array(
            'price'=>$editprice,
            'ctime'=>$time
        );

		
		
		if(trim(I('editprice')) > 0){
			$res = M('trading_price')->where($condi)->save($data);  
		}
		
        
        $this->redirect('KeySet/addCoinPrice');
    }
	
	
	    //保存KEY
    public function save_next_price(){
		$price = trim(I('coinPrice'));
		$next_start_time = strtotime(date("Y-m-d"))+3600*24;
		$next_end_time = strtotime(date("Y-m-d"))+3600*24*2;
		if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $price)) {
			$this->error('价格最多为2位小数');
		}
        $coinData = array(
            'price'=>$price,
            'ctime'=>$next_start_time
        );
		
		$condi['_string'] = " ctime >= $next_start_time and ctime <= $next_end_time";
		$count =  M('trading_price')->field('id,price,ctime')->where($condi)->count();
		
		if($count==0){//每天只能价格一次
			if(trim(I('coinPrice')) > 0){
			$res = M('trading_price')->add($coinData);  
			}
		}else{
			$this->error('每天价格只能设置一次');
		}
			
        
        $this->redirect('KeySet/addCoinPrice');
    }
	
    //KEY价格走势曲线图
    public function showPrice(){
            $search_stime = I('stime');
            $search_etime =I('etime');
            if($search_stime){
               $where['ctime'] =array('egt',strtotime($search_stime));
            }
            if($search_etime){
                $stamp_etime = strtotime($search_etime)+3600*24-1;
                if(!$search_stime){
                    $search_stime = time()-3600*24*7;
                }
                $where['ctime'] =array(array('egt',strtotime($search_stime)),array('elt',$stamp_etime));
            }
            if(!$search_stime && !$search_etime){
                    $search_stime = time()-3600*24*15;
                    $search_etime = time();
                    $where['ctime'] =array(array('egt',$search_stime),array('elt',$search_etime));
            }
            
            $product_info = M('trading_price')->where($where)->order('ctime asc')->getField('from_unixtime(ctime) as create_time,price');//生成create_time为键，price为值的数组  
            $info = M('trading_price')->where('1')->find();  
            $create_time = array_keys($product_info);//取得数组键  
            $price = array_values($product_info);//取得值  
            $title = "KEY价格走势图";  
            $subtitle = "KEY近期价格走势图";  
            $data = array(  
                'title'=>$title,  
                'subtitle'=>'KEY近期价格走势图',  
                'categories'=>json_encode($create_time),  
                'yAxis'=>array('title'=>'人民币（元）','plotLines'=>'#808080'),  
                'tooltip'=>'（元）',  
                'legend'=>'',  
                'series'=>array('name'=>$title,'data'=>str_replace("\"", "",json_encode($price))),
            );  
            $adminInfo=session("KEY_role");
            $KEYusername=$adminInfo['name'];
            $this->assign("KEYusername",$KEYusername);
            $this->assign('data',$data); 
            $this->display();
    }
	
	//添加累计交易量
    public function add_coin_amount() {
        $trading_amountModel = M("trading_amount");
        $count = $trading_amountModel->count(); 
        $list = $trading_amountModel->field('id,amount,ctime')->page(I('get.p'))->order('ctime desc')->limit(10)->select();

        $p = getpage($count, C('PAGE_SIZE'));;
        $show = $p->show();

        $adminInfo=session("KEY_role");
        $KEYusername=$adminInfo['name'];
        $this->assign("KEYusername",$KEYusername);
        $this->assign("tablename","设置KEY价格");
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }
    //保存累计交易量
    public function save_trading_amount(){
		$amount = trim(I('amount'));
		$trading_amountModel = M("trading_amount");
		if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $amount)) {
			$this->error('价格最多为2位小数');
		}
		$last_time = strtotime(date("Y-m-d"));
		$condi['ctime'] = array('gt',$last_time);;
		$list = $trading_amountModel->field('id,amount,ctime')->order('ctime desc')->limit(1)->find();
		$count = $trading_amountModel->field('id,amount,ctime')->where($condi)->count();
		
		if($count==0){
			$firstdata = array(
				'amount'=>0,
				'ctime'=>$last_time
			);
			M('trading_amount')->add($firstdata);
			$total = floatval($amount);		
		}else{
			$find = $trading_amountModel->field('id,amount,ctime')->where($condi)->order('ctime desc')->find();
			$total = floatval($amount) + $find['amount'];
		}
		$find = $trading_amountModel->field('id,amount,ctime')->where($condi)->order('ctime desc')->find();
		
        if($count==1 && $find['ctime'] == strtotime(date("Y-m-d"))-1){
			
			$coinData = array(
            'amount'=>$total,
            'ctime'=>time()
			);
			$res = M('trading_amount')->where(array('id'=>$find['id']))->save($coinData); 
			$this->redirect('KeySet/add_coin_amount');			
		}

		$coinData = array(
            'amount'=>$total,
            'ctime'=>time()
        );
        if(trim(I('amount')) > 0){
          $res = M('trading_amount')->add($coinData);  
        }
        $this->redirect('KeySet/add_coin_amount');
    }
	
}
?>