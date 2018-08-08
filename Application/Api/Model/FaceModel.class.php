<?php 
namespace Api\Model;
use Think\Model;
class FaceModel extends Model{
    protected $tableName = 'wallet';
    protected $tablePrefix = 'jl_';
    /**
     *
     * 金链钱包地址二维码
     */
    public function chain_address($uid){
        $user = $this->where(array("member_id"=>$uid,"wallet_type"=>1))->find();
        $qr = $user['wallet_address_qrcode'];
        if (empty($qr)){
            $qr = $this->create_qrcode_wallet($uid,$user['wallet_address']);
        }else{
            $qr = $user['wallet_address_qrcode'];
        }
        return $qr;
    }
    
    /**
     * 生成推荐二维码
     */
    public function create_qrcode_wallet($luid,$address){
        $filename = $luid.'.png';
        $path = './Uploads/qrcode/'.date('Y-m-d').'/';
        if(!is_dir($path)){
            mkdir($path);
        }
        import('Vendor.phpqrcode.phpqrcode','','.php');
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 6;//生成图片大小
        $content = $address;
        \QRcode::png($content, $path.$filename, $errorCorrectionLevel, $matrixPointSize,1,0);
        $this->where(array("member_id"=>$luid))->save(array('wallet_address_qrcode'=>ltrim($path.$filename,'.')));
        return ltrim($path.$filename,'.');
    }
    
    
}

?>