<?php
// 根据系统是否装了yac扩展判定加载哪个路由
if (class_exists('Yac')) {
    $route_version = '2017073106';
    //定义路由
    function yacCachedDispatcher(callable $routeDefinitionCallback, array $options = [], $route_version)
    {
        $options += [
            'routeParser'    => 'FastRoute\\RouteParser\\Std',
            'dataGenerator'  => 'FastRoute\\DataGenerator\\GroupCountBased',
            'dispatcher'     => 'FastRoute\\Dispatcher\\GroupCountBased',
            'routeCollector' => 'FastRoute\\RouteCollector',
            'cacheDisabled'  => false,
        ];
        $yac = new Yac();
        $routeKey = 'fast_routes_config_api';
        $keyRouteVersion = 'fast_routes_version_api';
        $dispatchData = $yac->get($routeKey);
        $cacheRouteVersion = $yac->get($keyRouteVersion);
        if (empty( $dispatchData ) || $route_version != $cacheRouteVersion) {
            $routeCollector = new $options['routeCollector'](new $options['routeParser'], new $options['dataGenerator']);
            $routeDefinitionCallback($routeCollector);
            $dispatchData = $routeCollector->getData();
            $yac->set($routeKey, $dispatchData);
            $yac->set($keyRouteVersion, $route_version);

            return new $options['dispatcher']($dispatchData);
        }
        else {
            return new $options['dispatcher']($dispatchData);
        }
    }

    $dispatcher = yacCachedDispatcher("addRouteRuless", [], $route_version);
}
else {
    $dispatcher = FastRoute\simpleDispatcher("addRouteRuless");
}

/**
 * 添加路由规则
 *
 * @param \FastRoute\RouteCollector $r
 */
function addRouteRuless(FastRoute\RouteCollector $r)
{
    //【城市相关】
    //获取城市列表
    $r->addRoute('GET', '/v1/cities', '\app\controllers\CityController@getCitiesV2');
    //格瓦拉获取城市列表
    $r->addRoute('GET', '/v1/cities/gewara', '\app\controllers\CityController@getWaraCity');
    //【影片相关】
    //城市下影片列表（热映）
    $r->addRoute('GET', '/v1/movies/show/cities/{cityId:\d+}', '\app\controllers\MovieController@getList');
    $r->addRoute('GET', '/v2/movies/show/cities/{cityId:\d+}', '\app\controllers\MovieController@getListV2');
    //即将上映影片列表
    $r->addRoute('GET', '/v1/movies/will/dategroup/cities/{cityId:\d+}', '\app\controllers\MovieController@getWillWithDate');
    //获取影片详情
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}', '\app\controllers\MovieController@getInfo');
    //电影主创
    $r->addRoute('GET', '/v1/actors/movies/{movieId:\d+}', '\app\controllers\MovieController@getActor');

    //喜欢、取消喜欢影人
    $r->addRoute(['GET', 'POST'], '/v1/actors/{actorId:\d+}/like', '\app\controllers\MovieController@likeActor');
    $r->addRoute('GET', '/v1/actors/{actorId:\d+}/news', '\app\controllers\ActorController@getActorNews');
    //【影院相关】
    //获取影院详情
    $r->addRoute('GET', '/v1/cinemas/{cinemaId:\d+}', '\app\controllers\CinemaController@getInfo');
    //获取支持某个会员卡的影院列表
    $r->addRoute('GET', '/v1/cinemas/vipcards/{typeId:\d+}', '\app\controllers\CinemaController@getCardCinemaList');
    //【排期】获取某影院下的所有影片排期
    $r->addRoute('GET', '/v1/sches/cinemas/{cinemaId:\d+}', '\app\controllers\ScheController@getInfo');
    $r->addRoute('GET', '/v2/sches/cinemas/{cinemaId:\d+}', '\app\controllers\ScheController@getInfoV2');
    $r->addRoute('GET', '/v1/sches/ext/{scheduleId:\d+}', '\app\controllers\ScheController@getScheduleExt');//获取某排期扩展属性（3D眼镜）

    //【座位相关】
    //获取影厅座位图
    $r->addRoute('GET', '/v1/seats/cinemas/{cinemaId:\d+}/rooms/{roomId:\w+}', '\app\controllers\SeatController@getSeats');

    //【定位相关】
    //IP定位
    $r->addRoute('GET', '/v1/location/ip', '\app\controllers\LocationController@ip');
    //经纬度定位
    $r->addRoute('GET', '/v1/location/coordinate', '\app\controllers\LocationController@coordinate');

    //根据城市名称获取Id
    $r->addRoute('GET', '/v1/location/getcityid', '\app\controllers\LocationController@getWyCityId');

    //【公告相关】
    //公告接口
    $r->addRoute('GET', '/v1/announce', '\app\controllers\AnnounceController@getInfo');
    //通知简讯(包含: 可用红包数量、最近观影影院等)
    $r->addRoute('GET', '/v1/announce/simpleinfo', '\app\controllers\AnnounceController@getSimpleInfo');
    //批量接口-影片详情页
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/batch', '\app\controllers\MovieController@batchMovieInfo');


    //锁座接口
    $r->addRoute('POST', '/v1/seats/lock', '\app\controllers\SeatController@lockSeat');
    $r->addRoute('POST', '/v2/seats/lock', '\app\controllers\SeatController@lockSeatV2');

    //新版全支付接口
    $r->addRoute('POST', '/v1/orders/{orderId:\d+}/pay[/{platforms:\w+}]', '\app\controllers\OrderController@pay');
    //全支付V2版本(暂时GET、POST都支持,后续应该只支持GET)
    $r->addRoute(['GET', 'POST'], '/v2/orders/{orderId:\d+}/pay[/{platforms:\w+}]', '\app\controllers\OrderController@payV2');
    //H5便捷支付网页
    $r->addRoute('GET', '/wap-payment/jd/{token:\w+}', '\app\controllers\PaymentController@easyPaymentJd');
    $r->addRoute('GET', '/wap-payment/gewara/{token:\w+}', '\app\controllers\PaymentController@easyPaymentGewara');
    $r->addRoute('GET', '/wap-payment/gewara-cardbin/{token:\w+}', '\app\controllers\PaymentController@GewaraCardNo');
    $r->addRoute(['GET', 'POST'], '/wap-payment/gewara-success/{token:\w+}', '\app\controllers\PaymentController@GewaraSuccess');
    $r->addRoute('GET', '/wap-payment-vipcard/gewara/{token:\w+}', '\app\controllers\PaymentController@easyPaymentVipGewara');
    $r->addRoute('GET', '/wap-payment-vipcard/jd/{token:\w+}', '\app\controllers\PaymentController@easyPaymentVipJd');
    $r->addRoute(['GET', 'POST'], '/wap-payment/gewara-vipcard-success/{info:\w+}', '\app\controllers\PaymentController@GewaraVipSuccess');

    //小吃支付接口
    $r->addRoute('POST', '/v1/cinemas/{cinemaId:\d+}/snack-payment', '\app\controllers\CinemasController@cinemaSnackPayment');
    //获取格瓦拉可用支付列表
    $r->addRoute('GET', '/v1/payments/gewara-list', '\app\controllers\PaymentController@gewaraList');
    //卡bin支付接口
    $r->addRoute('POST', '/v1/payments/bin/{token:\w+}', '\app\controllers\PaymentController@payBin');
    //获取用户红包信息
    $r->addRoute('GET', '/v1/discounts', '\app\controllers\DiscountController@getDiscounts');

    //收藏/取消收藏影院
    $r->addRoute('POST', '/v1/cinemas/{cinemaId:\d+}/favorite', '\app\controllers\FavoriteController@cinema');
    //获取用户的影院收藏列表
    $r->addRoute('GET', '/v1/cinemas/favorite', '\app\controllers\FavoriteController@cinemaList');

    //V1微信支付
    //查询可用优惠信息
    $r->addRoute(['GET', 'POST'], '/v1/bonus', '\app\controllers\BonusController@getBonus');
    //查询可用优惠信息V2版，融合礼品卡
    $r->addRoute(['GET', 'POST'], '/v2/bonus', '\app\controllers\BonusController@getBonusV2');
    //查询所有优惠
    $r->addRoute('GET', '/v1/allbonus', '\app\controllers\BonusController@getAllBonus');
    //退票
    $r->addRoute('POST', '/v1/orders/{orderId:\d+}/refund', '\app\controllers\OrderController@refundOrder');
    //查询订单列表
    $r->addRoute('GET', '/v1/orders', '\app\controllers\OrderController@getOrderList');
    //查询订单列表【V2】
    $r->addRoute('GET', '/v2/orders', '\app\controllers\OrderController@getOrderListV2');
    //查询订单详情
    $r->addRoute('GET', '/v1/orders/{orderId:\d+}', '\app\controllers\OrderController@getOrderDetail');
    //查询订单详情【V2】
    $r->addRoute('GET', '/v2/orders/{orderId:\d+}', '\app\controllers\OrderController@getOrderDetailV2');
    //删除订单
    $r->addRoute('DELETE', '/v1/orders/{orderId:\d+}', '\app\controllers\OrderController@deleteOrder');
    //获取未支付订单【V2】
    $r->addRoute('GET', '/v2/orders/unpaid', '\app\controllers\OrderController@unpaidOrderV2');
    //获取影院公告
    $r->addRoute('GET', '/v1/announcement', '\app\controllers\ResourceController@getAnnounce');

    //【搜索&大数据相关】
    //影院关键字搜索
    $r->addRoute('GET', '/v1/cinemas/search', '\app\controllers\SearchController@keywords');
    //影院过滤条件
    $r->addRoute('GET', '/v1/cinemas/filter', '\app\controllers\CinemaController@searchFilter');
    $r->addRoute('GET', '/v2/cinemas/filter', '\app\controllers\CinemaController@searchFilterV2');
    $r->addRoute('GET', '/v1/cinemas/cities/{cityId:\d+}', '\app\controllers\CinemaController@getListFromEs');//获取影院列表
    $r->addRoute('GET', '/v1/cinemas/cities/{cityId:\d+}/movies/{movieId:\d+}', '\app\controllers\CinemaController@getListByMovieFromEs');//正在上映某个片子的影院列表（相当于以前的影片排期接口）
    $r->addRoute('GET', '/v2/cinemas/cities/{cityId:\d+}', '\app\controllers\CinemaController@getListFromEsV2');//获取影院列表
    $r->addRoute('GET', '/v2/cinemas/cities/{cityId:\d+}/movies/{movieId:\d+}', '\app\controllers\CinemaController@getListByMovieFromEsV2');//正在上映某个片子的影院列表（相当于以前的影片排期接口）
    //综合搜索
    $r->addRoute('GET', '/v1/search/more', '\app\controllers\SearchController@more');
    //搜索推荐
    $r->addRoute('GET', '/v1/search/recommend', '\app\controllers\SearchController@recommend');

    //阵营相关
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/camp', '\app\controllers\MovieController@getMovieCamp');
    $r->addRoute('POST', '/v1/movies/{movieId:\d+}/camp', '\app\controllers\MovieController@addMovieCamp');

    //登录相关
    $r->addRoute('GET', '/v1/login', '\app\controllers\LoginController@login');

    //短信相关
    $r->addRoute('POST', '/v1/sms', '\app\controllers\SmsController@sendSmsCode');
    //需要登陆验证
    $r->addRoute('GET', '/v1/sms', '\app\controllers\SmsController@verifySmsCode');
    //不需要登陆验证
    $r->addRoute('PUT', '/v1/sms', '\app\controllers\SmsController@verifyCode');
    //【用户中心相关】
    $r->addRoute('GET', '/v1/users/mobile', '\app\controllers\UserController@getUserMobile'); //获取用户的用户中心的手机号
    $r->addRoute('GET', '/v1/users/info', '\app\controllers\UserController@getUserInfo');//获取用户的最新昵称，头像并同步到用户中心
    $r->addRoute('GET', '/v1/users/info/byMobile', '\app\controllers\UserController@getUserinfoByMobile'); //获取用户信息，通过手机号
    $r->addRoute('GET', '/v1/users/info/byUid', '\app\controllers\UserController@getUserinfoByUid'); //获取用户信息，通过uid
    $r->addRoute('GET', '/v1/users/info/byOpenid', '\app\controllers\UserController@getUserinfoByOpenid'); //获取用户信息，通过openid
    $r->addRoute('GET', '/v1/users/status/mobile', '\app\controllers\UserController@checkMobileStatus'); //检查手机号状态
    $r->addRoute('GET', '/v1/users/status/openid', '\app\controllers\UserController@checkOpenidStatus'); //检查openid状态
    $r->addRoute('GET', '/v1/users/openidList/byMobile', '\app\controllers\UserController@getOpenidListByMobile'); //手机号获取用户openid集合
    $r->addRoute('GET', '/v1/users/profile', '\app\controllers\UserController@getUserProfile'); //获取用户个人资料
    $r->addRoute('GET', '/v1/users/login', '\app\controllers\UserController@LoginAndBind'); //用户中心登录
    //APP用户手机号密码登陆接口
    $r->addRoute('POST', '/v1/users/app/login', '\app\controllers\UserController@mobileLogin');
    $r->addRoute(['POST', 'PUT'], '/v1/users/register', '\app\controllers\UserController@MobileRegister'); //手机号注册
    $r->addRoute(['POST', 'PUT'], '/v1/users/bind', '\app\controllers\UserController@Bind');  //手机号验证码绑定到用户中心
    $r->addRoute('GET', '/v1/users/bind/bonus-info', '\app\controllers\BonusController@getBindBonusInfo'); //绑定手机得红包红包信息查询
    $r->addRoute('GET', '/v1/users/bind/get-bonus', '\app\controllers\BonusController@getBindBonus'); //绑定手机领取红包
    $r->addRoute(['POST', 'PUT'], '/v1/users/profile', '\app\controllers\UserController@UserEdit'); //修改用户信息
    $r->addRoute(['POST', 'PUT'], '/v1/users/mobile', '\app\controllers\UserController@EditMobile'); //修改手机号
    $r->addRoute(['POST', 'PUT'], '/v1/users/password', '\app\controllers\UserController@EditPassword'); //修改密码
    $r->addRoute(['POST', 'PUT'], '/v1/users/password/reset', '\app\controllers\UserController@EditReset'); //重置密码
    $r->addRoute(['POST', 'PUT'], '/v1/users/password/newset', '\app\controllers\UserController@EditSetPassword'); //给无密码用户设置密码
    $r->addRoute('GET', '/v1/users/blackcheck', '\app\controllers\UserController@blackCheck'); //用户中心登录
    $r->addRoute('GET', '/v1/users/trace', '\app\controllers\UserController@getUserTrace'); //观影轨迹
    $r->addRoute('DELETE', '/v1/users/trace', '\app\controllers\UserController@deleteUserTrace'); //删除观影轨迹
    $r->addRoute('GET', '/v1/users/wants', '\app\controllers\UserController@getUserWants'); //想看清单
    $r->addRoute(['POST', 'PUT'], '/v1/users/star-pair', '\app\controllers\UserController@setStarPair'); //通过明星配对更新用户基础信息（生日、性别）字段
    $r->addRoute('GET', '/v1/users/payvipcard', '\app\controllers\UserController@payVipCard'); //微信电影票支付页面调用，当支付查询朝伟可用优惠中没有返回V卡  获取推荐的会员卡
    $r->addRoute('GET', '/v1/users/home-background', '\app\controllers\UserController@getUserHomeBackground'); //获取配置的背景图
    $r->addRoute('GET', '/v1/users/counts', '\app\controllers\UserController@getCounts'); //获取想看电影总数，喜欢影人总数，观影秘籍总数

    //查看其他用户的个人中心
    $r->addRoute('GET', '/v1/space/wants', '\app\controllers\SpaceController@getUserWants'); //用户想看的电影
    $r->addRoute('GET', '/v1/space/watch-same-movies', '\app\controllers\SpaceController@watchSameMovies'); //共同看过的电影
    $r->addRoute('GET', '/v1/space/home-background', '\app\controllers\SpaceController@backGround'); //用户查看其他用户的时候的背景图
    $r->addRoute('GET', '/v1/space/trace', '\app\controllers\SpaceController@getUserTrace'); //用户的观影轨迹
    $r->addRoute('GET', '/v1/space/like-actors', '\app\controllers\SpaceController@likeActor'); //用户喜欢的影人
    $r->addRoute('GET', '/v1/space/movieguide', '\app\controllers\SpaceController@getUserMovieGuideList'); //用户领取的观影秘笈
    $r->addRoute('GET', '/v1/space/profile', '\app\controllers\SpaceController@getUserProfile'); //查看者的个人资料
    $r->addRoute('GET', '/v1/space/counts', '\app\controllers\SpaceController@getCounts'); //获取想看电影总数，喜欢影人总数，观影秘籍总数

    //明星问候相关
    $r->addRoute('GET', '/v1/greeting', '\app\controllers\GreetingsController@getGreet');//获取问候

    //【影院会员卡相关】
    $r->addRoute('GET', '/v1/vipcards', '\app\controllers\CinemaVipController@getCardList');//获取某城市/某影院的卡列表
    $r->addRoute('GET', '/v1/vipcards/users', '\app\controllers\CinemaVipController@getUserCardList');//获取用户已经有的会员卡
    $r->addRoute('GET', '/v1/vipcards/info/{typeId:\w+}', '\app\controllers\CinemaVipController@getCardInfo');//会员卡详情
    $r->addRoute('GET', '/v1/vipcards/pay', '\app\controllers\CinemaVipController@payment');//会员卡支付
    $r->addRoute('GET', '/v1/vipcards/city/{cityId:\w+}', '\app\controllers\CinemaVipController@getCityCardList');//获取用户的会员卡列表(手Q)
    $r->addRoute('GET', '/v1/vipcards/usercards', '\app\controllers\CinemaVipController@getUserCardListByPage');//获取会员卡列表(和用户无关)(手Q)
    $r->addRoute('GET', '/v1/cinemas/{cinemaId:\d+}/vipcards', '\app\controllers\CinemaController@CinemaVipCard');//影院详情页融合版
    //APP内影院折扣卡展示页面WAP页面
    $r->addRoute('GET', '/wap/vip_my.html', '\app\controllers\CinemaVipController@showMyCard');
    $r->addRoute('GET', '/v1/vipcards/checkbuy/{typeId:\w+}', '\app\controllers\CinemaVipController@checkUserBuy');
    $r->addRoute('GET', '/v1/vipcards/simpleinfo/cinemas/{cinemaId:\w+}', '\app\controllers\CinemaVipController@getMergeSimpleInfo');

    //后续订单中心相关的都放到这里
    $r->addRoute('GET', '/v1/orders/mobile', '\app\controllers\OrderController@getOrderMobile');

    //cms、资讯等相关
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/news', '\app\controllers\CmsController@getMovieNews');//获取某部电影资讯
    //【尿点相关】
    //获取影片尿点
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/peeInfo', '\app\controllers\PeeController@getMoviePee');
    //点击尿点或取消尿点
    $r->addRoute('POST', '/v1/movies/{movieId:\d+}/peeLike', '\app\controllers\PeeController@clikePee');

    //获取发现头部信息
    $r->addRoute('GET', '/v1/find/info', '\app\controllers\FindController@getFindInfo');
    //获取发现列表
    $r->addRoute('GET', '/v1/find/{typeId:\d+}/list', '\app\controllers\FindController@getFindList');
    //发现导流
    $r->addRoute('GET', '/v1/find-guide/info', '\app\controllers\FindController@getFindGuide');

    //【媒资库相关】
    //获取影人详情
    $r->addRoute('GET', '/v1/actor/{actorId:\d+}/info', '\app\controllers\MsdbController@getActorInfo');
    //喜欢、取消喜欢影人
    $r->addRoute('POST', '/v1/actor/{actorId:\d+}/like', '\app\controllers\MsdbController@likeActor');//喜欢影人
    $r->addRoute('GET', '/v1/actor/my-actor', '\app\controllers\MsdbController@myActor'); //获取用户--我的影人
    //获取影片的影人列表--带评价信息
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/actor-list', '\app\controllers\MsdbController@getMovieActorListAndAppraise');
    //修改、新增、删除 用户对指定影片的指定影人的评价
    $r->addRoute('POST', '/v1/movies/{movieId:\d+}/appraise', '\app\controllers\MsdbController@saveActorAppraise');
    //获取影片图片--分类图片
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/poster', '\app\controllers\MsdbController@getMoviePoster');
    $r->addRoute('GET', '/v1/actors/{actorId:\d+}/poster', '\app\controllers\MsdbController@getActorPoster');

    //【商品中心相关】
    //获取卖品列表
    $r->addRoute('GET', '/v1/snacks/{cinemaId:\d+}', '\app\controllers\SnackController@snackList');
    $r->addRoute('GET', '/v2/snacks/{cinemaId:\d+}', '\app\controllers\SnackController@snackListV2');
    //获取可售座位
    $r->addRoute('GET', '/v1/seats', '\app\controllers\TicketController@getAvailableSeat');
    $r->addRoute('GET', '/v2/seats', '\app\controllers\TicketController@getMergedSeat');
    //卖品支付
    $r->addRoute('GET', '/v1/snacks/pay', '\app\controllers\SnackController@snackPayment');

    //【营销中心】
    //获取兑换券详情
    $r->addRoute('GET', '/v1/exchanges/info/{exchangeId:\w+}', '\app\controllers\ExchangeController@info');
    //兑换券
    $r->addRoute('GET', '/v1/exchanges/pay', '\app\controllers\ExchangeController@exchangePayment');
    $r->addRoute('GET', '/v1/exchanges/orders', '\app\controllers\ExchangeController@orderList');
    $r->addRoute('GET', '/v1/exchanges/orders/{orderId:\d+}', '\app\controllers\ExchangeController@orderInfo');
    //优惠到人接口
    $r->addRoute('GET', '/v1/discount', '\app\controllers\BonusController@discount');
    //查询点卡详情
    $r->addRoute('GET', '/v1/pointcards/info/{exchangeId:\w+}', '\app\controllers\PointCardController@getPointCardInfo');

    //发现文章
    $r->addRoute('GET', '/v1/discovery/recommend', '\app\controllers\DiscoveryController@recommend');
    //个人资料页推荐位
    $r->addRoute('GET', '/v1/discovery/will', '\app\controllers\DiscoveryController@movieWill');

    //学生认证
    $r->addRoute('GET', '/v1/school/oauth', '\app\controllers\SchoolController@oauth');

    //【公众号相关】
    $r->addRoute('GET', '/v1/wechat/follow', '\app\controllers\WechatController@getFollowStatus'); //判断用户是否关注了公众号

    //【观影秘籍】
    $r->addRoute('GET', '/v1/movieguide/{movieId:\d+}', '\app\controllers\MovieGuideController@getMovieGuide'); //查看影片的观影秘籍信息
    $r->addRoute('POST', '/v1/movieguide/{movieId:\d+}', '\app\controllers\MovieGuideController@takeMovieGuide'); //领取观影秘籍
    $r->addRoute('DELETE', '/v1/movieguide/{movieId:\d+}', '\app\controllers\MovieGuideController@removeMovieGuide'); //删除影片的观影秘籍信息
    $r->addRoute('GET', '/v1/movieguide/list', '\app\controllers\MovieGuideController@getUserMovieGuideList'); //获取用户已领取的观影秘籍
    $r->addRoute('GET', '/v1/movieguide/list/{movieId:\d+}', '\app\controllers\MovieGuideController@getUserMovieGuideInfo'); //获取用户已领取的某个片子的观影秘籍

    $r->addRoute('GET', '/v1/movies/guide/status/{movieId:\d+}', '\app\controllers\MovieGuideController@getMovieGuide');//获取观影秘籍状态（手Q）
    $r->addRoute('GET', '/v1/movies/guide/take/{movieId:\d+}', '\app\controllers\MovieGuideController@takeMovieGuide');//帮助用户领取观影秘籍（手Q）
    $r->addRoute('GET', '/v1/movies/guide/remove/{movieId:\d+}', '\app\controllers\MovieGuideController@removeMovieGuide');//删除用户领取观影秘籍（手Q）
    $r->addRoute('GET', '/v1/movies/guide/list', '\app\controllers\MovieGuideController@getUserMovieGuideList');//获取用户的领取列表（手Q）
    $r->addRoute('GET', '/v1/movies/guide/view/{movieId:\d+}', '\app\controllers\MovieGuideController@getUserMovieGuideInfo');//查看观影秘籍详情（手Q）

    //红点相关
    $r->addRoute('GET', '/v1/redspot/info', '\app\controllers\RedSpotController@getInfo'); //获取红点信息

    //【消息中心相关】
    $r->addRoute('GET', '/v1/message/point', '\app\controllers\MessageController@getRedPoint');//红点提醒
    $r->addRoute('GET', '/v1/message/clear', '\app\controllers\MessageController@clearRedPoint');//清除红点提醒
    $r->addRoute('GET', '/v1/message/list', '\app\controllers\MessageController@messageList');//消息盒子首页列表
    $r->addRoute('GET', '/v1/message/{type:\d+}/list', '\app\controllers\MessageController@messageType');//消息盒子 某类型消息列表
    $r->addRoute('GET', '/v1/message/{msgId:\d+}', '\app\controllers\MessageController@messageView');//消息详情

    //消息中心折扣卡促销
    $r->addRoute('GET', '/v1/message/promotion', '\app\controllers\MessageController@getToast');

    //bonus_proxy(旧版模式和新版接口模式)
    $r->addRoute('GET', '/cgi/bonus_proxy.php', '\app\controllers\ProxyController@bonusProxy');
    $r->addRoute('GET', '/v1/proxy', '\app\controllers\ProxyController@bonusProxy');

    //首页获取红包状态 微信小程序独有接口
    $r->addRoute('GET', '/v1/bonus/status', '\app\controllers\BonusController@getStatus');
    //小程序根据encryptedData获取unionId
    $r->addRoute('GET', '/v1/login/unionid', '\app\controllers\LoginController@getUnionId');

    //手Q公众号会员中心相关
    $r->addRoute('GET', '/v1/mp/switch', '\app\controllers\UserController@getMqqPushSwitch'); //获取用户手Q公众号推送开关
    $r->addRoute(['POST', 'PUT'], '/v1/mp/switch', '\app\controllers\UserController@setMqqPushSwitch'); //设置用户手Q公众号推送开关

    //获取首页自定义图标
    $r->addRoute('GET', '/v1/icons', '\app\controllers\ResourceController@getIcon');
    $r->addRoute('GET', '/v2/icons', '\app\controllers\ResourceController@getIconV2');

    //银行卡优惠
    $r->addRoute('GET', '/v1/bankprivilege', '\app\controllers\ActiveController@BankPrivilege');

    //app模块接口
    $r->addRoute('GET', '/v1/modules', '\app\controllers\AppResourceController@getModuleSwitch');
    $r->addRoute('GET', '/v1/patch', '\app\controllers\AppResourceController@getPatch');
    $r->addRoute('GET', '/v1/version/release', '\app\controllers\AppResourceController@getVersion');


    //用户第三方注册登陆（APP专有）
    $r->addRoute('POST', '/v1/register/third-party', '\app\controllers\LoginController@thirdParty');

    //用户绑定手机号(已登录)(app专用 )
    $r->addRoute('POST', '/v1/bind/third-party', '\app\controllers\UserController@bindThirdParty');

    //原声音乐
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/music', '\app\controllers\MovieController@music');

    //获取日签
    $r->addRoute('GET', '/v1/calendar', '\app\controllers\AppResourceController@getDaysign');
    //获取日签
    $r->addRoute('GET', '/v1/calendar/{month:\w+}', '\app\controllers\AppResourceController@getDaySignMonth');
    //获取去年今日日签（历史日签）
    $r->addRoute('GET', '/v1/calendar-lastyear', '\app\controllers\AppResourceController@getDaySignLastYear');

    //jspatch
    $r->addRoute('GET', '/v1/jspatch', '\app\controllers\AppResourceController@jspatch');
    //获取片单列表
    $r->addRoute('GET', '/v1/film-list/{listId:\d+}', '\app\controllers\MovieController@getFilmList');
    //获取某个影片下的片单列表
    $r->addRoute('GET', '/v1/film-list/movie/{movieId:\d+}', '\app\controllers\MovieController@getFilmListByMovie');

    //收藏某个片单
    $r->addRoute('POST', '/v1/film-list/favourite/{listId:\d+}', '\app\controllers\MovieController@favouriteFilmList');
    $r->addRoute('DELETE', '/v1/film-list/favourite/{listId:\d+}', '\app\controllers\MovieController@delFavouriteFilmList');
    //我的片单
    $r->addRoute('GET', '/v1/film-list/favourite', '\app\controllers\MovieController@getFavouriteList');

    //好友个人主页
    $r->addRoute('GET', '/v1/user/friend', '\app\controllers\AppUserController@friend');

    //滑动验证地址获取
    $r->addRoute('GET', '/v1/slider', '\app\controllers\UserController@slider');
    //意见反馈
    $r->addRoute('POST', '/v1/feedback', '\app\controllers\AppUserController@feedback');
    //获取影片商业化列表
    $r->addRoute('GET', '/v1/movies/biz', '\app\controllers\AppResourceController@getBiz');
    //【观影秘籍】
    //观影秘笈-所有观影秘笈列表（16年12月新增）
    $r->addRoute('GET', '/v1/movies/guides', '\app\controllers\MoviesController@movieGuideList');
    //首页拉新促销活动
    $r->addRoute('GET', '/v1/active/newcomer', '\app\controllers\ActiveController@NewcomerBonus');
    //有偿退改签
    $r->addRoute('GET', '/v1/changesfee/{cinemaId:\d+}', '\app\controllers\ChangesFeeController@getFeeInfo');
    //有偿退改签
    $r->addRoute('GET', '/v1/changesfee/fee', '\app\controllers\ChangesFeeController@getCurrentFee');

    //【格瓦拉】
    //格瓦拉影片id获取娱票儿影片id
    $r->addRoute('GET', '/v1/movie-id/yp/{gewaraMovieId:\d+}', '\app\controllers\MovieController@getWxMovieId');
    //格瓦拉影人id获取娱票儿影人id
    $r->addRoute('GET', '/v1/actor-id/yp/{gewaraActorId:\d+}', '\app\controllers\MovieController@getWxActorId');
    //格瓦拉获取全量
    $r->addRoute('GET', '/v1/gewara-yp/static/all', '\app\controllers\CinemaController@GewaraStaticAll');
    //获取格瓦拉可用支付列表
    $r->addRoute('GET', '/v1/gwlpay/pay-methods', '\app\controllers\PaymentController@gwlPayMethods');
    //格瓦拉专属支付
    $r->addRoute(['GET', 'POST'], '/v1/gwlpay/{platforms:\w+}', '\app\controllers\OrderController@gwlpay');
    //iOS Wallet
    $r->addRoute('GET', '/v1/orders/passbook/{orderId:\d+}', '\app\controllers\OrderController@passbook');
    //格瓦拉个人中心查询可用优惠
    $r->addRoute('GET', '/v1/bonus/gewara-list', '\app\controllers\BonusController@getUserBonusList');
    //格瓦拉支付页查询可用优惠和银行特价活动
    $r->addRoute('GET', '/v1/bonus/gewara-pay-list', '\app\controllers\BonusController@getPayBonusList');
    //格瓦拉通兑码兑换
    $r->addRoute('POST', '/v1/bonus/gewara-code-exchange', '\app\controllers\BonusController@exchangeCode');
    //格瓦拉v卡列表
    $r->addRoute('GET', '/v1/bonus/gewara-vcard-list', '\app\controllers\BonusController@vcardList');
    //格瓦拉V卡激活
    $r->addRoute('POST', '/v1/bonus/gewara-vcard-active', '\app\controllers\BonusController@vcardActive');
    //格瓦拉v卡信息
    $r->addRoute('GET', '/v1/bonus/gewara-vcard-info', '\app\controllers\BonusController@vcardInfo');
    //获取图片验证码
    $r->addRoute('GET', '/v1/verify/picture-code', '\app\controllers\SecurityController@getPicCode');
    //格瓦拉支付新放映接口
    $r->addRoute('GET', '/v1/gewara/cinemas/new-screening', '\app\controllers\CinemaController@GewaraCinemasSort');
    //判断某影院是否被用户收藏过
    $r->addRoute('GET', '/v1/cinemas/is-favorite', '\app\controllers\FavoriteController@isFavorite');
    $r->addRoute('GET', '/v1/cinemas/favorite-info', '\app\controllers\FavoriteController@favoriteInfoList');
    //卡bin页面
    $r->addRoute('GET', '/wap/jump.html', '\app\controllers\CinemaVipController@jump');
    $r->addRoute('GET', '/wap/bankcard.html', '\app\controllers\PaymentController@bankcard');
    $r->addRoute('GET', '/wap-payment/vipcard-error', '\app\controllers\PaymentController@GewaraVipError');
    //格瓦拉添加影片id通知
    $r->addRoute('GET', '/v1/gewara/movie-id/push', '\app\controllers\MovieController@GewaraMovieIdPush');

    //媒资库预告片列表
    $r->addRoute('GET', '/v1/videos/movies/{movieId:\d+}', '\app\controllers\MovieController@getMsdbVideos');
    
    //是否已购票
    $r->addRoute('GET', '/v1/movies/{movieId:\d+}/isbuy', '\app\controllers\TicketController@isBuy');

    //获取App发放红包数量
    $r->addRoute('GET', '/v1/app/bonus/number', '\app\controllers\AppResourceController@getRedPacketNum');

    //即将上映数据预览（明星见面会、最热、新片推荐）
    $r->addRoute('GET', '/v1/movies/will/preview/{cityId:\d+}', '\app\controllers\MovieController@getMovieWillPreview');

    //手Q观影社区
    $r->addRoute('GET', '/v1/qqwallet/hot-movies', '\app\controllers\FriendsWatchController@getHotMovieList');
    $r->addRoute('GET', '/v1/qqwallet/movieinfo', '\app\controllers\FriendsWatchController@getMovieInfo');
    $r->addRoute('GET', '/v1/qqwallet/friends', '\app\controllers\FriendsWatchController@getWatchFriends');
    //娱票儿首页正在上映即将上映常去影院融合版接口
    $r->addRoute('GET', '/v1/app/movies/show/cities/{cityId:\d+}', '\app\controllers\AppMovieController@getList');
    //娱票儿首页今日推荐
    $r->addRoute('GET', '/v1/app/recommend', '\app\controllers\AppMovieController@recommend');

    //小程序emoji——获取题目信息
    $r->addRoute('GET', '/v1/emojis/questions/{problemId:\d+}/repos/{repoId:\d+}', '\app\controllers\SmallRoutineController@getAProblemItem');
    //小程序emoji——获取随机题目信息
    $r->addRoute('GET', '/v1/emojis/questions/random/repos/{repoId:\d+}', '\app\controllers\SmallRoutineController@getRandomProblemItem');
    //小程序emoji——获取题目名数据
    $r->addRoute('GET', '/v1/emojis/repos', '\app\controllers\SmallRoutineController@getProblemRepos');
    //小程序emoji——获取指定的题库数据
    $r->addRoute('GET', '/v1/emojis/repos/{repoId:\d+}', '\app\controllers\SmallRoutineController@getAProblemRepo');
    //小程序emoji——提交答案
    $r->addRoute('POST', '/v1/emojis/answers/questions/{problemId:\d+}/repos/{repoId:\d+}', '\app\controllers\SmallRoutineController@checkProblemAnswer');
    //小程序emoji——获取用户已破解题目数
    $r->addRoute('GET', '/v1/emojis/usercrackcount', '\app\controllers\SmallRoutineController@getUserCrackCount');
    //小程序emoji——用户自定义汉字对应的可能的标记标号列表
    $r->addRoute('GET', '/v1/emojis/questions/text/ids', '\app\controllers\SmallRoutineController@getEmojiesByText');
    //小程序emoji——创建题目
    $r->addRoute('POST', '/v1/emojis/questions', '\app\controllers\SmallRoutineController@createEmojiesProblem');

}
