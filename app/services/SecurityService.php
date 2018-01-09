<?php

namespace app\services;

use app\base\BaseService;

class SecurityService extends BaseService
{
    /**
     * 生成图片验证码
     * 此方法,必须结合滑动验证使用
     *
     * @param $channelId   渠道
     * @param $id   用户的openId
     *
     * @return mixed
     */
    public function generatePictureVerifyCode($arrInput)
    {
        //定义发送错误
        $this->sdk->call('verify-code/create-verify-code', $arrInput);
        die;
    }

    /**
     * 验证图片验证码是否正确
     * @param $input
     */
    public function verifyPictureCode($arrInput)
    {
        $checkResult = $this->sdk->call("verify-code/check-code", $arrInput);
        return $checkResult;
    }


    public function clearVerifyCodeDate($arrInput)
    {
        $ret = $this->sdk->call("verify-code/remove-code-data", $arrInput);
        return $ret;
    }
}