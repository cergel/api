<?php

namespace app\controllers;

use app\base\BaseController;

class WechatController extends BaseController
{
    
    /**
     * 获取用户是否关注了公众号
     */
    public function getFollowStatus()
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service('Wechat')->getFollowStatus($params);
            
        }
        $this->jsonOut($ret);
    }
    
}