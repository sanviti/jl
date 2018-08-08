<?php
namespace DataAdmin\Controller;
use Think\Controller;
use DataAdmin\Common\Lib\Constants;
class BaseController extends Controller {
    protected $_admin = array();
    protected $_allow_list = array('pwdEdit', 'userWindowLock', 'userWindowUnlock');

    protected function _initialize(){
        $this->_admin = session('dataAdmin');

        //检测登录
        $this->_check_login();

        //窗口锁定
        $this->_window_lock();

        //权限验证
        $this->_check_auth();
    }

    /**
     * 检测登录
     * @return [type] [description]
     */
    private function _check_login(){
        //验证过期
        if (empty($this->_admin)) {
           $this->error('登录失效请重新登录', U('Login/index', array(
                'url' => base64_encode(U(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,'',''))
            )));
        }
        //token失效
        $login_token = F('login_token_'.$this->_admin['id']);
        if ($this->_admin['id'] != 53) {
            if($this->_admin['login_token'] != $login_token){
                session('dataAdmin',null);
                $this->error('账号在其他设备登录，请重新登录', U('Login/index', array(
                    'url' => base64_encode(U(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,'',''))
                )));
            }
        }

    }

    /**
     * 窗口锁定
     * @return [type] [description]
     */
    private function _window_lock(){
        if (session('user_window_lock') == 1 && ACTION_NAME != 'userWindowUnlock'){
            if(IS_POST || IS_AJAX){
                $this->error('用户界面锁定中');
            }else{
                $this->assign('user_window_lock', 1);
            }
        }
    }

    private function _check_auth(){
        //权限处理
        if ($this->_admin['role'] != 0) {
            $this->_admin['menu_list'] = D('Access')->getMenuIdsByRoleId($this->_admin['role']);
            $menu_action = strtolower(CONTROLLER_NAME . '/' . ACTION_NAME);
            $menu = D('TreeNode')->select();
            $menu_id = 0;
            foreach($this->_admin['menu_list'] as $val){
				foreach ($menu as $k => $v) {
					if($v['id']==$val['node_id'])
					{
						if (strtolower($v['m'].'/'.$v['a']) == strtolower($menu_action)) {
							$menu_id = (int) $k;
							break;
						}
					}
				}
           }
           if (empty($menu_id) || $menu_id == 0) {
                if($menu_id == 0){
                    $this->error('请联系管理员为您添加该模块权限 ' . $menu_action);
                }else{
                    $this->error('很抱歉您没有权限操作模块:' . $menu[$menu_id]['title']);
                }
            }
        }
        $this->loadMenu();
        $nownav['m']=strtolower(CONTROLLER_NAME );
        $nownav['a']=strtolower(ACTION_NAME);
        $this->assign('nownav',$nownav);
    }
    private  function  loadMenu(){

       foreach ($this->_admin['menu_list'] as  $v) {
       	$node_id[]=$v['node_id'];
       }
        //超级管理员
        if($this->_admin['role'] == 0){
            $menu=D('TreeNode')->where(array("menuflag"=>1,"is_hide"=>0))->order("sort DESC")->select();
            $this->assign('menu',$menu);
        }else{

            $menu=D('TreeNode')->where(array("id"=>array('IN',$node_id),"menuflag"=>1))->select();
            $this->assign('menu',$menu);
        }
    }

    //用户窗口临时锁定
    public function userWindowLock(){
        if(IS_AJAX){
            session('user_window_lock', 1);
            $this->success();
        }
    }

    //用户窗口解除锁定
    public function userWindowUnlock(){
        if(IS_AJAX){
            $pwd = I('pwd','','trim,htmlspecialchars');
            $admin = D('Admin');
            $adminInfo = $admin->field('password,salt')->where('id = %d', $this->_admin['id'])->find();
            if($adminInfo){
                if($adminInfo['password'] == md5(md5($pwd).Constants::PUB_SALT.$adminInfo['salt'])){
                    session('user_window_lock', 0);
                    $this->success('解锁成功');
                }else{
                    $this->error('密码错误');
                }
            }else{
                $this->error('解锁失败');
            }
        }
    }

    protected function checkFields($data = array(), $fields = array()) {
        foreach ($data as $k => $val) {
            if (!in_array($k, $fields)) {
                unset($data[$k]);
            }
        }
        return $data;
    }
    protected function adminid(){
        return $this->_admin['id'];
    }


}