<?php

$arr = [
    //service中,哪些不记录(这个其实没用,因为service的return都不记录了,这里是留作后用)
    'service' => [
        'comment/get-movie-comment',
        'comment/get-movie-comment-v2',
        'movie/get-movie-will-with-date',
        'cms/get-cms-news-list',
        'movie/get-movie-actor-list',
        'search/search-movie-cinema-list-from-es',
        'search/search-cinema-filters',
        'search/search-cinema-list-from-es',
        'movie/read-city-movie-by-page',
        'movie/get-movie-will-with-date',
        'movie/read-movie-info',
        'cinema/read-cinema-info',
        'sche/read-cinema-sche-and-format',
        'order/query-order-list-v1',
        'bonus/suit-bonus-info'
    ],
    //项目中的
    'app'     => [
        //GET请求白名单(白名单中的,记录response)
        'GET' => [
            'Snack_snackList',
            'Snack_snackListV2',
            'Ticket_getMergedSeat',
            'Wechat_getFollowStatus',
            'Bonus_getBonus',
            'Bonus_getBindBonusInfo',
            'User_getUserProfile',
            'User_checkOpenidStatus',
            'Order_payV2',
            'CinemaVip_getCardList',
            'CinemaVip_getUserCardList',
            'CinemaVip_getCardInfo',
            'CinemaVip_payment',
            'Location_ip',
            'Location_coordinate',
        ],
    ],

];

return $arr;