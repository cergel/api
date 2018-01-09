<?php

namespace app\controllers;

use app\base\BaseController;

class RedSpotController extends BaseController
{
    /**
     * 获取红点信息
     *
     * @param $cityId
     */
    public function getInfo()
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service('RedSpot')->getRedSpotInfo($params);
        }
        $this->jsonOut($ret);
    }
    
}