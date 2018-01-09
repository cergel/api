<?php

namespace app\controllers;

use app\base\BaseController;
use app\helper\Net;

class LocationController extends BaseController
{

    /**
     * IP定位
     * 可以通过request参数ip，来debug
     *
     * @example v1/location/ip
     */
    public function ip()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['ip'] = $this->getRequestParams('ip', Net::getRemoteIp());
        $ret = $this->service('Location')->ip($params);
        $this->jsonOut($ret);
    }

    /**
     * 经纬度定位
     *
     * @param  latitude  string   维度
     * @param  longitude string   经度
     *
     * @example v1/location/coordinate?latitude=36.050398814936806&longitude=103.834833473815951
     */
    public function coordinate()
    {
        $params = [];
        $strLatitude = $this->getRequestParams('latitude', '');
        $strLongitude = $this->getRequestParams('longitude', '');
        if (empty($strLatitude) || empty($strLongitude)) {
            $ret = $this->getErrorOut(ERRORCODE_MISS_ARGUMENT);
        } else {
            $params['channelId'] = CHANNEL_ID;
            $params['latitude'] = (float)$strLatitude;
            $params['longitude'] = (float)$strLongitude;
            $ret = $this->service('Location')->coordinate($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 通过汉字城市名获取对应的微影城市ID
     * @param $cityName
     * @return array
     */
    public function getWyCityId()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityName'] = $this->getRequestParams('cityName', '');
        $ret = $this->service('Location')->getWyCityId($params);
        $this->jsonOut($ret);
    }
}