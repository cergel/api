<?php

/**
 * 常量的定义, 最好在定义之前, 先和组内成员沟通是否有定义规则
 */

//定义默认redis渠道
defined("SALE_PLATFORM_TYPE") or define("SALE_PLATFORM_TYPE", 2);//售卖平台

switch (intval(\wyCupboard::$channelId)) {
    case 3: //微信
        $WX_MOVIE_APP_ID = 1;
        $WX_SALE_APP_ID = 100;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    case 28: //手Q
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 8020000000;
        $WX_ID_TYPE_SERVICE = 12;
        break;
    case 63: //小程序1
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 63000001;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    case 66: //小程序2
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 66000001;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    case 67: //小程序3
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 67000001;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    case 68: //小程序4
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 68000001;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    case 86: //小程序 为变5准备
        $WX_MOVIE_APP_ID = 2;
        $WX_SALE_APP_ID = 86000001;
        $WX_ID_TYPE_SERVICE = 11;
        break;
    default: //默认微信
        $WX_MOVIE_APP_ID = 1;
        $WX_SALE_APP_ID = 100;
        $WX_ID_TYPE_SERVICE = 11;
}
defined("WX_MOVIE_APP_ID") or define("WX_MOVIE_APP_ID", $WX_MOVIE_APP_ID);//售卖平台
defined("WX_SALE_APP_ID") or define("WX_SALE_APP_ID", $WX_SALE_APP_ID);//售卖方类别（相当于售卖平台下的子渠道）
defined("WX_ID_TYPE_SERVICE") or define("WX_ID_TYPE_SERVICE", $WX_ID_TYPE_SERVICE);//该渠道对应在用户中心端的编号，微信11，手Q12。主要调service时使用


defined("CITY_MOVIE_LIST") or define("CITY_MOVIE_LIST", "city_movie_list");
defined('STATIC_MOVIE_DATA') or define('STATIC_MOVIE_DATA', 'static_movie_data');

defined('STAR_GREETING_INFO') or define('STAR_GREETING_INFO', 'greeting_info:{#greetingId}');//明星问候缓存
defined('STAR_GREETING_ONLINE_ID') or define('STAR_GREETING_ONLINE_ID', 'greeting_online_id:{#channelId}');//线上明星问候id

//组内共用redis，适合容量小但请求频繁的数据
defined('GROUP_SHARE_FREQUENT') or define('GROUP_SHARE_FREQUENT', 'group_share_frequent');

defined('COOKIE_OPENID_NAME') or define('COOKIE_OPENID_NAME', '_di_nepo_');

//资源数据
//defined('RESOURCE_STATIC') or define('RESOURCE_STATIC', 'resource_static');
//三端底部icon图标redis key
//defined('ICON_CONFIG') or define('ICON_CONFIG', 'icon_config_{#channelId}');




//定义默认渠道
defined("DEFAULT_CHANNEL_ID") or define("DEFAULT_CHANNEL_ID", 8);
//静态数据
defined('APP_CACHE_DATA') or define('APP_CACHE_DATA', 'app_cache_data');

//观影轨迹CACHE
defined("USER_TRACE_ORDER_BUY_MOVIES") or define("USER_TRACE_ORDER_BUY_MOVIES", "buyMovies"); //用户订单中已购票(观影)影片列表
defined("USER_TRACE_UPDATE") or define("USER_TRACE_UPDATE", "updated"); //用户观影轨迹更新时间

defined('USER_DB_APP') or define('USER_DB_APP', 'user_db_');


//请求JAVA接口的一些常量
defined('APP_ID') or define('APP_ID', 1);
defined('I_PLATFORM') or define('I_PLATFORM', 1);

//明星选坐redis
defined('APP_CUSTOMIZATION_SEAT') or define('APP_CUSTOMIZATION_SEAT', 'app_customization_seat');

//日签的KEY
defined('KEY_DAY_SIGN_PAGING_CALENDAR') or define('KEY_DAY_SIGN_PAGING_CALENDAR', 'day_sign_calendar');//日签的月历
defined('KEY_DAY_SIGN_PAGING_MONTH') or define('KEY_DAY_SIGN_PAGING_MONTH', 'day_sign_month_');//某个月的日签

defined('DAY_SIGN_PAGING') or define('DAY_SIGN_PAGING', 'day_sign_paging');
//首页自定义图标redis
//defined('APP_ICON_CONFIG') or define('APP_ICON_CONFIG', 'app_icon_config');
//影片商业化详情列表
defined('MOVIE_INFO_BIZ') or define('MOVIE_INFO_BIZ', 'movie_info_biz');
//影片商业化详情列表
defined('APP_MODULE_SWITCH') or define('APP_MODULE_SWITCH', 'app_module_switch');

//APP热修复补丁
defined("KEY_JSPATCH_ITEM") or define("KEY_JSPATCH_ITEM", 'jspatch_item');
defined('KEY_TINKER_ITEM') or define('KEY_TINKER_ITEM', 'tinker_item');
//第三方登录验证用户是否有效
defined('WEIXIN_TOKEN_INFO_URL') or define('WEIXIN_TOKEN_INFO_URL', 'https://api.weixin.qq.com/sns/userinfo');
defined('QQ_TOKEN_INFO_URL') or define('QQ_TOKEN_INFO_URL', 'https://graph.qq.com/user/get_user_info');
defined('SINA_TOKEN_INFO_URL') or define('SINA_TOKEN_INFO_URL', 'https://api.weibo.com/oauth2/get_token_info');
//订单类型定义
defined('ORDER_TYPE_TICKET') or define('ORDER_TYPE_TICKET', '2');
defined('ORDER_TYPE_TICKET_SNACK') or define('ORDER_TYPE_TICKET_SNACK', '24');
defined('ORDER_TYPE_SNACK') or define('ORDER_TYPE_SNACK', '4');
defined('ORDER_TYPE_VIP_CARD') or define('ORDER_TYPE_VIP_CARD', '6');
defined('COOKIE_WX_OPEN_ID') or define('COOKIE_WX_OPEN_ID', 'WxOpenId');
defined('COOKIE_MQQ_OPEN_ID') or define('COOKIE_MQQ_OPEN_ID', 'MqqOpenId');
defined('COOKIE_WX_UNION_ID') or define('COOKIE_WX_UNION_ID', 'WxUnionId');
