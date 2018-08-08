<?php

namespace DataAdmin\Controller;
use Think\Controller;

class UploadController extends BaseController{
    public function editor() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型     
        $upload->savePath  = '/images/';
        $info=$upload->upload();
        echo json_encode( array( 
         'url'=>$info['upfile']['savepath'].'/'.$info['upfile']['savename'],                           
         'original'=>$info['upfile']['savepath'].'/'.$info['upfile']['savename'],       
         'state'=>'SUCCESS',       
        ));      
    }

    public function upload() {
        $upload = new \Think\Upload();// 实例化上传类
        $key = array_shift(array_keys($_FILES));
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型     
        $upload->savePath = '/images/';
        $info=$upload->upload();

        echo $info[$key]['savepath'].''.$info[$key]['savename']; 
    }

    public function uploadFile() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     50145728 ;// 设置附件上传大小
        $upload->exts      =     array('apk');// 设置附件上传类型     
        $upload->savePath = '/app/';
        $info = $upload->upload();
        // print_r($upload);
        // exit;

        echo $info['Filedata']['savepath'].''.$info['Filedata']['savename']; 
    }

    public function uploadvideo() {
      $upload = new \Think\Upload();// 实例化上传类
      $upload->maxSize   =     3145728 ;// 设置附件上传大小
      $upload->exts      =     array('mp4');// 设置附件上传类型     
      $upload->savePath = '/video/';
      $info=$upload->upload();   
      echo $info['Filedata']['savepath'].''.$info['Filedata']['savename']; 
    }

    public function uploaduserimg() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型     
        $upload->savePath = '/userimg/';
        $info=$upload->upload();   
        echo $info['Filedata']['savepath'].''.$info['Filedata']['savename']; 
    }
    
    public function uploadexcel() {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     1024*300 ;// 设置附件上传大小
        $upload->exts      =     array('xls', 'xlsx');// 设置附件上传类型
        $upload->savePath  = '/excel/';
        $info=$upload->upload();
        if(!$info){
            $this->ajaxReturn(array('state'=>0, 'msg' => $upload->getError()),'JSON');
        }else{
            $this->ajaxReturn(array('state'=>'SUCCESS', 'url' => $info['file']['savepath'].$info['file']['savename']),'JSON');
        }
    }

    /**
     * 批量处理提现
     */
    public function readexl() {
        $url = I("url");
        header("Content-Type:text/html;charset=utf-8");
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $sheet=0;
        $filePath = "./Uploads".$url;
        if(empty($filePath) or !file_exists($filePath)){die('file not exists');}
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return ;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = array();
        for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                $addr = $colIndex.$rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex][$colIndex] = $cell;
            }
        }
        $result = true;
        M()->startTrans();
        foreach ($data as $key => $value) {
            if($key > 1) {
                if($value['A'] != NULL) {
                    if($info = M('applycash')->where(array('id'=>$value['A']))->field('state')->find()) {
                        if($info['state'] == 0) {
                            $da = array(
                                'state' => 1,
                                'ptime' => time(),
                                'mgrid' => $value['S'],
                            );
                            $result = $result && M('applycash')->where(array('id'=>$value['A']))->save($da);
                            if(!$result) {
                                break;
                            }
                        }
                    }
    
                }
            }
        }
    
        if($result) {
            M()->commit();
            succ("处理成功");
        } else {
            M()->rollback();
            err("处理失败");
        }
    } 
    
    /**
     * 批量充值余额
     */
    public function add_balance() {
        $url = I("url");
        header("Content-Type:text/html;charset=utf-8");
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");
        $sheet=0;
        $filePath = "./Uploads".$url;
        if(empty($filePath) or !file_exists($filePath)){die('file not exists');}
        $PHPReader = new \PHPExcel_Reader_Excel2007();        //建立reader对象
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return ;
            }
        }
        $PHPExcel = $PHPReader->load($filePath);        //建立excel对象
        $currentSheet = $PHPExcel->getSheet($sheet);        //**读取excel文件中的指定工作表*/
        $allColumn = $currentSheet->getHighestColumn();        //**取得最大的列号*/
        $allRow = $currentSheet->getHighestRow();        //**取得一共有多少行*/
        $data = array();
        for($rowIndex=1;$rowIndex<=$allRow;$rowIndex++){        //循环读取每个单元格的内容。注意行从1开始，列从A开始
            for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                $addr = $colIndex.$rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if($cell instanceof PHPExcel_RichText){ //富文本转换字符串
                    $cell = $cell->__toString();
                }
                $data[$rowIndex][$colIndex] = $cell;
            }
        }
        $result = true;
        M()->startTrans();
        foreach ($data as $key => $value) {
            if($key > 1) {
                if($value['A'] != NULL) {
                    $where['phone'] = $value['A'];
                    $where['isfreeze'] = 0;
                    $where['is_lock'] = 0;
                    $member = D("Members")->userinfo($where);
                    if(empty($member)){
                            break;
                    }else{
                          //查询库中是否已有该订单号
                          $find = D("Recharge")->findbyorder($value['C']);
                          if(empty($find)){
                              //插记录
                              $dataArr = array(
                                  "phone"=>$value['A'],
                                  "uid"=>$member['id'],
                                  "money"=>$value['B'],
                                  "ctime"=>time(),
                                  "ptime"=>time(),
                                  "name"=>$member['name'],
                                  "state"=>1,
                                  "ordersn"=>$value['C'],
                              );
                              $result = $result && D("Recharge")->add($dataArr);
                              //加余额
                              $nowBalance['balance'] = bcadd($member['balance'],$value['B'],3);
                              $result = $result && D("Members")->balance($member['id'],$value['B'],'in');
                              //加member 充值字段
                              $nowDeposit['deposit'] = bcadd($member['deposit'],$value['B'],3);
                              $result = $result && D("Members")->deposit($member['id'],$value['B'],'in');
                              //插日志
                              $logArr = array(
                                  'uid' => $member['id'],
                                  'changeform' => 'in',
                                  'subtype' => 2,
                                  'money' => $value['B'],
                                  'ctime' => time(),
                                  'describes' => '您充值的'.$value['B']."余额已成功到账",
                                  'balance' => $nowBalance['balance'],
                                  'money_type'=>2,
                              );
                              $result = $result && D("MembersLog")->addlog($logArr);
                          }
                    } 
                }
            }
        }    
        if($result) {
            M()->commit();
            succ("处理成功");
        } else {
            M()->rollback();
            err("处理失败");
        }
    }
}
