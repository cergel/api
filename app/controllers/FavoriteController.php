<?php

namespace app\controllers;

use app\base\BaseController;

class FavoriteController extends BaseController
{
    /**
     * 收藏或取消收藏影院
     */
    public function cinema($cinemaId)
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaId'] = $cinemaId;
        $params['status'] = $this->getRequestParams('status', 0);
        if (empty($params['openId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($params['cinemaId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_MISS_ARGUMENT);
        } else {
            $serviceRe = $this->service("Favorite")->cinema($params);
        }
        $this->jsonOut($serviceRe);
    }

    /**
     * 获取用户收藏影院,用于测试
     */
    function cinemaList()
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        if (empty($params['openId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $serviceRe = $this->service("Favorite")->cinemaList($params);
        }
        $this->jsonOut($serviceRe);
    }

    /**
     * 判断某个影院是否被收藏
     */
    public function isFavorite()
    {
        $params = [];
        $ret = $this->getStOut();
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['cinemaId'] = $this->getRequestParams('cinemaId');
        $params['channelId'] = CHANNEL_ID;
        if (empty($params['openId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $serviceRe = $this->service("Favorite")->cinemaList($params);
        }
        if ($serviceRe['ret'] == 0) {
            $cinemas = $serviceRe['data']['cinemaList'];
        }
        if (in_array($params['cinemaId'], $cinemas)) {
            $ret['data']['isFavorite'] = 1;
        } else {
            $ret['data']['isFavorite'] = 2;
        }
        $this->jsonOut($ret);
    }


    public function favoriteInfoList()
    {
        $ret = $this->getStOut();
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $this->getRequestParams('cityId', '');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $serviceRe = $this->service("Favorite")->cinemaList($params);
        }
        $cinemaList = array();
        if ($serviceRe['ret'] == 0 && !empty($serviceRe['data'])) {
            foreach ($serviceRe['data']['cinemaList'] as $key => $cinemaId) {
                $params['cinemaId'] = $cinemaId;
                $ret = $this->service('Cinema')->getCinemaInfo($params);
                if ($ret['ret'] == 0 && $ret['sub'] == 0 && in_array(CHANNEL_ID,
                        \wyCupboard::$config['app_channel_ids'])
                ) {
                    //添加格瓦拉影院id
                    $this->service("Cinema")->_formatAddGewaraCinemaId($ret['data']);
                }
                if (!empty($params['cityId'])) {
                    if (!empty($ret) && ($ret['data']['city_id'] == $params['cityId'])) {
                        $cinemaList[] = $ret['data'];
                    }
                } else {
                    if (!empty($ret)) {
                        $cinemaList[] = $ret['data'];
                    }
                }

            }
        }
        $ret['data'] = $cinemaList;
        $this->jsonOut($ret);
    }
}