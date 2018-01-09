<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/19
 * Time: 下午4:35
 */

namespace app\controllers;


use app\base\BaseController;
use app\helper\Utils;

class OrderController extends BaseController
{
    /**
     * 全支付接口
     */
    public function pay($orderId = '', $payType = '')
    {
        //参数整理
        $allow_config = \wyCupboard::$config['allow_pay_type'];
        $gewara_config = \wyCupboard::$config['gawara_pay_type'];
        $return = self::getStOut();
        $arrSendParams = [
            'openId' => $this->service('Login')->getOpenIdFromCookie(),
            'orderId' => $orderId,
            'phone' => $this->getRequestParams("phone", ''),
            'channelId' => CHANNEL_ID,
            'tradeType' => 'JSAPI',
        ];
        //payType不同渠道不一样，1:微信,2:支付宝,7:财付通,12:京东,17:银联ApplePay,其他例如格瓦拉支付传具体银行的编号
        if (!empty($payType)) {
            //app调用路由获取payType
            $arrSendParams['payType'] = $payType;
            $arrSendParams['tradeType'] = 'APP';
        } elseif ($arrSendParams['channelId'] == 3) {
            $arrSendParams['payType'] = 1;
        } elseif ($arrSendParams['channelId'] == 28) {
            $arrSendParams['payType'] = 7;
        }
        //如果京东或者格瓦拉支付则添加returnUrl
        if ($arrSendParams['payType'] == "12") {
            $arrSendParams['tradeType'] = "WAP";
        }
        if (!in_array($arrSendParams['payType'], $allow_config)) {
            $return['errorcode'] = -241001;
            $this->jsonOut($return);
        }
        //如果京东或者格瓦拉支付则添加returnUrl
        if (in_array($arrSendParams['payType'], $gewara_config)) {
            $arrSendParams['returnUrl'] = Utils::getHost() . "/wap-payment/gewara-success/{$orderId}?channelId=" . CHANNEL_ID;
        }
        //卖品
        $arrSendParams['snackId'] = $this->getRequestParams("snackId", '');
        $arrSendParams['snackNum'] = $this->getRequestParams("snackNum", '');
        if (empty($arrSendParams['snackNum'])) {
            $arrSendParams['snackId'] = '';
        }
        $arrSendParams['smsToken'] = $this->getRequestParams("smsToken", '');
        $arrSendParams['smsCode'] = $this->getRequestParams("smsCode", '');
        $arrSendParams['disInfo'] = $this->getRequestParams("disInfo", '');
        $arrSendParams['goodsInfoList'] = $this->getRequestParams("goodsInfoList", '');
        //重新登录
        if (empty($arrSendParams['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($arrSendParams['orderId'])) {
            $ret = self::getErrorOut(ERRORCODE_ORDERID_CANT_NULL);
        } elseif (empty($arrSendParams['phone'])) {
            $ret = self::getErrorOut(ERRORCODE_MOBILE_CANT_NULL);
        } else {
            $ret = $this->service('Order')->pay($arrSendParams);
        }
        if ($ret['ret'] == 0 && $ret['sub'] == 0) {
            $response = $this->sdk->call("payment/format-payment-return-params-for-app",
                ['channelId' => CHANNEL_ID, 'response' => $ret, 'payType' => $payType]);
            $ret = $response['response'];
        } elseif ($ret['ret'] == 0 && $ret['sub'] == "-10000914") {
            //卡bin验证逻辑
            $response = $this->sdk->call("payment/format-payment-cardbin",
                ['arrSendParams' => $arrSendParams, 'response' => $ret]);
            $ret = $response['response'];
        }
        //按照APP的方式针对京东以及格瓦拉的返回结果进行格式化
        $this->jsonOut($ret);
    }

    /**
     * 全支付接口, 支持改签
     */
    public function payV2($orderId = '', $payType = '')
    {
        //参数整理
        $arrSendParams = [];
        $arrSendParams['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $arrSendParams['orderId'] = $orderId;
        $arrSendParams['phone'] = $this->getRequestParams("phone", '');
        $arrSendParams['channelId'] = CHANNEL_ID;
        if (in_array($arrSendParams['channelId'], [63, 66, 67, 68, 86])) {
            $arrSendParams['tradeType'] = 'SmallJSAPI';
        } elseif (in_array($arrSendParams['channelId'], [8, 9])) {
            $arrSendParams['tradeType'] = 'APP';
        } else {
            $arrSendParams['tradeType'] = 'JSAPI';
        }
        //payType不同渠道不一样，1:微信 微信小程序,2:支付宝,7:财付通,12:京东,17:银联ApplePay,其他例如格瓦拉支付传具体银行的编号
        if ($arrSendParams['channelId'] == 28) {
            $arrSendParams['payType'] = 7;
        } else {
            $arrSendParams['payType'] = 1;
        }
        if (!empty($payType)) {
            //app调用路由获取payType
            $arrSendParams['payType'] = $payType;
            $arrSendParams['tradeType'] = 'APP';
        } elseif ($arrSendParams['channelId'] == 28) {
            $arrSendParams['payType'] = 7;
        } else {
            $arrSendParams['payType'] = 1;
        }
        //卖品
        $arrSendParams['snackId'] = $this->getRequestParams("snackId", '');
        $arrSendParams['snackNum'] = $this->getRequestParams("snackNum", '');
        if (empty($arrSendParams['snackNum'])) {
            $arrSendParams['snackId'] = '';
        }
        $arrSendParams['smsToken'] = $this->getRequestParams("smsToken", '');
        $arrSendParams['smsCode'] = $this->getRequestParams("smsCode", '');
        $arrSendParams['disInfo'] = $this->getRequestParams("disInfo", '');
        $arrSendParams['goodsInfoList'] = $this->getRequestParams("goodsInfoList", '');
        //重新登录
        if (empty($arrSendParams['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($arrSendParams['orderId'])) {
            $ret = self::getErrorOut(ERRORCODE_ORDERID_CANT_NULL);
        } elseif (empty($arrSendParams['phone'])) {
            $ret = self::getErrorOut(ERRORCODE_MOBILE_CANT_NULL);
        } else {
            $ret = $this->service('Order')->payV2($arrSendParams);
        }
        if ($ret['ret'] == 0 && $ret['sub'] == 0) {
            $response = $this->sdk->call("payment/format-payment-return-params-for-app",
                ['channelId' => CHANNEL_ID, 'response' => $ret, 'payType' => $payType]);
            $ret = $response['response'];
        } elseif ($ret['ret'] == 0 && $ret['sub'] == "-10000914") {
            //卡bin验证逻辑
            $response = $this->sdk->call("payment/format-payment-cardbin",
                ['arrSendParams' => $arrSendParams, 'response' => $ret]);
            $ret = $response['response'];
        }
        $this->jsonOut($ret);
    }

    /**
     * 格瓦拉专属支付
     */
    public function gwlpay($payType = '')
    {
        //参数整理
        $arrSendParams = [];
        $orderId = $arrSendParams['orderId'] = $this->getRequestParams("orderId", '');
        $arrSendParams['payType'] = $payType;
        $arrSendParams['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $arrSendParams['phone'] = $this->getRequestParams("phone", '');
        $arrSendParams['channelId'] = CHANNEL_ID;
        $arrSendParams['tradeType'] = 'WAP';
        $appPayType = [1, 2, 7, 17];
        if (in_array($payType, $appPayType)) {
            $arrSendParams['tradeType'] = 'APP';
        }
        //卖品
        $arrSendParams['snackId'] = $this->getRequestParams("snackId", '');
        $arrSendParams['snackNum'] = $this->getRequestParams("snackNum", '');
        if (empty($arrSendParams['snackNum'])) {
            $arrSendParams['snackId'] = '';
        }
        $arrSendParams['smsToken'] = $this->getRequestParams("smsToken", '');
        $arrSendParams['smsCode'] = $this->getRequestParams("smsCode", '');
        $arrSendParams['version'] = $this->getRequestParams("appver", '');
        $arrSendParams['goodsInfoList'] = $this->getRequestParams("goodsInfoList", '');
        //格瓦拉专属参数
        $arrSendParams['reqSource'] = $this->getRequestParams("reqSource", '');
        $arrSendParams['merchantCode'] = $this->getRequestParams("merchantCode", '');
        $arrSendParams['bankCode'] = $this->getRequestParams("bankCode", '');
        $arrSendParams['gatewayCode'] = $this->getRequestParams("gatewayCode", '');
        //支付宝专有
        $arrSendParams['showUrl'] = $this->getRequestParams("showUrl", '');
        //京东支付v2,加参数
        if (strcmp($payType, 12) === 0) {
            $arrSendParams['interfaceVersion'] = 'JdV2';
        }
        //如果京东或者格瓦拉支付则添加returnUrl
        if (!in_array($arrSendParams['payType'], $appPayType)) {
            $arrSendParams['returnUrl'] = Utils::getHost() . "/wap-payment/gewara-success/{$orderId}?channelId=" . CHANNEL_ID;
        }
        $arrSendParams['isCardBin'] = $this->getRequestParams("isCardBin", '');
        //用户权益
        $arrSendParams['rightsId'] = $this->getRequestParams("rightsId", '');
        $arrSendParams['goodsId'] = $this->getRequestParams("goodsId", '');
        $arrSendParams['goodsNum'] = $this->getRequestParams("goodsNum", '');
        //重新登录
        if (empty($arrSendParams['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($arrSendParams['orderId'])) {
            $ret = self::getErrorOut(ERRORCODE_ORDERID_CANT_NULL);
        } elseif (empty($arrSendParams['phone'])) {
            $ret = self::getErrorOut(ERRORCODE_MOBILE_CANT_NULL);
        } else {
            //disInfo处理
            $this->fromatDisInfo($arrSendParams);
            if (!empty($arrSendParams['isCardBin'])) {
                unset($arrSendParams['isCardBin']);
                $tmp = [];
                $response = $this->sdk->call("payment/format-payment-cardbin",
                    ['payParams' => $arrSendParams, 'response' => $tmp, 'channelId' => $arrSendParams['channelId'],]);
                $ret = $response['response'];
                $this->jsonOut($response);

            } else {
                $ret = $this->service('Order')->payV3($arrSendParams);
            }
        }
        if ($ret['ret'] == 0 && $ret['sub'] == 0) {
            $response = $this->sdk->call("payment/format-payment-return-params-for-app",
                ['channelId' => CHANNEL_ID, 'response' => $ret, 'payType' => $payType]);
            $ret = $response['response'];
        } elseif ($ret['ret'] == 0 && $ret['sub'] == "-10000914") {
            //卡bin验证逻辑
            $response = $this->sdk->call("payment/format-payment-cardbin",
                ['payParams' => $arrSendParams, 'response' => $ret, 'channelId' => $arrSendParams['channelId'],]);
            $ret = $response['response'];
        }
        $this->jsonOut($ret);
    }

    public function fromatDisInfo(&$arrParams)
    {
        $disInfo = $this->getRequestParams("disInfo", '');
        if (!empty($disInfo)) {
            $disInfoArr = json_decode($disInfo, 1);
            $disInfoArr['version'] = !empty($arrParams['version']) ? $arrParams['version'] : '';
            if (CHANNEL_ID == 80) {
                $disInfoArr['devId'] = $this->getRequestParams("idfa", '');
            } else {
                if (CHANNEL_ID == 84) {
                    $imei = $this->getRequestParams("imei", '');
                    $devId = $this->getRequestParams("deviceid", '');
                    $disInfoArr['devId'] = !empty($imei) ? $imei : (!empty($devId) ? $devId : '');
                }
            }
            $arrParams['disInfo'] = json_encode($disInfoArr);
        }
    }

    //获取订单列表已支付
    public function getOrderList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['types'] = $this->getRequestParams('types', '2,4,24');
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Order')->getOrderList($params);
        }
        if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            $return = $this->getStOut();
            $Paid = [
                'orderList' => [],
                'total_row' => 0,
                'page' => 1,
                'num' => 0,
            ];
            $Paid['orderList'] = [];
            if ($ret['ret'] == 0 && $ret['sub'] == 0) {
                $Paid['orderList'] = $ret['data']['orderList'] ? $ret['data']['orderList'] : [];
                $Paid['total_row'] = $ret['total_row'];
                $Paid['page'] = $ret['page'];
                $Paid['num'] = $ret['num'];
                $ret = $this->service('Order')->_formatOrderList(CHANNEL_ID, $Paid['orderList']);
            }
            $return['data'] = $Paid;
            $ret = $return;
        }
        $this->jsonOut($ret);
    }

    //获取订单列表【V2版本,支持改签】
    public function getOrderListV2()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['expireFlag'] = $this->getRequestParams('expireFlag', '');
        $params['types'] = $this->getRequestParams('types', '2,4,24');
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Order')->getOrderListV2($params);
        }
        $this->jsonOut($ret);
    }

    //获取订单详情
    public function getOrderDetail($orderId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['salePlatformType'] = $this->getRequestParams('salePlatformType', SALE_PLATFORM_TYPE);
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['orderId'] = $orderId;
        $params['payStatus'] = $this->getRequestParams('payStatus');//只在支付成功页会传入
        $params['yupiaoRefundShow'] = $this->getRequestParams('yupiaoRefundShow', 'true');//判断娱票儿会员退票文案展示
        $params['movieId'] = $this->getRequestParams('movieId'); //只在支付成功页会传入，阵营购买计数使用，影片ID
        $params['campName'] = $this->getRequestParams('campName'); //只在支付成功页会传入，阵营购买计数使用，阵营名
        $params['seatNum'] = $this->getRequestParams('seatNum'); //只在支付成功页会传入，阵营购买计数使用，座位数
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Order')->queryOrderInfo($params);
        }
        $this->jsonOut($ret);
    }

    //获取订单详情V2版
    public function getOrderDetailV2($orderId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['orderId'] = $orderId;
        $params['payStatus'] = $this->getRequestParams('payStatus', 0);
        $params['yupiaoRefundShow'] = $this->getRequestParams('yupiaoRefundShow', 'true');//判断娱票儿会员退票文案展示
        $params['realChannelId']='';
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $params['realOpenId'] = $this->getRequestParams('openId', '');//传递过来的openId作为真实的订单openId
            if(!empty($params['realOpenId']) && !empty($orderId)){
                $params['realChannelId']=intval(substr($orderId, -2));//订单号后两位取整作为真实渠道
            }
            $ret = $this->service('Order')->queryOrderInfoV2($params);
        }
        $this->jsonOut($ret);
    }

    //删除订单方法

    public function deleteOrder($orderId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['orderId'] = $orderId;
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = \wyCupboard::$sdk->call("order/del-order", $params);
        }
        $this->jsonOut($ret);
    }

    //订单退款原因
    public function refundOrderReason($orderId)
    {
        $return = self::getStOut();
        $output = [
            ["id" => 1, "reason" => "计划有变，看不了了"],
            ["id" => 2, "reason" => "买错影院、场次、座位"],
            ["id" => 3, "reason" => "就不告诉你"],
        ];
        $return['data'] = $output;
        $this->jsonOut($return);
    }

    //订单退款
    public function refundOrder($orderId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['orderId'] = $orderId;
        $params['salePlatformType'] = $this->getRequestParams('salePlatformType', SALE_PLATFORM_TYPE);
        $params['refundReason'] = $this->getRequestParams('refundReason');
        if (empty($params['openId'])) {
            $data = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $data = \wyCupboard::$sdk->call('order/refund', $params);
            if ($data['ret'] == "50401" && $data['sub'] == "50401") {
                $data['msg'] = "系统超时,请稍后刷新订单查看退票状态";
            }
        }
        $this->jsonOut($data);
    }

    public function unpaidOrderV2()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $this->getRequestParams('cityId', 10);
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Order')->queryUnpaidOrderV2($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 从订单中心获取用户手机号
     */
    public function getOrderMobile()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['salePlatformType'] = $this->getRequestParams('salePlatformType', SALE_PLATFORM_TYPE);
        $params['appId'] = WX_MOVIE_APP_ID;

        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Order')->getOrderMobile($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * iOS Passbook
     * @param $orderId
     */
    public function passbook($orderId)
    {
        $channelId = $this->getRequestParams("channelId");
        $params = [
            'channelId' => $channelId,
            'openId' => $this->service('Login')->getOpenIdFromCookie(),
            'orderId' => $orderId,
        ];
        $data = $this->sdk->call("order/query-order-info-new", $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            $this->service('Order')->appleWallet($data['data']);
        }

    }
}
