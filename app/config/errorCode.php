<?php
return [
    //01代表系统级别,每一个小key就是一个系统级别错误
    '01' => [
        '0000' => [
            '1001' => ['userMsg' => ERROR_MESSAGE_SYSBUSY, 'sysMsg' => 'mysql connection error'],//mysql链接错误
            '1002' => ['userMsg' => ERROR_MESSAGE_SYSBUSY, 'sysMsg' => 'redis connection error'],//redis连接错误
            '1003' => ['userMsg' => '路由不匹配', 'sysMsg' => 'bad route'],//路由不匹配
            '1004' => ['userMsg' => '路由分发失败', 'sysMsg' => 'dispatch error'],//路由分发失败
            '1005' => ['userMsg' => '请求方法不允许', 'sysMsg' => 'not allowed'],//请求方法不允许
            '1006' => ['userMsg' => '控制器方法不存在', 'sysMsg' => 'controller not exists'],//控制器方法不存在
            '1007' => ['userMsg' => '服务器繁忙，请稍后尝试', 'sysMsg' => 'server internal error'],//服务器繁忙，请稍后尝试
            '1008' => ['userMsg' => '未知错误', 'sysMsg' => 'error code not defined'],//错误码未定义
            '1009' => ['userMsg' => '系统繁忙请重试', 'sysMsg' => 'system busy'],//系统繁忙请重试
            '1010' => ['userMsg' => '参数不完整', 'sysMsg' => 'miss argument'],//参数不完整
        ],
    ],
    //02代表模块级别，一级小key代表模块，二级小key代表模块具体错误
    '02' => [
        //1101表示业务
        '1101' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
            '1001' => ['userMsg' => 'token验证失败', 'sysMsg' => 'error token'],//token验证失败
            '1002' => ['userMsg' => '授权失败，请重新登录', 'sysMsg' => 'authorization failed'],//授权失败
            '1003' => ['userMsg' => '获取用户信息失败', 'sysMsg' => 'get user info failed'],//获取用户信息失败
            '1004' => ['userMsg' => '登录异常，请返回重试', 'sysMsg' => 'please login again'],//登录异常，请返回重试
            '1005' => ['userMsg' => '注册用户失败', 'sysMsg' => 'register user failed'],//注册用户失败
            '1006' => ['userMsg' => '解密openid失败', 'sysMsg' => 'decrpy openid failed'],//解密openid失败
            '1007' => ['userMsg' => '订单号不能为空', 'sysMsg' => 'orderId can not be null'],//订单号不能为空
            '1008' => ['userMsg' => '手机号不能为空', 'sysMsg' => 'mobile can not be null'],//手机号不能为空
            '2001' => ['userMsg' => '你选的座位暂时无法购买，请选择其他座位', 'sysMsg' => 'error token'],//场次信息未获取到
            '2002' => ['userMsg' => '该场次暂时无法购票，请选择其他场次', 'sysMsg' => 'error token'],//当前场次已经过期
            '2003' => ['userMsg' => '你选的座位暂时无法购买，请选择其他座位', 'sysMsg' => 'error token'],//锁定的座位已过期
            '2004' => ['userMsg' => '单人购买数量到达上限', 'sysMsg' => 'error token'],//单人购买数量到达上限
            '2005' => ['userMsg' => '你选的座位暂时无法购买，请选择其他座位', 'sysMsg' => 'error token'],//请求参数为空
            '2006' => ['userMsg' => '支付姐姐有点忙，请重新支付', 'sysMsg' => 'error token'],//非法的支付
            '2007' => ['userMsg' => '该场次暂时无法购票，请选择其他场次', 'sysMsg' => 'error token'],//当前批价ID不存在
            '2008' => ['userMsg' => '该场次暂时无法购票，请稍后再试或选择其他场次', 'sysMsg' => 'error token'],//不存在的订单
            '2009' => ['userMsg' => '该影院暂时无法购票，请选择其他影院', 'sysMsg' => 'error token'],//开放平台对应影院不存在
            '2010' => ['userMsg' => '支付姐姐忙坏了，请重新选座再支付', 'sysMsg' => 'error token'],//批价发生变化,更新失败
            '2011' => ['userMsg' => '支付姐姐有点忙，请重新支付', 'sysMsg' => 'error token'],//支付相关参数不全
            '2012' => ['userMsg' => '当有新订单时，老订单默认取消', 'sysMsg' => 'error token'],//存在未支付订单
            '2013' => ['userMsg' => '咦，手滑了，请再次重新选座', 'sysMsg' => 'error token'],//座位信息不合法
            '2014' => ['userMsg' => '影院售票系统异常，请稍后再试', 'sysMsg' => 'error token'],//当前订座系统影院信息不存在
            '2015' => ['userMsg' => '你选的座位暂时无法购买，请选择其他座位', 'sysMsg' => 'error token'],//当前订单的支付下单失败
        ],
        //1102是用户中心的业务
        '1102' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
            '1001' => ['userMsg' => '用户uid错误', 'sysMsg' => 'error param uid'],//参数错误
            '1002' => ['userMsg' => '获取用户微信信息失败', 'sysMsg' => 'error wechat userinfo'],//从微信获取用户信息失败
            '1003' => ['userMsg' => '注册用户失败', 'sysMsg' => 'error wechat userinfo'],//注册用户失败
            '1004' => ['userMsg' => '红包资源不存在', 'sysMsg' => 'error bonus resource'],//红包资源不存在
            '1005' => ['userMsg' => '缺少openId参数', 'sysMsg' => 'miss openId pramas'],//缺少openId参数
            '1006' => ['userMsg' => 'unionId不能为空', 'sysMsg' => 'error wechat need unionId'],//unionId不能为空
            '1007' => ['userMsg' => 'accesstoken获取失败', 'sysMsg' => 'get accesstoken error'],//accesstoken获取失败
        ],
        //商品中心——卖品
        '1103' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
        ],
        //营销中心——兑换券
        '1104' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
        ],
        //订单中心
        '1105' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
        ],
        //短信中心
        '1106' => [
            '1000' => ['userMsg' => '无效的滑动认证信息', 'sysMsg' => 'error slide params'],
            '1001' => ['userMsg' => '无效的手机号信息', 'sysMsg' => 'error phone params'],
            '1002' => ['userMsg' => '短信发送失败', 'sysMsg' => 'send sms code error'],
            '1003' => ['userMsg' => '滑动认证信息无效', 'sysMsg' => 'check slide info error'],
            '1004' => ['userMsg' => '手机号或验证码参数不全', 'sysMsg' => 'check phone and code error'],
            '1005' => ['userMsg' => '短信验证失败', 'sysMsg' => 'invalid sms code'],
        ],
        //大数据
        '1107' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],
        ],
        //影院收藏相关
        '1108' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],
            '1001' => ['userMsg' => '收藏失败', 'sysMsg' => 'error params'],
            '1002' => ['userMsg' => '取消收藏失败', 'sysMsg' => 'error params'],
        ],
        //观影秘籍相关
        '1109' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],
        ],
        //观影轨迹相关
        '1110' => [
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],
            '1001' => ['userMsg' => '观影轨迹删除失败', 'sysMsg' => 'error params'],
        ],
        //明星问候
        '1018'=>[
            '1000' => ['userMsg' => '参数错误', 'sysMsg' => 'error params'],//参数错误
            '1001' => ['userMsg' => '问候不存在', 'sysMsg' => 'greeting not exists'],//问候不存在
        ],

        //手Q观影社区
        '1019' => [
            '1000' => ['userMsg' => '缺少参数', 'sysMsg' => 'miss argument'],
            '1001' => ['userMsg' => 'AccessToken获取失败', 'sysMsg' => 'fail to get accesstoken'],
        ],
    ],
];