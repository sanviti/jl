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
        $user = M('Members');
		
        $userdata = $user->where('id = %d', $id)->find();
		
        if(empty($id) || empty($userdata)) $this->error('请选择要编辑的用户');
        if (IS_POST) {
			$data['teamlevel'] = trim(I('post.teamlevel', '', 'htmlspecialchars'));
			$data['isfreeze'] = trim(I('post.isfreeze', '', 'htmlspecialchars'));
			$data['is_lock'] = trim(I('post.is_lock', '', 'htmlspecialchars'));
			$data['remark'] = trim(I('post.remark', '', 'htmlspecialchars'));
			$data['is_pass'] = trim(I('post.is_pass', '', 'htmlspecialchars'));//是否通过
			$data['iscertify'] = trim(I('post.iscertify', '', 'htmlspecialchars'));//是否认证
			$data['is_face'] = trim(I('post.is_face', '', 'htmlspecialchars'));//面对面交易
			$user = D('Members');
			$userinfo= $user -> profiles($id, $field = 'is_pass,is_face');
			if($userinfo['is_pass'] == 1){
				$data['is_pass'] = 1;
			}else{
				if(intval($data['is_pass'])==1){
					$data['is_pass'] = $this->is_pass($id);
				}
				if($this->is_pass($id) == 1  && $data['is_face'] = 1 ) {
					$data['is_face'] = 1;
				}else{
					$data['is_face'] = 0;
				}
			}
			
			
			//开通面对面交易
			
			
			//开通面对面交易

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
		$data['iscertify'] = 3;
		$data['remark'] = $remark;
		
		if(intval($id) > 0){
			if($user ->where(array("id"=>$id))->save($data)){
			//$to = "Member/userlist/keyword/$userdata['phone']";
			 $this -> success('操作成功', U('Member/userlist',array('keyword'=>$userdata['phone'])));
			}else{
				$this -> error('操作失败');
			}
		}else{
			$this -> error('操作失败');
		}
	} 
	/*
     * 邀请列表
     */
    public function invite_list(){
		$id = I('id');
		
		$condi['teamid|leadid'] = $id;
        $recommend = M("members")->where($condi)->select();
		
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
		
		$this -> assign('totalinvite', count($recommend));
		$this -> assign('list', $recommend);
        $this -> display();
    }
    
	//社区会员激活状态
	/**
	*param $id 用户id
	*return  is_pass 1 已激活 0 未激活 2 已达到要求待申请激活
	*/
	public function is_pass($id){
		$is_pass  = 1;
		$falg  = 0;
		$falgone  = 1; 
		$falgtwo  = 1;
		$falgthree  = 1;
		
		$member = M('members')->field("is_pass,teamid,leadid")->where(array("id"=>$id))->select();
		$wallet = M('wallet')->field("wallet_number")->where(array("member_id"=>$id))->find();
		//条件1 长期持有1万的链
		if($wallet['wallet_number'] < 10000){
			$falgone  = 0;
		}
		
		
		$map['id'] = array('neq',$id);
		$map['teamid'] = $member['teamid'];
		$invite_list = M("members")->field("id,userid,name,phone")->where($map)->select();
		//条件二 社区成员达到50人以上
        $condi['teamid|leadid'] = $uid; 
        $count = M("members")->where($map)->count();
		if($count < 50){
			$falgtwo  = 0;
		}
		//条件3 社区成员用链积分换链达10万CNY
		$trading_succ = M("trading_succ")->field("sum(num)")->where(array("buy_uid"=>$id))->select();
		$total_buy = 0;
		$final_total = 0;
		foreach ($invite_list as $k => $v){
			$trading_buy = M("trading_succ")->field("num,price")->where(array("buy_uid"=>$v['id']))->select();
			for($i=0;$i<count($trading_buy);$i++){
				$trading_buy_total = $trading_buy[$i]['num'] * $trading_buy[$i]['price'];
				$total_buy += $trading_buy_total;
			}
			$final_total += $total_buy;
		}
		
		if($final_total < 100000){
			$falgthree  = 0;
		}
		if($falgone  == 1 && $falgtwo  == 1 && $falgthree  == 1){
			$falg  = 1;
		}
		//查看是否写入数据库
		if($is_pass == 1 && $falg ==1){
			$is_pass = 1;
		}else{
			$is_pass = 0;
		}
		
		return $is_pass;
	}
	
	/*
     * 导出用户
     */
    public function inviter(){
        $recommend = M("members")->field('name,phone,teamid')->select();
		
		//$recommend = M("members")->where($cond)->select();
        if(empty($recommend)){
            $data = array();
        }else{
            $recom_id = array();
            foreach ($recommend as $k=>$v){
                $inviter = M("members")->field('name,phone,teamid')->where(array("id"=>$v['teamid']))->find();
                if($profit==""){
                    $data[$k]['name'] = $recommend[$k]['name'];
                    $data[$k]['phone'] = $recommend[$k]['phone'];
                    $data[$k]['inviter'] =$inviter['name'];
                    $data[$k]['inviter_phone'] =$inviter['phone'];
                }else{
                    $data[$k]['inviter'] = "";
                    $data[$k]['inviter_phone'] ="";
                }
            } 
        }
		
		$head = array(
            '开户名',
            '手机号',
            '社区推荐人',
            '推荐人手机号',
        );
        $filename = "用户信息";
        $this->_ToExcel($filename,$head,$data);
    }
	//导出方法
    private	function _ToExcel($name,$hd,$data){
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        //对数据进行检验
        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        //检查文件名
        if(empty($name)){
            exit;
        }
    
        $date = date("Y_m_d",time());
        $name .= "_{$date}.xls";
    
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps = $objPHPExcel->getProperties();
    
        //设置表头
        $key = ord("A");
        foreach($hd as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $key += 1;
        }
    
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }
    
        $name = iconv("utf-8", "gb2312", $name);
        //重命名表
        // $objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$name\"");
        header('Cache-Control: max-age=0');
    
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
}