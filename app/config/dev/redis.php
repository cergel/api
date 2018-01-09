<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 11:30
 */
return [
    //models/Speelgroup.php��ʹ��
    GROUP_SHARE_FREQUENT => [
        'common' => [
            'type' => 'default',
            'db' => [
                [
                    'write' => ['host' => '192.168.200.253', 'port' => 6379, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '192.168.200.253', 'port' => 6379, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
            ],
        ]
    ],
];