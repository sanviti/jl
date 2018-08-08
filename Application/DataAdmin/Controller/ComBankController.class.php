<?php 
namespace DataAdmin\Controller;
class ComBankController extends  BaseController{
    /**
     * 公司银行卡列表
     */
    public function index(){
        $data = D("ComBank")->lists();
        $this->assign("list",$data);
        $this->display();
    }
    
    /**
     * 添加
     */
    public function adds(){
        if (IS_POST) {
			$data = $this -> add_combank();
			$obj = D('ComBank');
			if ($obj->add_combank($data)) {
				$this -> success("保存成功", U('ComBank/index'));
				return;
			} else {
				$this -> error('操作失败！');
				return;
			}
		}
		$this -> display();
    }
    /**
     * 执行添加
     */
    public function add_combank(){
        $param = I('post.');
        $field = array('bankname','subbank','card','truename','type');
		$data = $this->checkFields($param,$field);
		
		$data['ctime'] = time();
		$data['card'] = trim(I('post.card', '', 'htmlspecialchars'));
		$data['name'] = trim(I('post.name', '', 'htmlspecialchars'));
		$data['bankname'] = trim(I('post.bankname', '', 'htmlspecialchars'));
		$data['subbank'] = trim(I('post.subbank', '', 'htmlspecialchars'));
		$data['truename'] = trim(I('post.truename', '', 'htmlspecialchars'));

		/* if ( D('ComBank')->getBankcard($data['card'])) {
			$this -> error('银行卡已经存在');
			exit ;
		} */
		/* if (empty($data['name'])) {
		    $this -> error('名称不能为空');
		    exit ;
		}
		if (empty($data['bankname'])) {
			$this -> error('银行卡名称不能为空');
			exit ;
		}
		if (empty($data['truename'])) {
		    $this -> error('开户人不能为空');
		    exit ;
		} */
        $data['type'] = I('type');
		return $data; 
    }
    
    /**
     * 修改银行卡
     * @param number $id
     */
    public function edits($id = 0) {
        if ($id = (int)$id) {
            $obj = D('ComBank');
            if (!$detail = $obj->getinfo($id)) {
                $this -> error('请选择要编辑的系统消息');
            }
            if (IS_POST) {
                $data = $this -> edit_combank();
                $data['id'] = $id;
                if ($obj -> save($data)) {
                    $this -> success('操作成功', U('ComBank/index'));
                } else {
                    $this -> error('操作失败');
                }
            } else {
                $this -> assign('data', $detail);
                $this -> display();
            }
        } else {
            $this -> error('请选择要编辑的系统消息');
        }
    }
    
    private function edit_combank() {
        $param = I('post.');
        $field = array('bankname','subbank','card','truename','type');
        $data = $this -> checkFields($param,$field);
        $data['ctime'] = time();
        $data['card'] = trim(I('post.card', '', 'htmlspecialchars'));
        $data['name'] = trim(I('post.name', '', 'htmlspecialchars'));
		$data['bankname'] = trim(I('post.bankname', '', 'htmlspecialchars'));
		$data['subbank'] = trim(I('post.subbank', '', 'htmlspecialchars'));
		$data['truename'] = trim(I('post.truename', '', 'htmlspecialchars'));
        /* if (empty($data['bankname'])) {
			$this -> error('银行卡名称不能为空');
			exit ;
		}
		if (empty($data['truename'])) {
		    $this -> error('开户人不能为空');
		    exit ;
		} */
		if (empty($data['name'])) {
		    $this -> error('名称不能为空');
		    exit ;
		}
		/* if (empty($data['card'])) {
		    $this -> error('卡号不能为空');
		    exit ;
		} */
        $data['type'] = I('type');
        return $data;
    }
    
    /**
     * 删除公司银行卡
     */
    public function delcombank() {
        $notice = D('ComBank');
        $id = I('id');
        $result = $notice->deletes($id);
        if ($result) {
            succ('删除成功');
        } else {
            err('删除失败');
        }
    }
}

?>