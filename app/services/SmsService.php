<?php

namespace app\services;

use app\base\BaseService;

class SmsService extends BaseService
{
    /**
     * 发送短信验证码
     * 此方法,必须结合滑动验证使用
     *
     * @param $mobile   手机号
     *
     * @return mixed
     */
    public function sendSmsCodeWithSlide($arrInput)
    {
        //定义发送错误
        $ret = self::getStOut();

        //参数校验
        if (empty($arrInput['openId']) || empty($arrInput['slideCredential']) || empty($arrInput['slideId']) || (empty($arrInput['phone']) && empty($arrInput['smsToken']))) {
            $ret = self::getErrorOut(ERRORCODE_SMS_SEND_ERROR);
            return $ret;
        }
        //滑动认证信息验证
        $validateRes = $this->slideCheck($arrInput);
        if (!$validateRes) {
            $ret = self::getErrorOut(ERRORCODE_SMS_SLIDE_CHECK_ERROR);
            return $ret;
        }
        //如果有smsToken,从token中解密出手机号
        if (!empty($arrInput['smsToken'])) {
            $strPhoneNumber = $this->decryptPhone(['token' => $arrInput['smsToken']]);
            if (empty($strPhoneNumber) || !is_numeric($strPhoneNumber)) {
                $ret = self::getErrorOut(ERRORCODE_SMS_SLIDE_CHECK_ERROR);
                return $ret;
            }
            $arrInput['phone'] = $strPhoneNumber;
        }
        //发送短息
        $ret = $this->sendSmsCode($arrInput);
        return $ret;
    }

    /**
     * 发送短息
     * 此方法,只能被sendSmsCodeWithSlide调用
     *
     * @param $mobile   手机号
     * @param $code     短信验证码
     *
     * @return mixed
     */
    public function sendSmsCode($arrInput)
    {
        //参数处理
        $data = [
            'phone_number' => self::getParam($arrInput, 'phone'),
            'publicSignalShort' => CHANNEL_ID,  //渠道ID
            'channelId' => CHANNEL_ID,  //渠道ID
        ];
        $response = $this->sdk->call('sms/send-sms-code', $data);
        if (isset($response['errorcode']) && ($response['errorcode'] == 0)) {
            $response = $response['result'];
            $response['data'] = new \stdClass();
        }

        //将errorcode转换为ret形式，service修改为直接返回ret后可去掉该行
        $response = $this->convErrorCode($response);


        return $response;
    }

    /**
     * 验证短信验证码
     *
     * @param $mobile   手机号
     * @param $code     短信验证码
     *
     * @return mixed
     */
    public function verifySmsCode($arrInput)
    {
        $ret = self::getStOut();
        $ret['data'] = new \stdClass();
        //参数处理
        $data = [
            'phone_number' => self::getParam($arrInput, 'phone'),
            'code' => self::getParam($arrInput, 'code'),
            'publicSignalShort' => CHANNEL_ID,  //渠道ID
            'channelId' => CHANNEL_ID,  //渠道ID
        ];
        $response = $this->sdk->call('sms/verify-sms-code', $data);
        if (!isset($response['errorcode']) || ($response['errorcode'] != 0)) {
            $ret = self::getErrorOut(ERRORCODE_SMS_VALID_ERROR);
        }

        return $ret;
    }

    /**
     * 从token中解密出手机号
     *
     * @param token         令牌信息
     *
     * @return mixed
     */
    public function decryptPhone($arrInput)
    {
        $return = '';
        //参数处理
        $data = [
            'token' => self::getParam($arrInput, 'token'),
            'channelId' => CHANNEL_ID,  //渠道ID
        ];
        $response = $this->sdk->call('sms/decrypt-phone', $data);
        if (!empty($response) && isset($response['ret']) && ($response['ret'] == 0) && !empty($response['data']['phone'])) {
            $return = $response['data']['phone'];
        }
        return $return;
    }

    /**
     * 滑动验证
     */
    public function slideCheck($arrInput)
    {
        $return = false;
        //参数处理
        $data = [
            'slideCredential' => self::getParam($arrInput, 'slideCredential'),
            'slideId' => self::getParam($arrInput, 'slideId'),
            'channelId' => CHANNEL_ID,  //渠道ID
        ];
        if (empty($data['slideCredential']) || empty($data['slideId'])) {
            return $return;
        }
        //service调用
        $response = $this->sdk->call('slide-verify/check-credential', $data);
        if (isset($response['ret']) && ($response['ret'] == 0)) {
            $return = true;
        }
        return $return;
    }


    /**
     * 对旧版的短信service返回值做处理，将errorcode形式转换为ret形式
     * @param $arrInput
     * @return array
     */
    public function convErrorCode($arrInput)
    {
        if (isset($arrInput['errorcode']) && $arrInput['errorcode'] != 0) {
            $arrErrCode = [
                '-191001' => '系统异常，验证失败',
                '-191002' => '查找手机号验证信息失败',
                '-191003' => '验证码错误，请重新输入',
                '-191004' => '验证码已过期，请重试',
                '-191005' => '发送验证码过于频繁,请稍后再试',
                '-191006' => '验证已失效,请重新获取验证码',
                '-191007' => '图形验证码错误',
                '-191008' => '请输入图形验证码',
                '-191009' => '图形验证码配置文件获取失败',
            ];
            $msg = !empty($arrErrCode[$arrInput['errorcode']]) ? $arrErrCode[$arrInput['errorcode']] : '';
            $arrInput = [
                'ret' => '19',
                'sub' => $arrInput['errorcode'],
                'msg' => $msg,
            ];
        }
        return $arrInput;
    }
}