<?php
/**
 * Created by PhpStorm.
 * User: tanjunlin
 * Date: 2016/7/14
 * Time: 17:42
 */

namespace app\controllers;

use app\base\BaseController;
use app\helper\Utils;

class PaymentController extends BaseController
{
    /**
     * 退款原因接口由于电影票的退款原因是写死的
     * 但是都希望可以通过服务端返回
     */
    public function refundReason()
    {
        $output = [
            ["id" => 1, "reason" => "计划有变，看不了了"],
            ["id" => 2, "reason" => "买错影院、场次、座位"],
            ["id" => 3, "reason" => "就不告诉你"],
        ];
        $this->jsonOut(['ret' => 0, 'sub' => 0, 'data' => $output]);
    }

    /**
     * H5 APP快捷跳转 京东支付
     * @param $token
     */
    public function easyPaymentJd($token)
    {
        //通过token获取参数
        $channelId = $this->getRequestParams("channelId", "9");
        $JdPayParams = $this->sdk->call("payment/get-easy-payment-token", compact('channelId', 'token'));
        if (!$JdPayParams) {
            echo "<h1>Invalid Token !</h1>";
        } else {
            if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
                $PayParams = json_decode($JdPayParams, true);
                $httpMethod = isset($PayParams['httpMethod']) ? $PayParams['httpMethod'] : 'post';
                $payUrl = isset($PayParams['payUrl']) ? $PayParams['payUrl'] : '';
                $payParams = isset($PayParams['payParams']) ? $PayParams['payParams'] : [];
                echo Utils::render("payjd2", compact('httpMethod', 'payUrl', 'payParams', 'memberEncode'));
            } else {
                echo Utils::render("payjd", ['JdPayParams' => $JdPayParams]);
            }
        }
    }

    /**
     * H5 APP快捷跳转 格瓦拉支付
     * @param $token
     */
    public function easyPaymentGewara($token)
    {
        //通过token获取参数
        $channelId = $this->getRequestParams("channelId", "9");
        $PayParams = $this->sdk->call("payment/get-easy-payment-token", compact('channelId', 'token'));
        if (!$PayParams) {
            echo "<h1>Invalid Token !</h1>";
        } else if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            //格瓦拉登录态字段
            $memberEncode = $this->getRequestParams("memberEncode", '');
            $PayParams = json_decode($PayParams, true);
            $httpMethod = isset($PayParams['httpMethod']) ? $PayParams['httpMethod'] : 'post';
            $payUrl = isset($PayParams['payUrl']) ? $PayParams['payUrl'] : '';
            $payParams = isset($PayParams['payParams']) ? $PayParams['payParams'] : [];
            echo Utils::render("newpaygewara", compact('httpMethod', 'payUrl', 'payParams','memberEncode'));
            die();
        } else {
            echo Utils::render("paygewara", ['PayParams' => $PayParams]);
        }
    }

    //卡bin验证
    public function GewaraCardNo($token)
    {
        $data = [];
        $data['token'] = $token;
        echo Utils::render("cardno", $data);
    }

    //格瓦拉卡bin支付
    public function payBin($token)
    {
        $token = htmlspecialchars($token);
        $channelId = $this->getRequestParams("channelId", 9);
        $bankCardNo = $this->getRequestParams("bankCardNo");
        $return = $this->sdk->call("payment/bin-payment-token", compact('channelId', 'token', 'bankCardNo'));
        $this->jsonOut($return);
    }

    private function GetAppUri()
    {
        $uri = 'wxmovie';
        if (in_array(CHANNEL_ID, [84, 80])) {
            $uri = 'gewara';
        }
        return $uri;
    }

    //格瓦卡成功回调
    public function GewaraSuccess($orderId)
    {
        if (isset($orderId)) {
            echo "<script> window.location.href='{$this->GetAppUri()}://paysuc?orderId={$orderId}'</script>";
        } else {
            echo "<script> window.location.href='{$this->GetAppUri()}.://payfail'</script>";
        }
    }

    //格瓦拉支付列表
    public function gewaraList()
    {
        $output = \wyCupboard::$config['gawara_pay_type'];
        $this->jsonOut(['ret' => 0, 'sub' => 0, 'msg' => "success", 'data' => $output]);
    }

    //获取格瓦拉支付点卡信息
    public function getPointCardInfo($cardId)
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['mobile'] = $this->getRequestParams("mobile", '');
        $params['cardPass'] = $cardId;
        $params['orderId'] = $this->getRequestParams("orderId");
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['subChannelId'] = APP_ID;
        $return = $this->sdk->call('card/get-point-card-info', $params);
        $this->jsonOut($return);
    }


    public function payAllTypeV2($orderId, $payType)
    {
        //判断当前支付是否在支付允许列表里
        $allow_config = \wyCupboard::$config['allow_pay_type'];
        $gewara_config = \wyCupboard::$config['gawara_pay_type'];
        $return = $this->stdOut();
        if (!in_array($payType, $allow_config)) {
            $return['errorcode'] = -241001;
            $this->jsonOut($return);
        }
        $channelId = $this->getRequestParams("channelId");
        //定义支付的公共参数
        $params = [
            'channelId' => $channelId,
            'openId' => $this->service('Login')->getOpenIdFromCookie(),
            'orderId' => $orderId,
            'phone' => $this->getRequestParams("phone"),
            'payType' => $payType,
            'tradeType' => "APP",
            'snackId' => $this->getRequestParams("snackId"),
            'snackNum' => $this->getRequestParams("snackNum"),
            'smsCode' => $this->getRequestParams("smsCode"),
            'smsToken' => $this->getRequestParams("smsToken"),
            'disInfo' => $this->getRequestParams("disInfo"),
            'goodsInfoList' => $this->getRequestParams("digoodsInfoListsInfo"),
        ];
        //如果京东或者格瓦拉支付则添加returnUrl
        if ($payType == "12") {
            $params['tradeType'] = "WAP";
        }
        if (in_array($payType, $gewara_config)) {
            $params['returnUrl'] = Utils::getHost() . "/wap-payment/gewara-success/{$orderId}?channelId=".CHANNEL_ID;
        }
        $response = $this->sdk->call("pay/pay", $params);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $ret = $this->sdk->call("payment/format-payment-return-params-for-app",
                compact('channelId', 'response', 'payType'));
            $response = $ret['response'];
        } elseif ($response['ret'] == 0 && $response['sub'] == "-10000914") {
            //卡bin验证逻辑
            $ret = $this->sdk->call("payment/format-payment-cardbin", compact('params', 'response'));
            $response = $ret['response'];
        }
        //按照APP的方式针对京东以及格瓦拉的返回结果进行格式化
        $this->jsonOut($response);
    }

    //VIP折扣卡卡格瓦拉支付
    public function easyPaymentVipGewara($token)
    {
        //通过token获取参数
        $channelId = $this->getRequestParams("channelId", "9");
        $PayParams = $this->sdk->call("payment/get-easy-payment-token", compact('channelId', 'token'));
        if (!$PayParams) {
            echo "<h1>Invalid Token !</h1>";
        } else if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $PayParams = json_decode($PayParams, true);
            $httpMethod = isset($PayParams['httpMethod']) ? $PayParams['httpMethod'] : 'post';
            $payUrl = isset($PayParams['payUrl']) ? $PayParams['payUrl'] : '';
            $payParams = isset($PayParams['payParams']) ? $PayParams['payParams'] : [];
            $memberEncode = $this->getRequestParams("memberEncode", '');
            echo Utils::render("newpayvipgewara", compact('httpMethod', 'payUrl', 'payParams','memberEncode'));
            die();
        } else {
            echo Utils::render("payvipgewara", ['PayParams' => $PayParams]);
        }
    }

    //VIP折扣卡卡格瓦拉支付
    public function easyPaymentVipJd($token)
    {
        //通过token获取参数
        $channelId = $this->getRequestParams("channelId", "9");
        $PayParams = $this->sdk->call("payment/get-easy-payment-token", compact('channelId', 'token'));
        if (!$PayParams) {
            echo "<h1>Invalid Token !</h1>";
        } else {
            if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
                $PayParams = json_decode($PayParams, true);
                $httpMethod = isset($PayParams['httpMethod']) ? $PayParams['httpMethod'] : 'post';
                $payUrl = isset($PayParams['payUrl']) ? $PayParams['payUrl'] : '';
                $payParams = isset($PayParams['payParams']) ? $PayParams['payParams'] : [];
                echo Utils::render("payjd2", compact('httpMethod', 'payUrl', 'payParams', 'memberEncode'));
            } else {
                echo Utils::render("payvipjd", ['PayParams' => $PayParams]);
            }
        }
    }

    //VIP折扣卡格瓦拉支付成功回调
    public function GewaraVipSuccess($type)
    {
        echo "<script> window.location.href='{$this->GetAppUri()}://discountCardPayResult'</script>";
    }

    //VIP折扣卡格瓦拉支付成功回调
    public function GewaraVipError($type)
    {
        echo "<script> window.location.href='{$this->GetAppUri()}://discountCardPayResultFail'</script>";
    }

    //卡bin验证
    public function bankcard()
    {
        if (!isset($_GET['token']) || empty($_GET['token'])) {
            exit("token Required!");
        }
        $params = [
            'channelId' => $_GET['channelId'],
            'orderId' => $_GET['orderId'],
            'token' => htmlspecialchars($_GET['token']),
        ];

        $cardNo = $this->sdk->call('payment/get-bin-payment-order-card-info', $params);
        if ($cardNo) {
            $params['cardNo'] = $cardNo;
        }
        echo Utils::render("cardno", $params);
    }

    /**
     * 获取格瓦拉可用支付方式
     */
    public function gwlPayMethods()
    {
        $arrSendParams['businessId'] = $this->getRequestParams("businessId");
        $arrSendParams['channelId'] = CHANNEL_ID;
        $arrSendParams['version'] = $this->getRequestParams("version");
        $payMethods = $this->sdk->call("pay/gewara-pay-methods", $arrSendParams);
        if ($payMethods['ret'] == 0) {
            foreach ($payMethods['data'] as $key => &$value) {
                if (empty($value['payIcon'])) {
                    $value['payIcon'] = new \stdClass();
                }
            }
        }
        $this->jsonOut($payMethods);
    }
}