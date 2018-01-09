<?php

namespace app\controllers;

use app\base\BaseController;

class TicketController extends BaseController
{
    //获取可售座位
    public function getAvailableSeat()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['salePlatformType'] = !empty($arrParams['salePlatformType']) ? $arrParams['salePlatformType'] : SALE_PLATFORM_TYPE;//售卖平台
        $params['appId'] = WX_MOVIE_APP_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = $this->service('User')->getSubChannelId();//子渠道，如8020000000
        $params['roomId'] = $this->getRequestParams("roomId");
        $params['cinemaId'] = $this->getRequestParams("cinemaId");
        $params['scheduleId'] = $this->getRequestParams("scheduleId");
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['bisServerId'] = $this->getRequestParams("bisServerId");
        if (empty($params['openId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $serviceRe = $this->sdk->call("ticket/qry-available-seats", $params);
        }
        $this->jsonOut($serviceRe);
    }

    //获取融合版座位
    public function getMergedSeat()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['salePlatformType'] = SALE_PLATFORM_TYPE;//售卖平台
        $params['appId'] = WX_MOVIE_APP_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = $this->service('User')->getSubChannelId();//子渠道，如8020000000
        $params['cinemaId'] = $this->getRequestParams("cinemaId");
        $params['movieId'] = $this->getRequestParams("movieId");
        $params['mpId'] = $this->getRequestParams("scheduleId");

        if (empty($params['cinemaId']) || empty($params['mpId'])) {
            $serviceRe = self::getErrorOut(ERRORCODE_GOOD_CENTER_PARAMS_ERROR);
        } else {
            $serviceRe = $this->sdk->call("ticket/qry-merge-seats", $params);
        }
        if ($serviceRe['ret'] == 0 && $serviceRe['sub'] == 0 && in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            $this->formatSeatV2($serviceRe['data']);
        }

        $this->jsonOut($serviceRe);
    }
    public function formatSeatV2(&$arrData)
    {
        $areaInfos = [];
        if (!empty($arrData['areaInfos'])) {
            $i = 0;
            foreach ($arrData['areaInfos'] as $areaId => $value) {
                $value['areaNo'] = $areaId;
                //处理行
                if (!empty($value['seatsInfos'])) {
                    $value['seatsInfos'] = array_values($value['seatsInfos']);
                }
                $areaInfos[] = $value;
                $i++;
            }
        }
        $arrData['areaInfos'] = $areaInfos;
    }

    /**
     * 是否已对某电影进行购票
     * @param $movieId
     */
    public function isBuy($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['movieId'] = $movieId;
        if (empty( $params['movieId'] ) || empty( $params['openId'] )) {
            $serviceRe = self::getErrorOut(ERRORCODE_GOOD_CENTER_PARAMS_ERROR);
        }
        else {
            $serviceRe = $this->sdk->call("msdb/is-buy-ticket", $params);
        }
        $this->jsonOut($serviceRe);
    }
}