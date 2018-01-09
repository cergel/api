<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 11:30
 */
return [
    //models/Speelgroup.php在使用
    GROUP_SHARE_FREQUENT => [
        'common' => [
            'type' => 'default',
            'db' => [
                [
                    'write' => ['host' => '10.66.143.36', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.144.241', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.126.222', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.144.241', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.145.158', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.145.159', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.179.83', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.145.159', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.145.16', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.146.119', 'port' => 28004, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.179.84', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                    'read' => ['host' => '10.66.146.119', 'port' => 28005, 'timeout' => 3, 'password' => '', "prefix" => 'frequent_', "database" => 0,],
                ],
            ],
        ],
    ],
];