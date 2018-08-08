<?php
namespace Common\Lib;
/**
 * 全局常量
 */
class Constants{
    # AUTH_TOKEN 登录令牌
    # USER_TOKEN 用户令牌
    
    const PUB_SALT = '#YGYH3i8Y0!z3*'; //公共盐
    const BASE_URL = 'http://yhg.lxy.com';
    const PUB_CACHE_TIME = 300; //缓存时间 秒
    const AUTH_TOKEN_TIME = 604800; //session token过期时间
    const SMS_INTERVAL_TIME = 80; //短信发送间隔 单位秒
    const SMS_EXPIRE_TIME = 300; //验证码有效期

    const IMAGE_CODE_EXPIRE_TIME = 300; //图形验证码有效期

    #短信类型
    const SMS_REGISTER_CODE = 1; //注册验证码   
    const SMS_FINDPWD_CODE = 2; //找回及设置密码验证码
	const SMS_FINDPAYPWD_CODE = 3; //找回及设置交易密码验证码
	const SMS_APPLY_CODE = 4; //提现验证码

    #错误代码
    const ERRCODE_AUTHTOKEN_VOID = 10000; //登录失效
    const ERRCODE_MEMBER_VOID = 10001; //用户不存在
    const ERRCODE_MEMBER_LOCK = 10002; //用户锁定
    const ERRCODE_SIGNTRUE_VOID = 10003; //签名失效

    #加密
    const ENCRYP_TEXT_KEYNAME = 'entext'; //密文POST字段



    #短信模板ID
    const SMSTEMPLATE_REG_VCODE = 244565;
    const SMSTEMPLATE_APPLYCASH_VCODE = 244565;
    const SMSTEMPLATE_FINDPWD_VCODE = 244565;
    const SMS_APPLY_VCODE = 244565;


    #交易中心挂单状态1交易中 2交易成功 3已撤单
    const ORDER_INTRADING = 1;
    const ORDER_SUCCESS = 2;
    const ORDER_CANCEL = 3;

    #交易时间 每周一至周日10:00—16:30
    const ORDER_TRANDING_STIME=36000;
    const ORDER_TRANDING_ETIME=59400;

    #卖出手续费
    const SCORE_SELL_FEE = 0.1; //10%
    #买入手续费
    const SCORE_BUY_FEE = 0.02; //2%
    
    
    #卖出手续费(提现)
    const SCORE_APPLY_FEE = 0.05; //0%
    
    #交易锁定 redis
    const REDIS_TRADLOCK_TIME = 60;

    const REDIS_UPDATE_ACCOUNT_PREFIX = 'UPDATE_ACCOUNT_';
    const REDIS_BUYLOCK_PREFIX = 'TRADING_BUY-';
    const REDIS_SELLLOCK_PREFIX = 'TRADING_SELL-';
    const REDIS_PLANLOCK_TIME = 3600;
    const REDIS_PLANLOCK_NAME = 'TRADING_PLAN_SELL';
    const REDIS_PLANFAIL_NAME = 'TRADING_PLAN_FAIL_NUM';

    #账户日志类型
    // [余额]现金充值  1余额提现    2充值余额   3充值金链   4用户转赠金链  5受赠金链 6用户转赠余额 7受赠余额 8余额提现失败 9交易
    
    //收益
    const PROFIT_PERSONAL = 0.02; //用户推广
    const PROFIT_TEAMl = 0.01; //初级团队奖励
    const PROFIT_TEAMH = 0.02; //高级团队奖励
    
    //系统注册用户id
    const APPLY_UID = 3; //提现手续费插入ID
    const FACE_UID = 4;  //面对面手续费插入ID


}