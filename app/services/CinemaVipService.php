<?php

namespace app\services;


use app\base\BaseService;
use app\helper\Utils;

class CinemaVipService extends BaseService
{

    /**
     * 获取用户的会员卡列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getUserCardList($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $return['data']->list = [];
        $return['data']->tipText = '';
        if (!empty($arrParams['openId'])) {
            $params = [];
            $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
            $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
            $params['cardNo'] = $this->getParam($arrParams, 'cardNo');
            $params['cinemaId'] = $this->getParam($arrParams, 'cinemaId');
            $return = $this->sdk->call("cinema-vip/get-user-card-list", $params);
        }

        return $return;
    }

    /**
     * 获取用户的会员卡列表
     * 这个是 我的-会员卡 页面, 查询用户所有的会员卡列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getUserCardListByPage($arrParams = [])
    {
        $return = self::getStOut();
        if (!empty($arrParams['openId']) && !empty($arrParams['channelId'])) {
            $return = $this->sdk->call("cinema-vip/get-user-card-list-by-page", $arrParams);
        }

        return $return;
    }

    /**
     * 获取会员卡列表(和用户无关)
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCardList($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();

        $params = [];
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        $params['cinemaId'] = !empty($arrParams['cinemaId']) ? $arrParams['cinemaId'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $res = $this->sdk->call("cinema-vip/get-card-list", $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        }

        return $return;
    }

    /**
     * 获取某个城市下的所有会员卡列表,支持分页(和用户无关)
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCityCardList($arrParams = [])
    {
        $return = self::getStOut();
        if (!empty($arrParams['cityId']) && !empty($arrParams['channelId'])) {
            $return = $this->sdk->call("cinema-vip/get-city-card-list-by-page", $arrParams);
        }

        return $return;
    }

    /**
     * 获取会员卡详情
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCardInfo($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['typeId'] = !empty($arrParams['typeId']) ? $arrParams['typeId'] : '';
        $params['subTypeId'] = !empty($arrParams['subTypeId']) ? $arrParams['subTypeId'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $res = $this->sdk->call("cinema-vip/get-card-info", $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        }

        return $return;
    }

    /**
     * 获取支付串
     * @param array $arrParams
     * @param       string memberCardInfo 会员卡信息,json类型
     *
     * @return array
     */
    public function payment($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['phone'] = !empty($arrParams['phone']) ? $arrParams['phone'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['orderSource'] = !empty($arrParams['orderSource']) ? $arrParams['orderSource'] : WX_SALE_APP_ID;;
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        $params['memberCardInfo'] = !empty($arrParams['memberCardInfo']) ? $arrParams['memberCardInfo'] : '';
        //如果活动ID不为空，就传给service
        if (!empty($arrParams['actType'])) {
            $params['actType'] = $arrParams['actType'];
        }
        //基础参数处理
        $this->service('Payment')->_formatParams($arrParams, $params);
        $response = $this->sdk->call("pay/vipcard", $params);
        if (!empty($response) && isset($response['ret']) && ($response['ret'] == 0) && !empty($response['data'])) {
            $this->service('Payment')->_formatResponse($response);
            $return['data'] = $response['data'];
        }

        return $return;
    }

    /**
     * 获取支付串(格瓦拉)
     * @param array $arrParams
     * @param       string memberCardInfo 会员卡信息,json类型
     *
     * @return array
     */
    public function gwlPayment($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['phone'] = !empty($arrParams['phone']) ? $arrParams['phone'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['orderSource'] = !empty($arrParams['orderSource']) ? $arrParams['orderSource'] : WX_SALE_APP_ID;;
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        $params['memberCardInfo'] = !empty($arrParams['memberCardInfo']) ? $arrParams['memberCardInfo'] : '';
        $params['payType'] = !empty($arrParams['payType']) ? $arrParams['payType'] : '';
        //如果活动ID不为空，就传给service
        if (!empty($arrParams['actType'])) {
            $params['actType'] = $arrParams['actType'];
        }
        $params['tradeType'] = 'APP';
        $params['isGwl'] = true;
        //如果格瓦拉京东支付则添加returnUrl
        $payType = $params['payType'];
        $appPayType = [1, 2, 7, 17];
        $typeParams = '';
        $channelId = CHANNEL_ID;
        if (!in_array($payType, $appPayType) || $payType == 12) {
            $params['tradeType'] = 'WAP';
            $type = json_decode($params['memberCardInfo'], true);
            if ($type) {
                $typeParams = $type[0]['typeId'] . "_" . $type[0]['subTypeId'];
            }
            $params['returnUrl'] = Utils::getHost() . "/wap-payment/gewara-vipcard-success/{$typeParams}?channelId=".CHANNEL_ID;
        }
        //如果京东支付单独修改支付类型为wap
        if ($payType == 12) {
            $params['tradeType'] = 'WAP';
            $params['errorUrl'] = Utils::getHost() . "/wap-payment/vipcard-error?channelId=".CHANNEL_ID;
        }
        //格瓦拉
        $params['reqSource'] = !empty($arrParams['reqSource']) ? $arrParams['reqSource'] : '';
        $params['merchantCode'] = !empty($arrParams['merchantCode']) ? $arrParams['merchantCode'] : '';
        $params['bankCode'] = !empty($arrParams['bankCode']) ? $arrParams['bankCode'] : '';
        $response = $this->sdk->call("pay/vipcard", $params);
        if (!in_array($payType, $appPayType) && $response['ret'] == 0 && $response['sub'] == 0) {
            $response = $this->sdk->call("payment/gwl-payment-link-for-vip", compact('channelId', 'response', 'payType'))['response'];
        }
        return $response;
    }

    /**
     * 检测用户是否已经买过某个折扣卡
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function checkUserBuy($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = false;
        if (!empty($arrParams['channelId']) && !empty($arrParams['typeId'])) {
            $return = $this->sdk->call("cinema-vip/check-user-by", $arrParams);
        }

        return $return;
    }

}
