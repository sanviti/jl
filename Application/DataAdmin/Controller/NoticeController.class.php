<?php 
namespace DataAdmin\Controller;
class NoticeController extends BaseController{
    private $createnotice_fields = array('title', 'info ', 'add_time', );
    private $editnotice_fields = array('title', 'info', 'update_time');
    /**
     * 列表
     */
    public function lists(){
        $noticeMoldel=D("Notice");
        $page = I('p');
        $limit = C('PAGE_SIZE');
        $field = array("title,add_time,type,id");
        $data=$noticeMoldel->notice_list($page,$limit,$field);
        $count = $noticeMoldel->getcount();
        $p = getpage($count, C('PAGE_SIZE'));
        $show = $p->newshow();
        $this->assign("list",$data);
        $this->assign("page",$show);
        $this->display();
    }
    
    /**
     * 添加公告
     */
   public function add_notice() {
		if (IS_POST) {
			$data = $this -> addCheck();
			$obj = D('notice');
			if ($obj -> add($data)) {
				$this -> success("保存成功", U('Notice/lists'));
				return;
			} else {
				$this -> error('操作失败！');
				return;
			}
		}
		$this -> display();
	}

	private function addCheck() {
		$param = I('post.');
		$data = $this->checkFields($param, $this->createnotice_fields);
		$add_time = time();
		$data['title'] = trim(I('post.title', '', 'htmlspecialchars'));

		if ( D('notice')->getNoticeTitle($data['title'])) {
			$this -> error('消息已经存在');
			exit ;
		}
		if (empty($data['title'])) {
			$this -> error('消息标题不能为空');
		}
		$data['info'] = $_POST['info'];
		if (empty($data['info'])) {
			$this -> error('消息内容不能为空');
		}
		$data['add_time'] = $add_time;
        $data['type'] = I('type');
		return $data;
	}
	
	/**
	 * 修改公告
	 * @param number $id
	 */
	public function edit_notice($id = 0) {
	    if ($id = (int)$id) {
	        $obj = D('notice');
	        if (!$detail = $obj -> find($id)) {
	            $this -> error('请选择要编辑的系统消息');
	        }
	        if (IS_POST) {
	            $data = $this -> editCheck();
	            $data['id'] = $id;
	            if ($obj -> save($data)) {
	                $this -> success('操作成功', U('Notice/lists'));
	            } else {
	                $this -> error('操作失败');
	            }
	        } else {
	            $this -> assign('detail', $detail);
	            $this -> display();
	        }
	    } else {
	        $this -> error('请选择要编辑的系统消息');
	    }
	}
	
	private function editCheck() {
	    $param = I('post.');
	    $data = $this -> checkFields($param, $this -> editnotice_fields);
	    $update_time = time();
	    $data['title'] = trim(I('post.title', '', 'htmlspecialchars'));
	    if (empty($data['title'])) {
	        $this -> error('消息标题不能为空');
	    }
	    $data['info'] = $_POST['info'];
	    if (empty($data['info'])) {
	        $this -> error('消息内容不能为空');
	    }
	    $data['update_time'] = $update_time;
	    $data['type'] = I('type');
	    return $data;
	}
    
	/**
	 * 删除公告
	 */
	public function delNotice() {
		$notice = D('notice');
		$id = I('id');
		$result = $notice -> where(array("id"=>$id)) -> delete();
		if ($result) {
			succ('删除成功');
		} else {
			err('删除失败');
		}
	}
    
}
?>