<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/29
 * Time: 上午11:17
 */

namespace app\controllers;


use app\base\BaseController;

class DiscountController extends BaseController
{

    //获取用户可用红包列表
    public function getDiscounts()
    {
        $channelId = CHANNEL_ID;
        $status = $this->getRequestParams("status");
        $scheduleId = $this->getRequestParams("scheduleId");
        $page = $this->getRequestParams("page", 1);
        $num = $this->getRequestParams("num", 10);
        $idfa = $this->getRequestParams("idfa");
        $DeviceId = $this->getRequestParams("DeviceId", '');
        $imei = $this->getRequestParams("imei", '');
        $cinId = $this->getRequestParams("cinemaId", '');
        $tktCnt = $this->getRequestParams("tktCnt", '');
        $params = [
            'openId' => $this->service('Login')->getOpenIdFromCookie(),
            'channelId' => $channelId,
            'salePlatformType' => SALE_PLATFORM_TYPE,
            'subChannelId' => $channelId,
            'userId' => $this->service('Login')->getAuthInfoByToken("uid"),
            'status' => $status,
            'page' => $page,
            'num' => $num,
            'appId' => APP_ID,
        ];
        //如果有排期搜集排期ID
        if ($scheduleId) {
            $params['scheduleId'] = $scheduleId;
        }
        //搜集影院ID与票的张数
        if ($cinId && $tktCnt) {
            $params['cinId'] = $cinId;
            $params['tktCnt'] = $tktCnt;
        }
        //搜集设备信息
        if ($channelId == 8 && $idfa) {
            $params['idfa'] = $idfa;
        }
        if ($channelId == 9 && $imei && $DeviceId) {
            $params['deviceid'] = $DeviceId;
            $params['imei'] = $imei;
        }
        $ret = $this->sdk->call("bonus/query-bonus", $params);
        $this->jsonOut($ret);
    }

}