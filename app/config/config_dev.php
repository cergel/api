<?php
//定义常量，控制当前应用处在什么环境：local,dev,pre,master
defined("APP_ENV") or define("APP_ENV", 'dev');
require(__DIR__ . DS . APP_ENV . DS . 'apiUrl.php'); //跟环境有关的常量配置
require(__DIR__ . DS . 'constant.php');        //redis常量及一些系统常量
require(__DIR__ . DS . 'errorConstant.php');   //获得错误信息定义常量
$global_config_app = [];
$app_channel_ids = ['8', '9', '80', '84'];
$global_config = [
    'db' => require(__DIR__ . DS . APP_ENV . DS . 'db.php'),  //数据库配置放这里
    'redis' => require(__DIR__ . DS . APP_ENV . DS . 'redis.php'),//redis配置放这里
    'errorCode' => array_merge(require(__DIR__ . DS . 'errorCodeApp.php'), require(__DIR__ . DS . 'errorCode.php')),
    'filters' => [
        'RequestParamsFilter', //参数验证
//        'InterfaceLimitFilter',//接口访问频度限制
    ],
    //日志路径的根文件夹
    'logRootPath' => '/data/logs/wepiao_api/',
    //sdk路径
    'sdkPath' => ROOT_DIR . '..' . DS . 'service' . DS . 'sdk' . DS . 'sdk.class.php',
    //参数过滤配置
    'requestParamsConfig'=>require(__DIR__.DS.'requestParamsConfig.php'),
    //接口限制配置
    'interfaceLimit'=>require(__DIR__.DS.'interfaceLimit.php'),
    //哪些接口不记录日志或记录日志
    'logExclude' => require('logExceptions.php'),
    //是否开启文件缓存（只针对正在上映影片列表、影片详情等接口），1开启，0不开启
    'cacheData' => 1,
    //NFS地址
    "nfs_host" => "https://wxadminpre.wepiao.com/",
    //其他API
    //当前app渠道
    "app_channel_ids" => $app_channel_ids,
    //格瓦拉AppId
    "gewara_channel_ids" => ['80', '84']
];

if (in_array(CHANNEL_ID, $app_channel_ids)) {
    $payTypeConfig = require(__DIR__ . DS . 'appPayConfig.php');
    //app 特有的过滤器
    array_push($global_config['filters'], 'RequestSignFilter');

    $global_config['requestSignConfig'] = require(__DIR__ . DS . 'requestSignConfig.php');
//    $global_config['requestTokenConfig'] = require(__DIR__ . DS . 'requestTokenConfig.php');
    //允许的支付方式
    $global_config['allow_pay_type'] = $payTypeConfig['allow_pay_type'];
    //格瓦拉支付方式 用于处理网页支付的URL
    $global_config['gawara_pay_type'] = $payTypeConfig['gawara_pay_type'];
    //App NFS地址
    $global_config["app_get_patch_filter_list"] = [3430000023,];
    $global_config['gewara_app_id'] = [
        80 => '50000070',
        84 => '50000020',
    ];
}
return $global_config;
