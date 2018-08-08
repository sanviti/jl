<?php

namespace DataAdmin\Controller;
use Think\Controller;
class VersionController extends BaseController {

    /**
     * 版本管理
     */
    public function index(){
        $verModel = M('version');
        //分页
        $pageSize = C('PAGE_SIZE');
        $count = $verModel->count();
        $p = getpage($count,$pageSize);
        $show = $p->show();
        $list = $verModel->alias('v')->field("v.*,adm.username AS managername")->join("LEFT JOIN __ADMIN__ adm ON adm.id = v.managerid")->page(I('p'))->limit($pageSize)->order('v.code DESC')->select();
        $this->assign('page',$show);
        $this->assign('list',$list);
//        dump($list);die;
        $this->display();
    }
    /**
     * 版本添加
     */
    public function add(){
        if(IS_AJAX){
            $verModel = M('version');
            $verModel->create();
            $verModel->dateline = NOW_TIME;
            $verModel->managerid = $this->adminid();
            if($verModel->add()){
                $this->success('添加成功');
            }else{
                $this->success('添加失败');
            }
        }
        $this->display();
    }
    /**
     * 版本修改
     */
    public function edit(){
        if($error = admin_require_check('code')) $this->error($error);
        $verModel = M('version');
        $condi['code'] = I('code');
        if(IS_AJAX){
            $verModel->create();
            $verModel->dateline = NOW_TIME;
            $verModel->managerid = $this->adminid();
            if($verModel->where($condi)->save()){
                $this->success('修改成功');
            }else{
                $this->success('修改失败');
            }
            exit;
        }
        $data = $verModel->where($condi)->find();
        $this->assign('data',$data);
        $this->display('add');
    }

    /**
     * 版本删除
     */
    public function del(){
        if($error = admin_require_check('code')) $this->error($error);
        $verModel = M('version');
        $condi['code'] = I('code');
        if($verModel->where($condi)->delete()){
            succ("删除成功");
        }else{
            err("删除失败");
        }
    }

}