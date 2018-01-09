<?php

namespace app\controllers;

use app\base\BaseController;

class ScheController extends BaseController
{
    /**
     * 获取某影院下的所有影片排期
     *
     * @param $cityId
     */
    public function getInfo($cinemaId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaId'] = $cinemaId;
        $params['movieId'] = $this->getRequestParams('movieId', '');
        $params['cityId'] = $this->getRequestParams('cityId', '');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //微信小程序专用
        $params['needMore'] = $this->getRequestParams('needMore', 1);
        $params['all'] = $this->getRequestParams('all', 2);
        $ret = $this->service('Sche')->getScheInfo($params);
        $this->jsonOut($ret);
    }

    /**
     * 某个影院所有电影排期V2接口
     * @param int $cinemaId 影院ID，必传
     * @param int $cityId 城市ID，必传
     */
    public function getInfoV2($cinemaId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaId'] = $cinemaId;
        $params['movieId'] = $this->getRequestParams('movieId', '');
        $params['payReduceId'] = $this->getRequestParams('payReduceId', '');
        $params['needMore'] = $this->getRequestParams('needMore', 3); //是否需要其他影片基础信息，1->需要 2->不需要，3->返回全部完整数据，不传默认是3
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Sche')->getScheInfoV2($params);
        if ($ret['ret'] == 0 && $ret['sub'] == 0 && in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            //格式化影院排期数据
            $this->service("Sche")->_formatAppScheV2($ret['data']);
        }
        //格式化label
        $this->service("Sche")->_formatScheLabel($ret['data']);
        $this->jsonOut($ret);
    }

    /**
     * 获取某排期扩展属性（是否配有3D 是否需要自带3D眼镜）
     *
     * @param $cityId
     */
    public function getScheduleExt($scheduleId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['scheduleId'] = $scheduleId;
        $params['cinemaId'] = $this->getRequestParams('cinemaId', '');
        $ret = $this->service('Sche')->getScheduleExt($params);
        $this->jsonOut($ret);
    }

}