<?php
namespace DataAdmin\Controller;
header("Content-Type:text/html;charset=utf-8");
use DataAdmin\Common\Lib\Constants;
class MemberController extends BaseController {
    //角色
    private $createrole_fields = array('name', 'status', 'remark', 'pid');
    private $editrole_fields = array('name', 'status', 'remark');
    //用户
    private $createuser_fields = array('user', 'password', 'username', 'role', 'note', 'state', 'add_time', 'last_logintime', 'last_loginip');
    private $edituser_fields = array('user', 'password','role', 'username', 'note', 'state', 'update_time');
    //系统消息
    private $createnotice_fields = array('title', 'info ', 'add_time', );
    private $editnotice_fields = array('title', 'info', 'update_time');
    
    /**
     * 用户管理
     */
    public function userlist(){
		
		$keyword = I('keyword');
		$userlevel = I('userlevel');
		$iscertify = I('iscertify');
		
        $members = D('members');

		
		if($userlevel!=""){
			$condi['userlevel'] = $userlevel;
			$condurl['userlevel'] =$userlevel;
		}
		
		if($iscertify!=""){
			$condi['iscertify'] =$iscertify;
			$condurl['iscertify'] =$iscertify;
		}
		if($keyword!=""){
			$condurl['keyword'] =$keyword;
		}
		
		$condi['_string']="(phone like '%$keyword%' or name like '%$keyword%') ";
        $list = $members -> where($condi) -> page(I('get.p')) -> order('id desc') -> limit(C('PAGE_SIZE')) -> select();
		
        $count = $members -> count();
		//$count = count($list);
		$count = $members->where($condi)->count();
        $p = getpage($count, C('PAGE_SIZE'),$condurl);
        $show = $p->newshow();
		
		
        $this -> assign('page', $show);
        $this -> assign('keyword', $keyword);
        $this -> assign('userlevel', $userlevel);
        $this -> assign('iscertify', $iscertify);
        // 赋值分页输出
        $this -> assign('list', $list);
        
        $this -> display();
    }

    public function edituser($id = 0) {
        $id = I('id');
        $user = M('members');
		
        $userdata = $user->where('id = %d', $id)->find();
        if(empty($id) || empty($userdata)) $this->error('请选择要编辑的用户');
        if (IS_POST) {
			$data['teamlevel'] = trim(I('post.teamlevel', '', 'htmlspecialchars'));
			$data['isfreeze'] = trim(I('post.isfreeze', '', 'htmlspecialchars'));
			$data['is_lock'] = trim(I('post.is_lock', '', 'htmlspecialchars'));
			$data['remark'] = trim(I('post.remark', '', 'htmlspecialchars'));
			$data['iscertify'] = 1;
            if ($user ->where(array("id"=>$id))->save($data)) {
                $this -> success('操作成功', U('Member/userlist'));
            } else {
                $this -> error('操作失败');
            }
        } else {
            $this -> assign('detail', $userdata);
            $this -> display();
        }  
    }
	//审核不通过
	 public function unpass(){
		$id = I('id');
		$remark = I('remark');
        $user = M('members');
		$userdata = $user->field('phone')->where('id = %d', $id)->find();
		$data['iscertify'] = 0;
		$data['remark'] = $remark;
		
		if(intval($id) > 0){
			if($user ->where(array("id"=>$id))->save($data)){
			//$to = "Member/userlist/keyword/$userdata['phone']";
			 succ('操作成功');
			 //$this -> success('操作成功', U('Member/userlist',array('keyword'=>$userdata['phone'])));
			}else{
			err('操作失败');
				//$this -> error('操作失败');
			}
		}else{
			err('操作失败');
			//$this -> error('操作失败');
		}
	} 
	/*
     * 邀请列表
     */
    public function invite_list(){
		//$id = I('id');
		$id = 1239;
		$member = M('members')->field("userlevel")->where(array("id"=>$id));
        if($role==1){
            $condi['leadid'] = $id; 
        }elseif($role==2){
            //社区
            $condi['teamid|leadid'] = $id;
        }
        $recommend = M("members")->field("id,userid,name,phone")->where($condi)->select(false);var_dump($recommend);die;
        if(empty($recommend)){
            $data = array();
        }else{
            $recom_id = array();
            foreach ($recommend as $k=>$v){
                $profit = M("trading_succ")->where(array("buy_uid"=>$v['id']))->sum("num*price");
                if($profit==""){
                    $recommend[$k]['num'] = 0.00;
                }else{
                    $recommend[$k]['num'] = $profit;
                }
            } 
        }
		
		$this -> assign('recommend', $recommend);
        $this -> display();
    }
    
}