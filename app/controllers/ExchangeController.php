<?php

namespace app\controllers;

use app\base\BaseController;

class ExchangeController extends BaseController
{
    
    /**
     * 优惠到人接口
     */
    public function discount()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service('Exchange')->discount($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 获取兑换券详情
     *
     * @param string $grouponId 团购券id
     */
    public function info($grouponId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['grouponId'] = $grouponId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['idType'] = WX_ID_TYPE_SERVICE;
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['grouponId'] )) {
            $ret = self::getErrorOut(ERRORCODE_GOOD_BONUS_EXCHANGE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service("Exchange")->info($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 卖品支付
     *
     * @return  void
     */
    public function exchangePayment()
    {
        $params = [];
        //登陆校验
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['phone'] = $this->getRequestParams('phone', '');
        $params['cinemaId'] = $this->getRequestParams('cinemaId', '');
        $params['channelId'] = CHANNEL_ID;
        $params['bank'] = "0";
        $params['subsrc'] = "30610000";
        $params['ticketType'] = "2";
        $params['category'] = "1";
        $params['uin_type'] = "1";
        //payType不同渠道不一样，1:微信,2:支付宝,7:财付通,12:京东,17:银联ApplePay,其他例如格瓦拉支付传具体银行的编号
        if ($params['channelId'] == 3) {
            $params['payType'] = 1;
        } elseif ($params['channelId'] == 28) {
            $params['payType'] = 7;
        }
        $params['payItem'] = $this->getRequestParams('payItem', '');
        $params['iPaySource'] = "115";
        $params['salePlatformType'] = SALE_PLATFORM_TYPE;
        $params['userId'] = "";
        $params['appId'] = WX_MOVIE_APP_ID;
        $params['idType'] = WX_ID_TYPE_SERVICE;
        //参数校验
        if (empty( $params['payItem'] ) || empty( $params['phone'] ) || empty( $params['cinemaId'] )) {
            $ret = self::getErrorOut(ERRORCODE_GOOD_BONUS_EXCHANGE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service("Exchange")->exchangePay($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 获取兑换券订单列表
     */
    public function orderList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['salePlatformType'] = SALE_PLATFORM_TYPE;
        $params['pageNum'] = $this->getRequestParams('page', 1);
        $params['pageSize'] = $this->getRequestParams('num', 10);
        $params['userId'] = "";
        $params['appId'] = WX_MOVIE_APP_ID;
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service("Exchange")->orderList($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 获取兑换券订单详情
     *
     * @param string $grouponId 团购券id
     */
    public function orderInfo($orderId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['orderId'] = $orderId;
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['orderId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ORDER_CENTER_PARAMS_ERROR);
        }
        else {
            $ret = $this->service("Exchange")->orderInfo($params);
        }
        $this->jsonOut($ret);
    }
    
}