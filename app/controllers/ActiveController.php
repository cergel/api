<?php
/**
 * 活动类接口
 */
namespace app\controllers;

use app\base\BaseController;

class ActiveController extends BaseController
{
    /**
     * 银行卡优惠
     */
    public function BankPrivilege()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $this->getRequestParams('cityId');
        $ret = $this->service('Active')->BankPrivilege($params);
        $this->jsonOut($ret);
    }

    /**
     * 首页拉新促销弹框活动
     */
    public function NewcomerBonus()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = WX_SALE_APP_ID;
        if (empty($params['openId'])) {
            $ret = self::getStOut();
        } else {
            $ret = $this->service('Active')->NewcomerBonus($params);
        }
        $this->jsonOut($ret);
    }

}