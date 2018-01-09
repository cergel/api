<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/7
 * Time: 11:42
 */
/**
 * 用于某个接口在多长时间
 * redisConf为一个redis的配置
 * rule对应的一级key为controller,二级key为action,里面的second为几秒钟，times为此接口可以接受的最多请求数
 */
return [
    'redisConf'=>[
        'type' => 'default',
        'db' => [
            [
                'write' => ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 10, 'password' => '', "prefix" => "", "database" => 0,],
                'read' => ['host' => '127.0.0.1', 'port' => 6379, 'timeout' => 10, 'password' => '', "prefix" => "", "database" => 0,],
            ],
        ],
    ],
    'rule'=>[
    ]
];