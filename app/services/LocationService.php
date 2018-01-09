<?php

namespace app\services;


use app\base\BaseService;

class LocationService extends BaseService
{

    /**
     * IP定位
     *
     * @param array $arrParams
     *              ip          string  ip地址
     *              channelId   string  渠道编号
     *              ip          string  ip地址
     *
     * @return array
     */
    public function ip($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['ip'] = !empty($arrParams['ip']) ? $arrParams['ip'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['type'] = 3;
        if (!empty($params['ip'])) {
            $res = $this->sdk->call("locate/ip", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }

    /**
     * 经纬度定位
     *
     * @param array $arrParams
     *              latitude    float 维度
     *              longitude   float 经度
     *
     *
     * @return array
     */
    public function coordinate($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['latitude'] = !empty($arrParams['latitude']) ? $arrParams['latitude'] : '';
        $params['longitude'] = !empty($arrParams['longitude']) ? $arrParams['longitude'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        if (!empty($params['latitude']) && !empty($params['longitude'])) {
            $res = $this->sdk->call("locate/near-by-city", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            } else {
                $return['ret'] = $res['ret'];
                $return['sub'] = $res['sub'];
                $return['msg'] = $res['msg'];
            }
        }

        return $return;
    }

    /**
     * 通过汉字城市名获取对应的微影城市ID
     * @param $cityName
     * @return array
     */
    public function getWyCityId($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['cityName'] = $arrParams['cityName'];
        $params['channelId'] = $arrParams['channelId'];
        if (!empty($params['cityName']) && !empty($params['channelId'])) {
            $res = $this->sdk->call("locate/getWyCityId", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            } else {
                $return['ret'] = $res['ret'];
                $return['sub'] = $res['sub'];
                $return['msg'] = $res['msg'];
            }
        }
        return $return;
    }

}