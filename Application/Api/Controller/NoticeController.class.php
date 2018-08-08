<?php 
namespace Api\Controller;
use Think\Controller;
class NoticeController extends Controller{
    /**
     * 资讯列表
     */
    public function lists(){
        $model = D("Notice");
        $page = I("p");
        $data = $model->lists($page,10);
        $result = array(
            'list'=>$data
        );
        succ($result);
    }
    /**
     * 资讯详情
     */
    public function detail(){
        require_check_post("id");
        $model = D("Notice");
        $id = intval(I("id"));
        $detail = $model->detail($id);
        succ($detail);
    }
}

?>