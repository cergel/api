<?php
/**
 * 必要的传入参数 大key代表controller,小key代表action ,数组代表必须存在的字段
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/30
 * Time: 16:40
 */
return [
    //排期
    'Schedule' => [
        //获得城市下影院排期
        'getCity' => [
        ],
        //获得某影院(所有)影片的排期
        'getCinema' => [
        ]
    ],
    'Ticket' => [
    ],

    //影片相关
    'movies'=>[
    ],
    'cities' => [
        //获取城市列表
        'getCities' => [
        ],
    ],
    //影院相关
    'cinemas' => [
        //获取影院列表
        'getList' => [
            'cityId'=>'require|numeric',
        ],
        //获取影院详情
        'getInfo' => [
        ],
        //获取影厅座位图
        'getRoom' => [
        ],
        //获取小吃卖品信息
        'getSnack' => [
        ],
    ],
    //支付相关
    'payment' => [
        'geWaraList' => [
        ],
        'refundReason' => [
        ],
    ],
    //静态资源
    'resource' => [
        //获取影院公告
        'getAnnounce' => [
            'position' => 'require|numeric',
        ],
        //获取明星选坐信息
        'getCustomization' => [
        ],
        //获取日签内容
        'getCalendar' => [
        ],
        //获取首页订制图片
        'getIcon' => [
        ],
    ],
//    cms、资讯相关
    'cms' => [
        'getMovieNews' => [
            'movieId'
        ],
        'saveActorAppraise' =>[
            'actorId',
        ],
    ],
    'pee' => [
        'clikePee' => [
            'peeId',
            'movieId',
            'status',
        ],
    ],

];