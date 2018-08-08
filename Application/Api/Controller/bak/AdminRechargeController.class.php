<?php 
namespace Api\Controller;
use Think\Controller;
header("Content-Type:text/html;charset=utf-8");
class AdminRechargeController extends Controller{
    //接收未处理订单
    public function addchain(){
		
        $url = "http://t1.renrengyw.com/Api/Chain/dealorder";
        $data = post($url);
		
        $results = json_decode($data,true);
        $data = $results['result']['list'];
        M()->startTrans();
        $pass = true;
        $ordersn = array();
        foreach ($data as $k=>$v){
                //根据手机号查询用户
                $member = D("Members")->getMemberByPhone($v['phone']);
                //查询该订单号是否已充值
                $findwhere['ordersn'] = $v['ordersn'];
              
                $findorder = D("Recharge")->get_data($findwhere);
                if(empty($member) || !empty($findorder)){ 
                    continue;
                }else{
                    //加充值记录
                    $order = array(
                        "uid" => $member['id'],
                        "phone" =>$member['phone'],
                        "last_name"=>$member['name'],
                        "name"=>$member['name'],
                        "money"=>$v['key'],
                        "ctime"=>time(),
                        "ordersn"=>$v['ordersn'],
                        "state"=>0,
                        "remark"=>"bydr",
                    );
                    $pass = $pass && D("Recharge")->adds($order);
                    //充值余额
                    $pass = $pass && D("Members")->balance($member['token'],$v['key'],'in');
                    //修改订单状态
                    $changeArr = array(
                        'state'=>1,
                        'ptime'=>time(),
                    );
                    $pass = $pass && D("Recharge")->changestate($v['ordersn'],$changeArr);
                    //加日志
                    $log = array(
                        "uid"=>$member['id'],
                        "changeform"=>"in",
                        "subtype"=>2,
                        "money"=>$v['key'],
                        "ctime"=>time(),
                        "describes"=>"您于".date("Y-m-d H:i",time())."充值的账户余额".$v['key']."已到账",
                        "balance"=>$member['balance']+$v['key'],
                        "money_type"=>2,
                    );
                   $pass = $pass && D("MembersLog")->adds($log);
                   if($pass){
                       $ordersn = $v['ordersn'];
					   
                   }
					if($pass){
						$urldata = array(
							'ordersn'=>$ordersn
						);
						$url = "http://t1.renrengyw.com/Api/Chain/changestatus";
						$rtn = $this->httpPost($url,$urldata);
						if($rtn){
							M()->commit();
						}else{
							M()->rollback();
						}
					}else{
						M()->rollback();
					}
		
                }
        }
        
    }
	private function httpPost($postUrl, $parms){
		$data = array();
		$header = Array("Accept:application/json","charset=UTF-8");
			$data = array();
		foreach($parms as $key=>$val){
			$datas[] = $key.'='.$val;
			if(is_array($val)){
				$data[] = $key.'='.json_encode($val);
			}else{
				$data[] = $key.'='.$val;
			}
		}
		$post_data = implode('&',$data);
		$post_datas = implode('&',$datas);
		$postUrl = $postUrl.'?'.$post_data;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL,$postUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_HEADER, 0);    //不取得返回头信息 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $parms);
		ob_start();
		curl_exec($ch);
		$result = ob_get_contents();
		ob_end_clean();
		curl_close($ch);
		return $result;
	}
}
?>