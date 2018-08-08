<?php
namespace DataAdmin\Controller;
header("Content-Type:text/html;charset=utf-8");
use DataAdmin\Common\Lib\Constants;
class AuthController extends BaseController {
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
     * 角色管理
     */
      public function role() {
		$role = D('Role');
		$count = $role -> getCount();
		$p = getpage($count, C('PAGE_SIZE'));
		$show = $p -> show();
		$list = $role->rolelist(I('get.p'),C('PAGE_SIZE'),'id desc');
		$this -> assign('page', $show);
		// 赋值分页输出
		$this -> assign('list', $list);
		$this -> display();
    }

    /**
     * 角色添加
     */
    public function addrole(){
        if (IS_POST) {
            $data = $this->addroleCheck();
            $obj = D('role');
            if ($obj -> add($data)) {
                $this -> success("保存成功", U('Auth/role'));
                return;
            } else {
                $this -> error('操作失败！');
                return;
            }
        }
        $this -> display();
    }

    private function addroleCheck() {
        $param = I('post.');
        $data = $this -> checkFields($param, $this -> createrole_fields);
        $data['name'] = trim(I('post.name', '', 'htmlspecialchars'));
        if ( D('role') -> getRoleName($data['name'])) {
            $this -> error('角色名已经存在');
            exit ;
        }
        if (empty($data['name'])) {
            $this -> error('角色名不能为空');
        }
        $data['status'] = $data['status'];
        $data['remark'] = trim(I('post.remark', '', 'htmlspecialchars'));
        $data['pid'] = 0;
        return $data;
    }
    
    /**
     * 角色修改
     */
    public function editrole($id = 0) {
		if ($id = (int)$id) {
			$obj = D('role');
			if (!$detail = $obj -> find($id)) {
				$this -> error('请选择要编辑的角色');
			}
			if (IS_POST) {
				$data = $this -> editroleCheck();
				$data['id'] = $id;
				if ($obj -> save($data)) {
					$this -> success('操作成功', U('Auth/role'));
				} else {
					$this -> error('操作失败');
				}
			} else {
				$this -> assign('info', $detail);
				$this -> display();
			}
		} else {
			$this -> error('请选择要编辑的角色');
		}
	}
    
    private function editroleCheck() {
        $param = I('post.');
        $data = $this -> checkFields($param, $this -> editrole_fields);
        $data['name'] = trim(I('post.name', '', 'htmlspecialchars'));
        if (empty($data['name'])) {
            $this -> error('角色名不能为空');
        }
        $data['remark'] = trim(I('post.remark', '', 'htmlspecialchars'));
        $data['status'] = $data['status'];
        return $data;
    }
    
    /**
     * 设置
     */
    public function setting() {
        $access = D('access');
        $treenode = D('treenode');
        if ($_POST) {
            $ID = I('post.id');
            $getrule = I('post.rule');
            $access -> where(array('role_id' => $ID)) -> delete();
            for ($i = 0; $i < count($getrule); $i++) {
                $data['role_id'] = $ID;
                $data['node_id'] = $getrule[$i];
                $result = $access -> add($data);
            }
            if ($result) {
                $this -> success("权限修改成功", U('Auth/role'), 2);
            } else {
                $this -> error("权限修改失败.....");
            }
        }
        else {
            $id = I('get.id');
            $nodeids = $access -> where('role_id = ' . $id . '') -> field('node_id') -> select();
            $ids = '';
            foreach ($nodeids as $val) {
                $ids = '#' . $ids . $val['node_id'] . '#';
            }
            $data = $treenode -> field('id,title,pid,menuflag')->where(array('is_hide'=>0)) -> select();
            $this -> assign('list', $data);
            $this -> assign('group', $ids);
            $this -> assign('role', $id);
            $this -> display();
        }
    }
    
    /**
     * 用户管理
     */
    public function userlist(){
        $role = D('role');
        $admin = D('admin');
        $count = $admin -> count();
        $p = getpage($count, C('PAGE_SIZE'));
        $show = $p -> show();
        $list = $admin -> page(I('get.p')) -> order('id desc') -> limit(C('PAGE_SIZE')) -> select();
        $list2 = $role -> select();
        $this -> assign('page', $show);
        // 赋值分页输出
        $this -> assign('list', $list);
        $this -> assign('list2', $list2);
        $this -> display();
    }
    
    /**
     * 添加用户
     */
    public function adduser() {
        $role = D('role');
        $list = $role -> where(array('status' => 1)) -> select();
        if (IS_POST) {
            $data = $this -> adduserCheck();
            $obj = D('admin');
            $user = M('role_user');
            $result = $obj -> add($data);
            if ($result) {
                $data2['role_id'] = htmlspecialchars($data['role']);
                $data2['user_id'] = $result;
                $user -> add($data2);
                $this -> success("保存成功", U('Auth/userlist'));
                return;
            } else {
                $this -> error('操作失败！');
                return;
            }
        }
        $this -> assign('list', $list);
        $this -> display();
    }
    
    private function adduserCheck() {
        $param = I('post.');
        $data = $this->checkFields($param,$this->createuser_fields);
        $add_time = time();
        $ip = get_client_ip();
        $data['user'] = trim(I('post.user', '', 'htmlspecialchars'));
        if ( D('Admin')->where("user = '%s'", $data['user'])->find()) {
            $this -> error('用户名已经存在');
        }
        $token = $this->_admin = session('dataAdmin');
        $salt = strtoupper(substr(md5(create_code(4)),0,6));
        $data['password'] = md5(I('password', '', 'trim,md5').Constants::PUB_SALT.$salt);
        $data['salt'] = $salt;
        if (empty($data['password'])) {
            $this -> error('密码不能为空');
        }
        $data['role'] = htmlspecialchars($data['role']);
        $data['note'] = trim(I('post.note', '', 'htmlspecialchars'));
        $data['username'] = trim(I('post.username', '', 'htmlspecialchars'));
        $data['state'] = $data['state'];
        $data['add_time'] = $add_time;
        $data['last_logintime'] = $add_time;
        $data['last_loginip'] = $ip;
        $data['update_time'] = 0;
        $data['login_token'] = $token['login_token'];
        return $data;
    }
    
    public function edituser($id = 0) {
        $id = I('id/d');
        $obj = D('admin');
        $role = D('role');
        $user = M('role_user');
        $list = $role -> select();
        $admin = $obj->where('id = %d', $id)->find();
        if(empty($id) || empty($admin)) $this->error('请选择要编辑的用户');
        if (IS_POST) {
            $data = $this -> edituserCheck($admin);
            if ($obj ->where(array("id"=>$id))->save($data)) {
                $data2['role_id'] = htmlspecialchars($data['role']);
                $user -> where(array('user_id' => $id)) -> save($data2);
                $this -> success('操作成功', U('Auth/userlist'));
            } else {
                $this -> error('操作失败');
            }
        } else {
            $this -> assign('list', $list);
            $this -> assign('detail', $admin);
            $this -> display();
        }  
    }
    
    private function edituserCheck($admin) {
        $password = I('password', '','trim');
        $ip = get_client_ip();
        $data['user'] = trim(I('post.user', '', 'htmlspecialchars'));
        if ( D('Admin')->where("user = '%s' and id <> %d", $data['user'],$admin['id'])->find()) {
            $this -> error('用户名已经存在');
        }
        if ($admin['password'] == $password){
            $data['password'] = $password;
        }else{
            $data['password'] = md5(md5($password).Constants::PUB_SALT.$admin['salt']);
        }
        $data['note'] = trim(I('post.note', '', 'htmlspecialchars'));
        $data['username'] = trim(I('post.username', '', 'htmlspecialchars'));
        $data['userimg'] = trim(I('post.userimg', '', 'htmlspecialchars'));
        $data['state'] = I('state');
        $data['role'] = I('role');
        $data['update_time'] = time();
        return $data;
    }
    
    public function delrole() {
        $role = D('role');
        $id = I('get.id');
        $result = $role -> where('id =' . $id . '') -> delete();
        if ($result) {
            $this -> success('删除成功', U('Auth/role'));
        } else {
            $this -> error('删除失败');
        }
    }
    
    public function deluser() {
        $admin = D('admin');
        $id = I('id');
        $result = $admin -> where(array("id"=>$id)) -> delete();
        if ($result) {
            succ("删除成功");
        } else {
            err('删除失败');
        }
    }
    
    /**
     *  修改用户密码
     */
 //修改密码
    public function pwdEdit(){
        if(IS_AJAX){
            $oldPwd = I('oldpwd','','trim');
            $newPwd = I('newpwd','','trim');
            $confirmPwd = I('cpwd','','trim');
            if(strlen($newPwd) < 6) $this->error('密码长度必须大于等于六位');
            if($newPwd != $confirmPwd) $this->error('两次密码不一致');
            $admin = D('Admin');
            $adminInfo = $admin->field('password,salt')->where('id = %d', $this->_admin['id'])->find();
            if($adminInfo['password'] != md5(md5($oldPwd).Constants::PUB_SALT.$adminInfo['salt'])){
                $this->error('原密码不正确');
            }

            $salt = strtoupper(substr(md5(create_code(4)),0,6));
            $data['password'] = md5(md5($newPwd).Constants::PUB_SALT.$salt);
            $data['salt'] = $salt;
            $result = $admin->where('id = %d', $this->_admin['id'])->save($data);
            if($result){
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }
        $this->display();
    }
}