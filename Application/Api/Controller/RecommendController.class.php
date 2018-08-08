<?php 
namespace Api\Controller;
header("Content-Type:text/html;charset=utf-8");
class RecommendController extends BaseController{
    /**
     * 推广首页 
     */
    public function lists(){
        $RecommModel = D("recommend");
        $uid = $this->uid;
        if(empty($uid)) err("该用户不存在");
        $member = D("Members")->profiles($this->userToken);
        if(empty($member)) err("该用户不存在");
        
        //已推荐人数
        $lead_num = D("Recommend")->lead_num($uid,$member['userlevel']);
        //今日推荐收益
        $t_income = D("Recommend")->t_income($uid,$member['userlevel']);
        //总收益
        $role = $member['userlevel'];
        if($role==1){
            $z_income = $member['pgain'];
        }elseif ($role==2){
            $z_income = $member['cgain'];
        }
        $array = array(
            'lead_num'=>$lead_num,
            't_income'=>$t_income,
            'z_income'=>$z_income
        );
        succ($this->output($array));
         //succ($array);
    }
    
    /**
     * 推广收益  
     * type(收益类型  1个人   2社区) 
     */
    public function profits(){
        $RecommModel = D("recommend");
        require_check_api("type",$this->post);
        $uid = $this->uid;
        $type=$this->post['type'];
        $page=$this->post['p'];
        $data = $RecommModel->profits_data($uid,$type,$page);
        succ($this->output($data));
        //succ($data);
    }
    
    /**
     * 推荐链接
     */
    public function recommend(){
        $uid = $this->uid;
        $model = D("recommend");
        //二维码
        $data = $model->recommends($uid);
        //推荐链接
        $member = D("Members")->profiles($this->userToken);
        $userid = $member['userid'];
        $link = $this->rec_reglink($userid);
        $recom = array(
            'qrcode'=>$data,
            'link'=>$link
        );
        succ($this->output($recom));
    }
    
    //获取推荐注册URL
    private function rec_reglink($userid){
        return 'http://bygdcc.com/Api/Web/index/invitecode/'.$userid;
    }
}
?>