<?php
return [
    //设置接口的签名密钥on为打开签名off为关闭签名
    'switch' => "on",
    //以下设置为true的方法开启签名验证
    'required' => [
        'Ticket' => [
            //获取可售座位图
            'getAvailableSeat',
            //锁坐接口
            'lockSeat',
        ],
        'Order' => [
            //获取未支付订单
            'getUnpaidOrder',
            //获取订单列表
            'getOrderList',
            //获取订单详情
            'getOrderDetail',
            //订单退款
            'refundOrder',
        ],
    ]

];