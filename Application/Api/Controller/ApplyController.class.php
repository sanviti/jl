<?php 
namespace Api\Controller;
use Common\Lib\Constants;
class ApplyController extends BaseController{
    /**
     * 卖出(提现)
     * money  卖出数量
     * cardid 银行卡id
     * cid    支给商户id
     * 
     */
    public function applycash(){
        require_check_api('money,cardid,pwd,cid,vcode', $this->post);
        $cardid = intval($this->post['cardid']);
        $cid = intval($this->post['cid']);
        $pwd = $this->post['pwd'];
        //金额检测
        $money = number_format($this->post['money'], 2, '.', '');
        if($money > 50000 || $money <= 0)err('金额错误');
        if($money % 100 > 0) err('卖出数量要是 100 的倍数');
        
        $memberModel = D('Members');
        $member = $memberModel->normalMember($this->userToken);
        if(!$member) err('操作失败');
        
        if($member['isfreeze'] == 1) {
            err('抱歉，您的账户资金已被冻结！');
        }
        
        if($member['is_lock'] == 1) {
            err('抱歉，您的账户资金已被锁定！');
        }
        $role = $member['userlevel'];
        //余额、签名
        if($member['balance']<$money) err('余额不足');
        
        //银行卡
        $bankinfo = M('bankcard')->where(array('uid'=>$member['id'],'cardid'=>$cardid))->find();
        if(!$bankinfo)err('银行卡不存在');
        
        if($bankinfo['truename'] != $member['name']) {
            err('提现失败，您的银行卡开户名与您的账户姓名不一致');
        }
        
        //二级密码
        if($member['paypwd'] == null) err('请设置交易密码');
        if($member['paypwd'] != md5password($pwd,$member['paysalt'])) err('交易密码错误');

        //判断验证码
        $codeModel = D('Validcode');
        if($codeModel -> expires($member['phone'],$this->post['vcode'],Constants::SMS_APPLY_CODE)){
            err('短信验证码错误或已失效');
        }
        
        $newBalance = $member['balance'] = bcsub($member['balance'],$money,3);
        
        //日志
        $log = array(
             'uid' => $member['id'], 
             'changeform' => 'out',
             'subtype' => 1,
             'money_type' =>2, //1金链  2余额
             'money' => $money,
             'ctime' => time(),
             'balance' => $newBalance,
             'describes' => '余额提现',
        );
        //提现申请
        $disburse = bcmul($money,(1-Constants::SCORE_APPLY_FEE),3); //到账金额
        $account = bcmul($money,Constants::SCORE_APPLY_FEE,3); //手续费
        $apply = array(
             'uid' => $member['id'],
             'money' => $money,
             'disburse' => $disburse,
             'cardid' => $bankinfo['card'],
             'rname' => $bankinfo['truename'],
             'bank' => $bankinfo['bankname'],
             'subbranch' => $bankinfo['branchname'], 
             'ctime' => time(),
             'cid'   =>$cid,
             'role'  =>$role,
             'account'=>$account
        );
        
        $signModel = D("MembersSign");
        $logModel = D("MembersLog");
        $applyModel = D("Apply");
        
        //数据操作
        M()->startTrans();
        $result = true;
        $result = $result && $memberModel->balance($this->userToken,$money,'');
        $result = $result && $logModel->adds($log);
        $result = $result && $applyModel->adds($apply);
        //给提现手续费
        $applymember = $memberModel->findAdminuser(Constants::APPLY_UID);
        $applytoken = $applymember['token'];
        $result = $result && $memberModel->balance($applytoken,$account,'in');
        if($result){
            M()->commit();
            succ('操作成功');
        }else{
            M()->rollback();
            err('操作失败'); 
        }
    }
    
    /**
     * 支给账户
     */
    public function combank_list(){
        $list = D("ComBank")->combanklist();
        succ($this->output($list));
    }
}

?>