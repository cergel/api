<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/8/18
 * Time: 18:19
 */

namespace app\services;

use app\base\BaseService;

class BonusService extends BaseService
{
    /**
     * 获取可用优惠
     *
     * @param array $arrParams
     *
     * @return array|mixed
     */
    public function getBonus($arrParams = [])
    {
        $return = self::getStOut();
        //锁座的临时订单
        $hasBuy = $this->checkUserBuy($arrParams['openId'], WX_ID_TYPE_SERVICE, $arrParams['channelId']);
        $arrParams['isNew'] = $hasBuy ? '0' : '1';
        $arrParams['unpayedOrderId'] = $arrParams['sTempOrderID'];
        $arrParams['status'] = 1;//可用
        $arrBonusRet = $this->queryBonous($arrParams);
        $arrReturn = [];
        if (isset($arrBonusRet['data'])) {
            $arrReturn['bonus_discount_list'] = $arrBonusRet['data'];
            if (isset($arrBonusRet['data']['vCardList'])) {
                $arrReturn['cardlist'] = $arrBonusRet['data']['vCardList'];
                unset($arrBonusRet['data']['vCardList']);
            } else {
                $arrReturn['cardlist'] = [];
            }
        }
        $return['data'] = $arrReturn;
        return $return;
    }

    /**
     * 获取可用优惠V2版本，融合礼品卡
     * @param array $arrParams
     * @return array|mixed
     */
    public function getBonusV2($arrParams = [])
    {
        $return = self::getStOut();
        //锁座的临时订单
        $strUnPayedOrderId = $arrParams['sTempOrderID'];
        $hasBuy = $this->checkUserBuy($arrParams['openId'], WX_ID_TYPE_SERVICE, $arrParams['channelId']);
        $arrParams['isNew'] = $hasBuy ? '0' : '1';
        $arrParams['unpayedOrderId'] = $strUnPayedOrderId;
        $arrParams['status'] = 1;//可用
        $arrParams['isQrycard'] = 1;//查询礼品卡
        $arrParams['page'] = 1;
        $arrParams['num'] = 1000;
        $arrParams['orderId'] = $arrParams['sTempOrderID'];
        $arrParams['subChannelId'] = $arrParams['saleObjType'];
        //新增俩字段  影院Id,购买的座位数 2015-12-01
        if (!empty($arrParams['cinemaId'])) {
            $arrParams['cinId'] = $arrParams['cinemaId'];
        }
        $res = $this->sdk->call('bonus/queryBonus', $arrParams);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        }
        return $return;
    }

    /**
     * 获取所有优惠
     * @param array $arrParams
     * @return array|mixed
     */
    public function getAllBonus($arrParams = [])
    {
        $return = self::getStOut();
        $hasBuy = $this->checkUserBuy($arrParams['openId'], WX_ID_TYPE_SERVICE, $arrParams['channelId']);
        $arrParams['isNew'] = $hasBuy ? '0' : '1';
        $arrParams['status'] = 0;//所有
        $arrBonusRet = $this->queryBonous($arrParams);
        $return['data'] = isset($arrBonusRet['data']) ? $arrBonusRet['data'] : [];
        return $return;
    }

    /**
     * 查用户是否有过购买
     *
     * @param string $openId
     * @param string $channelId
     *
     * @return boolean
     */
    function checkUserBuy($openId, $saleType, $channelId)
    {
        $sdkUrl = 'user/check-new-user';
        $arrSendParams = [
            'openId' => $openId,
            'idType' => $saleType,
            'channelId' => $channelId,
        ];
        //调用“判断新老用户接口”。老用户买过，新用户未买过
        $response = $this->sdk->call($sdkUrl, $arrSendParams);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $response = $response['data'];

            return ($response['isNew']) ? false : true;
        } else {
            return false; //调用失败也认为是没有购买
        }
    }

    /**
     * 查询可用优惠
     *
     * @param array $arrSendParams 发送参数
     * @param int $intTryTimes 对Java接口共发起的请求次数（当连接不上Java主机的时候，如果$tryTimes>1，可再发起最多n-1次请求）
     * @param int $incResTimeOut 如果连接Java主机成功，等待Java返回内容的超时时间，0为不超时
     *
     * @return mixed
     */
    public function queryBonous($arrParams = [])
    {
        //获取可用优惠
        $arrSendParams = [
            'channelId' => $arrParams['channelId'],
            'status' => $arrParams['status'],    //1表示获取所有可用的，0则是获取全部
            'mpid' => $arrParams['scheduleId'],
            'page' => 1,
            'num' => 1000,   //要一次性把所有可用优惠都返回，设置1000目前应该足够了
            'sUin' => $arrParams['openId'],
            'orderId' => $arrParams['sTempOrderID'],
            'openId' => $arrParams['openId'],
            'scheduleId' => $arrParams['scheduleId'],
            'salePlatformType' => $arrParams['salePlatformType'],
            'subChannelId' => $arrParams['saleObjType'],
            'appId' => $arrParams['appId'],
            'phone' => self::getParam($arrParams, 'phone'),
        ];

        //新增俩字段  影院Id,购买的座位数 2015-12-01
        if (!empty($arrParams['cinemaId'])) {
            $arrSendParams['cinId'] = $arrParams['cinemaId'];
        }
        if (!empty($arrParams['ticket'])) {
            $arrSendParams['tktCnt'] = $arrParams['ticket'];
        }
        //是否返回不可用优惠，0不返回，1返回
        if (isset($arrParams['invalidFlg'])) {
            $arrSendParams['invalidFlg'] = $arrParams['invalidFlg'];
        }
        $arrBonusRet = $this->sdk->call('bonus/queryBonus', $arrSendParams);

        return $arrBonusRet;
    }

    /**
     * 查询礼品卡
     *
     * @param array $arrSendParams 发送参数
     * @param int $intTryTimes 对Java接口共发起的请求次数（当连接不上Java主机的时候，如果$tryTimes>1，可再发起最多n-1次请求）
     * @param int $incResTimeOut 如果连接Java主机成功，等待Java返回内容的超时时间，0为不超时
     *
     * @return mixed
     */
    public function qryCard($arrParams = [])
    {
        $cardSendParams = [
            'channelId' => $arrParams['channelId'],
            'status' => 1,    //查询有效优惠
            'mpid' => $arrParams['scheduleId'],
            'openId' => $arrParams['openId'],
            'userId' => $arrParams['userId'],
            'auth_id' => '',
            'subChannelId' => $arrParams['saleObjType'],
            'page' => 1,
            'num' => 1000,   //要一次性把所有可用优惠都返回，设置1000目前应该足够了，后续可优化
        ];
        $arrRet = $this->sdk->call('giftcard/query-giftcard', $cardSendParams);

        return $arrRet;
    }

    /**
     * 优惠到人接口
     *
     * @param $strOpenId
     *
     * @return mixed
     */
    public function discount($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $res = $this->sdk->call('task/discount', $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        }
        if (empty($return['data'])) {
            $return['data'] = new \stdClass();
        }

        return $return;
    }

    /**
     * 获取首页红包状态
     * @param array $arrParams (channelId openId)
     * @return \stdClass
     */
    public function getStatus($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        //$params['openId'] = $arrParams['openId'];
        $res = $this->sdk->call('bonus/get-status', $params);
        return $res;
    }


    /**
     * 获取可用优惠
     *
     * @param array $arrParams
     *
     * @return array|mixed
     */
    public function getUserBonusList($arrParams = [])
    {
        //锁座的临时订单
        $arrBonusRet = $this->sdk->call('bonus/query-gewara-bonus-list', $arrParams);
        $this->filterHtmlLabel($arrBonusRet);
        return $arrBonusRet;
    }

    /**
     * 支付页获取可用优惠
     *
     * @param array $arrParams
     *
     * @return array|mixed
     */
    public function getPayBonusList($arrParams = [])
    {
        //锁座的临时订单
        $arrBonusRet = $this->sdk->call('bonus/query-gewara-bonus-and-pay-list', $arrParams);
        $this->filterHtmlLabel($arrBonusRet);
        return $arrBonusRet;
    }

    /**
     * 通兑码兑换
     *
     * @param array $arrParams
     *
     * @return array|mixed
     */
    public function exchangeCode($arrParams = [])
    {
        $arrBonusRet = $this->sdk->call('bonus/exchange-gewara-code', $arrParams);
        return $arrBonusRet;
    }

    /**
     * v卡列表
     * @param $arrInput
     * @return mixed
     */
    public function getVcardList($arrInput)
    {
        $arrBonusRet = $this->sdk->call('bonus/gewara-vcard-list', $arrInput);
        return $arrBonusRet;
    }

    public function activeGewaraVcard($arrInput)
    {
        $arrBonusRet = $this->sdk->call('bonus/gewara-vcard-active', $arrInput);
        return $arrBonusRet;
    }

    public function vcardInfo($arrInput)
    {
        $arrBonusRet = $this->sdk->call('bonus/gewara-vcard-info', $arrInput);
        return $arrBonusRet;
    }

    /**
     * 过滤bnsInfoList\presellInfoList\couponInfoList中的description中的html标签
     * @param $items
     */
    private function filterHtmlLabel(&$data)
    {
        //处理
        $filterItems = array(
            'bnsInfoList',
            'presellInfoList',
            'couponInfoList',
        );
        foreach ($filterItems as $filterItem) {
            if (isset($data['data'][$filterItem]) && !empty($data['data'][$filterItem])) {
                foreach ($data['data'][$filterItem] as &$item) {
                    if (isset($item['description']) && !empty($item['description'])) {
                        $item['description'] = strip_tags($item['description']);
                    }
                }
            }
        }
    }


}