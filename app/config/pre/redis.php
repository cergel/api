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
                    'write' => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "vote_", 'database' => 1],
                    'read' => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "vote_", 'database' => 1],
                ],
            ],
        ]
    ],
];