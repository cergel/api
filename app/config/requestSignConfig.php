<?php
/***
 * 接口验签配置说明
 * 客户端每次发版前需要约定当前客户端版本的密钥,否则会使用默认密钥进行签名验证
 * 如果需要停止客户端的某一个渠道某一个版本的接入仅需在本配置文件中吊销对应渠道的签名密钥即可
 * 不需要签名验证的页面[HTML5支付页,部分活动的落地页,APP升级检测接口]在except中排除即可
 * 建议使用BASE64上传的接口由于请求体巨大不要参与验签
 * switch 为签名验证的总开关调试模式下改成off即可不验证签名
 */
return [
    //设置接口的签名密钥on为打开签名off为关闭签名
    //版本号配置规则：不需要每个版本都添加，只需要在key值改变的版本添加一条即可，会自动进行匹配。
    'switch' => "on",
    'secret' => [
        '8' => [
            '1.2.0' => 'IrFhnAZkluFUTLfn6cwxVG5D',
            '6.1.8' => 'qt6vounGx9fzI12bCpmS863tY3',
            '7.1.1' => 'XELHlrr4LQU7JSD3cIn4FMkk',
        ],
        '9' => [
            '1.2.0' => '3KditP8mu6wfNhAakaBj48dg',
            '7.1.0' => 'D3xl5s3RIABXXrKDESnDIIqa1P',
            '7.1.1' => 'XELHlrr4LQU7JSD3cIn4FMkk',
            '7.6.0' => 'XELHlrr4LQU7JSD3cIn4FMkk',
            '7.7.0' => 'XELHlrr4LQU7JSD3cIn4FMkk',
        ],
        '80' => [
            '0.1.0' => '3KditP8mu6wfNhAakaBj48dg',
            '9.0.0' => '3KditP8mu6wfNhAakaBj48dg',
        ],
        '84' => [
            '0.1.0' => '3KditP8mu6wfNhAakaBj48dg',
            '9.0.0' => '3KditP8mu6wfNhAakaBj48dg',
        ],
        'default' => 'XELHlrr4LQU7JSD3cIn4FMkk'
    ],

    //以下设置的方法均不需要设置签名串
    'except' => [
        'Cities' => [
            'getCities',
        ],
        'Movie' => [
            'getNow',
            'getLater',
            'getInfo',
            'getWxMovieId',
            'getWxActorId',
            'GewaraMovieIdPush'
        ],
        'Cinema' => [
            'getList',
            'getInfo',
            'getRoom',
            'getSnack',
            'GewaraStaticAll',
        ],
        'Resource' => [
            'getAnnounce',
            'getCustomization',
            'getCalendar',
            'getIcon',
        ],
        'Payment' => [
            'geWaraList',
            'refundReason',
            'easyPaymentVipGewara',
            'GewaraSuccess',
            'easyPaymentGewara',
            'easyPaymentJd',
            'easyPaymentVipJd',
            'GewaraVipSuccess',
            'GewaraVipError',
            'bankcard',
            'gwlPayMethods',
            'payBin',
        ],
        'City' => [
            'getWaraCity',
        ],
        'Find' =>[
            'getFindInfo',
            'getFindList',
        ],
        'CinemaVip'=>[
            'jump',
            'showMyCard'
        ],
        'Security' => [
            'getPicCode',
        ],
    ]

];