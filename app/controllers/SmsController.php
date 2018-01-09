<?php

namespace app\controllers;

use app\base\BaseController;

class SmsController extends BaseController
{
    /**
     * 发送短信验证码
     *
     * @param phone_number   手机号
     *
     * @return mixed
     */
    public function sendSmsCode()
    {
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['phone'] = $this->getRequestParams('phone');    //手机号
        $params['smsToken'] = $this->getRequestParams('smsToken');  //手机号token,也就是密文手机号,这个是给优惠到人关联手机号用的
        $params['slideCredential'] = $this->getRequestParams('slideCredential');  //滑动验证成功后的密钥串
        $params['slideId'] = $this->getRequestParams('slideId');  //此次滑动验证的id
        //重新登录
        if (empty($params['openId'])&& CHANNEL_ID == 28) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($params['slideId']) || empty($params['slideCredential'])) {
            $ret = self::getErrorOut(ERRORCODE_SMS_PARAMS_SLIDE_ERROR);
        } elseif (empty($params['phone']) && empty($params['smsToken'])) {
            $ret = self::getErrorOut(ERRORCODE_SMS_PARAMS_PHONE_ERROR);
        } else {
            $ret = $this->service('Sms')->sendSmsCodeWithSlide($params);
        }
        $this->jsonOut($ret);
    }


    /**
     * 验证短信验证码
     *
     * @param phone_number   手机号
     * @param code           短信验证码
     *
     * @return mixed
     */
    public function verifySmsCode()
    {
        $params = [];
        $params['code'] = $this->getRequestParams('code');  //短信码
        $params['phone'] = $this->getRequestParams('phone');  //手机号
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($params['phone']) || empty($params['code'])) {
            $ret = self::getErrorOut(ERRORCODE_SMS_PARAMS_PHONE_CODE_ERROR);
        } else {
            $ret = $this->service('Sms')->verifySmsCode($params);
        }
        $this->jsonOut($ret);
    }

    //校验短信验证码是否正确不需要登陆
    public function verifyCode()
    {
        $return = self::getStOut();
        $channelId = $this->getRequestParams("channelId");
        $phone = $this->getRequestParams("phone");
        $code = $this->getRequestParams("code");
        //滑动验证通过后调用service发送短信验证码
        $params = [
            'channelId' => $channelId,
            'phone_number' => $phone,
            'code' => $code,
        ];
        $response = $this->sdk->call("sms/verify-sms-code", $params);
        if (!empty($response['errorcode'])) {
            $this->jsonOut($response);
        } else {
            $return['data'] = $response['result']['data'];
            $this->jsonOut($return);
        }

    }
}