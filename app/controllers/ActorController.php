<?php

namespace app\controllers;

use app\base\BaseController;

class ActorController extends BaseController
{
    public function getActorNews($actorId)
    {
        $return = self::getStOut();
        $params['channelId'] = CHANNEL_ID;
        $params['page'] = $this->getRequestParams("page");
        $params['num'] = $this->getRequestParams("num");
        $params['actorId'] = $actorId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = \wyCupboard::$sdk->call('news/get-actor-news', $params);
        if ($ret['sub'] == 0 && $ret['ret'] == 0) {
            $arrNews = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $arrNews;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }

    

}