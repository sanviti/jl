<?php 
/**
 * 推荐
 */
namespace Api\Model;
header("Content-Type:text/html;charset=utf-8");
use Think\Model;
use Common\Lib\Constants;
class RecommendModel extends Model{
     protected $tableName = 'members';
     protected $tablePrefix = 'jl_';
    
    /**
     * 已推荐人数
     */
    public function lead_num($uid,$role){
        if($role!=""){
            if($role==1){
                $condi['leadid'] = $uid;
            }elseif($role==2){
                //社区会员
                $condi['teamid|leadid'] = $uid; 
            }
        }
        $count = $this->where($condi)->count();
        return $count;
    }
    
    /**
     * 今日推荐收益
     */
    public function t_income($uid,$role){
        if($role!=""){
            if($role==1){ //普通会员
                    $condi['leadid'] = $uid;
                    $recommend = $this->field("id")->where($condi)->select();
                    if(empty($recommend)){
                        $t_income = 0.000;
                    }else{
                        $recom_id = array();
                        foreach ($recommend as $v){
                            $recom_id[] = $v['id'];
                        }
                        $now = strtotime(date("Y-m-d",time()));
                        $tom = strtotime(date("Y-m-d",strtotime("+1 day")));
                        $where['buy_uid'] = array("IN",$recom_id);
                        $where['ctime'] = array(array("EGT",$now),array("ELT",$tom));
                        //普通
                        $pfee = Constants::PROFIT_PERSONAL;
                        $out = M("trading_succ")->where($where)->sum("num*price"); //买入表
                        $out = $pfee*$out; 
                        $t_income = number_format($out,3);
                }
            }elseif ($role==2){//社区会员
                    //查询该社区的等级
                    $team = M("members")->field("teamlevel")->where(array("id"=>$uid))->find();
                    $teamlevel = $team['teamlevel'];
                    //社区奖励
                    $condi['teamid'] = $uid;
                    $crecommend = $this->field("id")->where($condi)->select();
                    if(empty($crecommend)){
                        $ct_income = 0.000;
                    }else{
                        $crecom_id = array();
                        foreach ($crecommend as $v){
                            $crecom_id[] = $v['id'];
                        }
                        $now = strtotime(date("Y-m-d",time()));
                        $tom = strtotime(date("Y-m-d",strtotime("+1 day")));
                        $where['buy_uid'] = array("IN",$crecom_id);
                        $where['ctime'] = array(array("EGT",$now),array("ELT",$tom));
                        //社区
                        if($teamlevel==1){
                            $cfee = Constants::PROFIT_TEAMl;
                            $out = M("trading_succ")->where($where)->sum("num*price");
                            $out = $cfee*$out;
                        }elseif ($teamlevel==2){
                            $cfee2 = Constants::PROFIT_TEAMH;
                            $out = M("trading_succ")->where($where)->sum("num*price");
                            $out = $cfee2*$out;
                        } 
                        $ct_income = number_format($out,3);
                    }
                    //普通奖励
                    $where['leadid'] = $uid;
                    $precommend = $this->field("id")->where($where)->select();
                    if(empty($precommend)){
                        $pt_income = 0.000;
                    }else{
                        $recom_id = array();
                        foreach ($precommend as $v){
                            $precom_id[] = $v['id'];
                        }
                        $now = strtotime(date("Y-m-d",time()));
                        $tom = strtotime(date("Y-m-d",strtotime("+1 day")));
                        $wheres['buy_uid'] = array("IN",$precom_id);
                        $wheres['ctime'] = array(array("EGT",$now),array("ELT",$tom));
                        //普通
                        $afee = Constants::PROFIT_PERSONAL;
                        $out = M("trading_succ")->where($wheres)->sum("num*price"); //买入表
                        $out = $afee*$out;
                        $pt_income = number_format($out,3);
                    }
                 $t_income = bcadd($ct_income,$pt_income,3);
            }  
        } 
        if($t_income==""){
            $t_income = 0.00;
        }       
        return $t_income;
    }
    
    /**
     * 收益
     */
    public function profits_data($uid,$role,$page){
        //已推荐个人
        $lead_num = $this->lead_num($uid,$role);
        //今日推荐收益
        $t_income = $this->t_income($uid,$role);
        //推荐人列表
        $lists = $this->lead_lists($uid,$role,$page);
		//是否激活
		$is_pass = M("members")->field("id,is_pass")->where(array('id'=>$uid))->find();
        $array = array(
            'lead_num'=>$lead_num,
            't_income'=>$t_income,
            'is_pass'=>$is_pass['is_pass'],
            'list'=>$lists
        );
        return $array;
    }

	
    /*
     * 推荐人列表
     */
    public function lead_lists($uid,$role,$page){
        if($role==1){
            $condi['leadid'] = $uid; 
        }elseif($role==2){
            //社区
            $condi['teamid|leadid'] = $uid;
        }
        $page=empty($page)?1:$page;
        $recommend = M("members")->field("id,name,phone")->where($condi)->page($page)->limit(10)->select();
        if(empty($recommend)){
            $recommend = array();
        }else{
            foreach ($recommend as $k=>$v){
                $profit = M("trading_succ")->where(array("buy_uid"=>$v['id']))->sum("num*price");
                if($profit==""){
                    $recommend[$k]['num'] = 0.00;
                }else{
                    $recommend[$k]['num'] = $profit;
                }
            } 
        }
        return $recommend;
    }
    
    /**
     *
     * 我要推荐
     */
    public function recommends($uid){
        $user = M('members')->where(array("id"=>$uid))->find();
        $qr = $user['invite_qrcode'];
        if (empty($qr)){
            $qr = $this->create_qrcode_rec($user['userid']);
        }else{
            $qr = $user['invite_qrcode'];
        }
        return $qr;
    }
    
    /**
     * 生成推荐二维码
     */
    public function create_qrcode_rec($luid){
        $filename = md5(rand(0,9999).microtime()).'.png';
        $path = './Uploads/qrcode/'.date('Y-m-d').'/';
        if(!is_dir($path)){
            mkdir($path);
        }
        import('Vendor.phpqrcode.phpqrcode','','.php');
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        $content = "http://bygdcc.com/Api/Web/index/invitecode/".$luid;
        \QRcode::png($content, $path.$filename, $errorCorrectionLevel, $matrixPointSize,1,0);
        M('members')->where('userid="%s"',$luid)->save(array('invite_qrcode'=>ltrim($path.$filename,'.')));
        return ltrim($path.$filename,'.');
    }
}

?>