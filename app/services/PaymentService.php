<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/28
 * Time: 上午11:17
 */

namespace app\services;


use app\base\BaseService;
use app\helper\Utils;

class PaymentService extends BaseService
{
    //小程序渠道
    private $smallChannelId = [63, 66, 67, 68];
    //微信支付渠道
    private $WXPayChannelId = [3, 63, 66, 67, 68];
    //财付通支付渠道
    private $QQPayChannelId = [28];
    //娱票儿app支付渠道
    private $ypAppChannelId = [8, 9];
    //当前支付PayType
    private $payType = '';

    /**
     * 获取当前支付的payType
     * @param array $arrParams
     * @return int|string
     */
    public function getPayType($arrParams = [])
    {
        $payType = $this->payType;
        if (!empty($payType)) {
            return $payType;
        }
        if (in_array(CHANNEL_ID, $this->WXPayChannelId)) {
            //固定微信支付
            $payType = 1;
        } elseif (in_array(CHANNEL_ID, $this->QQPayChannelId)) {
            //固定手Q支付
            $payType = 7;
        } else {
            //其他支付方式
            $payType = !empty($arrParams['payType']) ? $arrParams['payType'] : '';
        }
        $this->payType = $payType;
        return $payType;
    }

    /**
     * 获取当前支付的tradeType
     * @param array $arrParams
     * @return string
     */
    public function getTradeType($arrParams = [])
    {
        //默认是wx支付
        $tradeType = 'JSAPI';
        $payType = $this->getPayType($arrParams = []);
        if (in_array(CHANNEL_ID, $this->smallChannelId)) {
            //小程序
            $tradeType = 'SmallJSAPI';
        } elseif (in_array(CHANNEL_ID, $this->ypAppChannelId)) {
            //娱票儿App默认
            $tradeType = 'APP';
        }
        if ($payType == 12) {
            $tradeType = 'WAP';
        }
        return $tradeType;
    }

    /**
     * 添加折扣卡支付的returnUrl
     * @param array $arrParams
     * @param array $params
     */
    public function setVipCardUrl($arrParams = [], &$params = [])
    {
        $gewara_config = \wyCupboard::$config['gawara_pay_type'];
        $payType = $this->getPayType($arrParams = []);
        //如果格瓦拉京东支付则添加returnUrl
        if (in_array($payType, $gewara_config) || $payType == 12) {
            if ($arrParams['memberCardInfo']) {
                $typeParams = $arrParams[0]['typeId'] . "_" . $arrParams[0]['subTypeId'];
            }

            $params['returnUrl'] = Utils::getHost() . "/wap-payment/gewara-vipcard-success/{$typeParams}";
        }
        //如果京东支付单独修改支付类型为wap
        if ($payType == 12) {
            $params['errorUrl'] = Utils::getHost() . "/wap-payment/vipcard-error";
        }
    }

    /**
     * 格式化机接口请求前的数据
     * @param array $arrParams
     * @param array $params
     */
    public function _formatParams($arrParams = [], &$params = [])
    {
        $params['payType'] = $this->getPayType($arrParams);
        $params['tradeType'] = $this->getTradeType($arrParams);
        $this->setVipCardUrl($arrParams, $params);
    }

    /**
     * 格式化机接口请求返回的数据
     * @param $response
     */
    public function _formatResponse(&$response)
    {
        $gewara_config = \wyCupboard::$config['gawara_pay_type'];
        $payType = $this->getPayType();
        if ((in_array($payType, $gewara_config) || $payType == 12)) {
            $channelId = CHANNEL_ID;
            $response = $this->sdk->call("payment/gwl-payment-link-for-vip", compact('channelId', 'response', 'payType'))['response'];
        }
    }
}
