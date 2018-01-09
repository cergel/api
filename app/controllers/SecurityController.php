<?php

namespace app\controllers;

use app\base\BaseController;

class SecurityController extends BaseController
{
    /**
     * 获取图片验证码
     *
     * @param $id 用户的openId
     * @param $channelId 渠道
     */
    public function getPicCode()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        //用户的openId
        $params['id'] = $this->service('Login')->getOpenIdFromCookie();;
        $ret = $this->service('Security')->generatePictureVerifyCode($params);
        $this->jsonOut($ret);
    }
}