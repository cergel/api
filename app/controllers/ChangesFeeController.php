<?php
/**
 * 有偿退改签相关接口
 */
namespace app\controllers;

use app\base\BaseController;

class ChangesFeeController extends BaseController
{
    /**
     * 影院退改签手续费规则
     */
    public function getFeeInfo($cinemaNo)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaNo'] = $cinemaNo;
        $ret = $this->service('ChangesFee')->getFeeInfo($params);
        $this->jsonOut($ret);
    }

    /**
     * 影院退改签当前费用
     */
    public function getCurrentFee()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaNo'] = $this->getRequestParams('cinemaNo');
        $params['showDate'] = $this->getRequestParams('showDate');
        $ret = $this->service('ChangesFee')->getCurrentFee($params);
        $this->jsonOut($ret);
    }

}