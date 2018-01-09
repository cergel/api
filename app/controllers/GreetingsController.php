<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/10/12
 * Time: 14:08
 */

namespace app\controllers;
use app\base\BaseController;

class GreetingsController extends BaseController
{
    /**
     * 获取线上的明星祝福
     */
    public function getGreet(){
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $serviceRe = $this->service('Greetings')->getGreet($params);
        $this->jsonOut($serviceRe);
    }
}