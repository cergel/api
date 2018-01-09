<?php

namespace app\controllers;

use app\base\BaseController;

class CityController extends BaseController
{
    //调用SDK获取城市列表
    public function getCities()
    {
        $return = self::getStOut();
        $ret = \wyCupboard::$sdk->call('city/read-city', ['channelId' => CHANNEL_ID]);
        if ($ret['sub'] == 0 AND $ret['ret'] == 0) {
            $cityArr = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $cityArr;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }
    //调用SDK获取城市列表
    public function getCitiesV2()
    {
        $return = self::getStOut();
        $ret = \wyCupboard::$sdk->call('city/read-city-v5', ['channelId' => CHANNEL_ID]);
        if ($ret['sub'] == 0 AND $ret['ret'] == 0) {
            $cityArr = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $cityArr;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }
    
    /**
     * V5版本的城市列表接口
     */
    public function getCitiesV5()
    {
        $return = self::getStOut();
        $ret = \wyCupboard::$sdk->call('city/read-city-v5', ['channelId' => CHANNEL_ID]);
        if ($ret['sub'] == 0 AND $ret['ret'] == 0) {
            $cityArr = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $cityArr;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }

    public function getWaraCity()
    {
        $return = self::getStOut();
        $ret = \wyCupboard::$sdk->call('city/read-city-gewala', ['channelId' => CHANNEL_ID]);
        if ($ret['sub'] == 0 && $ret['ret'] == 0) {
            $cityArr = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $cityArr;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }

    

}