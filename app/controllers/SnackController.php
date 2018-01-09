<?php

namespace app\controllers;

use app\base\BaseController;

class SnackController extends BaseController
{

    /**
     * 获取小吃列表
     * 正常来说,获取小吃列表,只需要 suitableNumber 和 cinemaId 就可以了。
     * 后来小吃加价购需求,需要返回小吃的优惠列表,而这个优惠列表和订单、排期、用户都有关联,所以,当发现有这几个参数的时候,才会返回小吃的优惠信息
     *
     */
    public function snackList($cinemaId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaNo'] = $cinemaId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['orderId'] = $this->getRequestParams("orderId", '');
        $params['suitableNumber'] = $this->getRequestParams("suitableNumber", '');
        $params['mpId'] = $this->getRequestParams("mpId", '');
        $ret = $this->service('Snack')->getSnackList($params);

        $this->jsonOut($ret);
    }

    /**
     * 获取小吃列表
     * 正常来说,获取小吃列表,只需要 suitableNumber 和 cinemaId 就可以了。
     * 后来小吃加价购需求,需要返回小吃的优惠列表,而这个优惠列表和订单、排期、用户都有关联,所以,当发现有这几个参数的时候,才会返回小吃的优惠信息
     * 卖品三期加上了推荐小吃规则
     */
    public function snackListV2($cinemaId = '')
    {
        $params = [];
        $rightParams = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaNo'] = $cinemaId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['orderId'] = $this->getRequestParams("orderId", '');
        $params['suitableNumber'] = $this->getRequestParams("suitableNumber", '');
        $params['mpId'] = $this->getRequestParams("mpId", '');
        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $rightParams['memberId'] = $this->getRequestParams("memberId");
            $rightParams['seatNum'] = $this->getRequestParams("seatNum");
            $rightParams['cityCode'] = $this->getRequestParams("cityId", 10);
            $rightParams['partnerId'] = \wyCupboard::$config['gewara_app_id'][CHANNEL_ID];
            $rightParams['movieId'] = $this->getRequestParams("movieId");
            $rightParams['sellPrice'] = $this->getRequestParams("sellPrice");
            $rightParams['costPrice'] = $this->getRequestParams("costPrice");
            $rightParams['edition'] = $this->getRequestParams("edition");
            $rightParams['language'] = $this->getRequestParams("language");
            $rightParams['playTime'] = $this->getRequestParams("playTime");
            $rightParams['appVersion'] = $this->getRequestParams("appVersion");
        }
        $ret = $this->service('Snack')->getSnackListV2($params, $rightParams);
        $this->jsonOut($ret);
    }

    /**
     * 卖品支付
     *
     * @return  void
     */
    public function snackPayment()
    {
        $arrSendParams = [];
        //登陆校验
        $arrSendParams['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($arrSendParams['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $arrSendParams['phone'] = $this->getRequestParams('phone', '');
        $arrSendParams['channelId'] = CHANNEL_ID;
        //payType不同渠道不一样，1:微信,2:支付宝,7:财付通,12:京东,17:银联ApplePay,其他例如格瓦拉支付传具体银行的编号
        if ($arrSendParams['channelId'] == 3) {
            $arrSendParams['payType'] = 1;
        } elseif ($arrSendParams['channelId'] == 28) {
            $arrSendParams['payType'] = 7;
        }
        $arrSendParams['cinemaNo'] = $this->getRequestParams('cinemaId', '');
        $arrSendParams['buySource'] = $this->getRequestParams('buySource', '');
        $arrSendParams['orderSource'] = WX_SALE_APP_ID;
        $arrSendParams['cityNo'] = $this->getRequestParams('cityId', '');
        $arrSendParams['tradeType'] = 'JSAPI';
        $arrSendParams['spm'] = $this->getSpmParam();
        $arrSendParams['snackInfos'] = $this->getRequestParams('snackInfos', '');
        if (!empty($arrSendParams['snackInfos'])) {
            $arrSendParams['snackInfos'] = json_decode($arrSendParams['snackInfos'], true);
        }
        //参数校验
        if (empty($arrSendParams['cinemaNo']) || empty($arrSendParams['cityNo']) || empty($arrSendParams['phone']) || empty($arrSendParams['snackInfos'])) {
            $ret = self::getErrorOut(ERRORCODE_GOOD_CENTER_PARAMS_ERROR);
        } else {
            $ret = $this->service("Snack")->snackPay($arrSendParams);
        }
        $this->jsonOut($ret);
    }


}