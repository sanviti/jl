<?php
return array(

    //'SHOW_PAGE_TRACE' =>true,
    //'配置项'=>'配置值'
    /* 调试配置 */
    //'SHOW_PAGE_TRACE' => true,
    'MODULE_ALLOW_LIST'  => array('Admin','Home','Api','DataAdmin'),
    'URL_MODEL'                 =>2,    //2为去掉url中的index.php

    'DEFAULT_MODULE'        =>  'Home',  // 默认模块
    'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
    'DEFAULT_ACTION'        =>  'index', // 默认操作名称

    /* 数据库配置 */
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '47.74.144.204', // 服务器地址
    'DB_NAME'   => 't1_bygcc_cc', // 数据库名
    'DB_USER'   => 't1_bygcc_cc', // 用户名
    'DB_PWD'    => 'dxepNhdbxsjhFD7c',  // 密码 */
    'DB_PORT'   => '3306', // 端口

    /* 'DB_HOST'   => '192.168.1.48', // 服务器地址
    'DB_NAME'   => 'byjl', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '',  // 密码
<<<<<<< .mine
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => '', // 数据库表前缀
||||||| .r22
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'jl_', // 数据库表前缀
=======
   'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'jl_', // 数据库表前缀 */
>>>>>>> .r184

//    'DB_TYPE'   => 'mysql', // 数据库类型
//    'DB_HOST'   => 'localhost', // 服务器地址
//    'DB_NAME'   => 'byjl', // 数据库名
//    'DB_USER'   => 'root', // 用户名
//    'DB_PWD'    => '123',  // 密码
   'DB_PORT'   => '3306', // 端口
   'DB_PREFIX' => 'jl_', // 数据库表前缀

    /* 分页COUNT */
    'PAGE_SIZE'   => '10',

    //模版设置相关
    'TMPL_ACTION_SUCCESS'   => 'Public/dispatch_jump',
    'TMPL_ACTION_ERROR'     => 'Public/dispatch_jump',

    //加载配置文件
//    'LOAD_EXT_CONFIG' => 'site',


    'LOG_RECORD' => FALSE, // 开启日志记录
    'LOG_LEVEL'  =>'EMERG,ALERT,CRIT,ERR', // 只记录EMERG ALERT CRIT ERR 错误
    'LOG_TYPE'      =>  'File', // 日志记录类型 默认为文件方式

    'APP_DEBUG'=>true,
     'DB_FIELD_CACHE'=>false,
     'HTML_CACHE_ON'=>false,

    // redis配置
    'REDIS_HOST' => '127.0.0.1',
    'REDIS_PORT' => 6379,
    'REDIS_AUTH'=>'#YCpgFRGL&kzd*B39EsoJN3CZ48M9#%ccL7XdgUFM',


);