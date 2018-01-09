<?php

namespace app\controllers;

use app\base\BaseController;
use app\services\LoginService;

class LoginController extends BaseController
{
    public function login()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['code'] = $this->getRequestParams('code');
        $params['subWxapp'] = $this->getRequestParams('subWxapp');
        $params['_client_redirect_'] = $this->getRequestParams('redirectUrl');
        if (empty($params['_client_redirect_'])) {
            $params['_client_redirect_'] = $this->getRequestParams('_client_redirect_');
        }
        $return = $this->service('Login')->login($params);
        //有跳转链接,就返回ret为302
        if (isset($return['data']['_client_redirect_'])) { //微信
            $return['data']['redirectUrl'] = $return['data']['_client_redirect_'];
            unset($return['data']['_client_redirect_']);
            $return['ret'] = $return['sub'] = 302;
        } elseif (isset($arrRes['data']['redirectUrl']) && !empty($arrRes['data']['redirectUrl'])) { //手Q
            $return['data'] = [
                'ret' => 302,
                'sub' => 302,
                'redirectUrl' => $arrRes['data']['redirectUrl']
            ];
        }
        //data为空,可能是拿code换取openId失败的情况
        if (empty($return['data'])) {
            $return = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        $this->jsonOut($return);
    }


    //APP手机号登录
    public function mobileLogin()
    {
        $return = $this->stdOut();
        //检查是否需要滑动验证如果设备号发生变动则需要滑动验证
        $channelId = CHANNEL_ID;
        $phone = $this->getRequestParams("phone");
        $password = $this->getRequestParams("password");
        $code = $this->getRequestParams("code", "");
        //第三方绑定登陆
        $openId = $this->getRequestParams("openId", "");
        $otherId = $this->getRequestParams("otherId", "");
        $unionId = $this->getRequestParams("unionId", "");
        //检查用户是否需要进行设备验证
        $params = [
            'channelId' => $channelId,
            'mobileNo' => $phone,
        ];
        $response = $this->sdk->call("user/verify-mobile-device", $params);
        if ($response['ret'] == 0 && $response['ret'] == 0) {
            $needVerify = $response['data']['devicechanged'];
        } else {
            $needVerify = 0;
        }
        //如果需要验证但是又未设置验证码
        if ($needVerify && empty($code)) {
            $return['errorcode'] = -112031;
            $this->jsonOut($return);
        }
        //验证短信是否正确
        if ($needVerify) {
            $response = $this->service("Sms")->verifySmsCode($channelId, $phone, $code);
            if (isset($response['errorcode']) && $response['errorcode'] != 0) {
                $this->jsonOut($response);
            }
        }

        //调用登陆接口获取用户信息
        $params = [
            'mobileNo' => $phone,
            'password' => $password,
            'channelId' => $channelId,
        ];
        $response = $this->sdk->call('user/login', $params);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //第三方登陆相关判断
            if ($openId && $otherId) {
                //判断是否为微信登陆如果微信登陆则要求必须有unionId
                if ($otherId = 11 && empty($unionId)) {
                    $return['errorcode'] = -111001;
                    $this->jsonOut($return);
                }
                //调用SDK绑定第三方账户以及用户数据
                $params = [
                    'mobileNo' => $phone,
                    'openId' => $openId,
                    'otherId' => $otherId,
                    'unionId' => $unionId,
                    'channelId' => $channelId,
                ];
                $response = $this->sdk->call("user/bind-mobile", $params);
                if ($response['ret'] != 0 && $response['sub'] != 0) {
                    switch ($response['sub']) {
                        case -20009:
                            $return['errorcode'] = -112027;//当前账号已被绑定过
                            break;
                        case -20008:
                            $return['errorcode'] = -112024;//手机号不存在
                            break;
                        case -20004:
                            $return['errorcode'] = -112025;//手机号码已被其他账号占用
                            break;
                        case -20003:
                            $return['errorcode'] = -112026;//手机号码已被当前账号绑定
                            break;
                        default :
                            $return['errorcode'] = -112029;//绑定手机号失败
                            break;
                    }
                    $this->jsonOut($return);
                }
            }
            //添加用户token
            $return['data'] = $this->service("User")->addUserToken($channelId, $response['data']);
            $this->service("User")->formatAvatar($return['data']);
            //登陆后优惠到人
            $this->sdk->call("task/discount",
                ['channelId' => $channelId, 'openId' => $return['data']['openId']]);
            //添加可信设备
            if ($needVerify) {
                $params = ['channelId' => $channelId, 'mobileNo' => $phone];
                $this->sdk->call('user/add-mobile-device', $params);
            }

        } else {
            if ($response['sub'] == -20008) {
                $return['errorcode'] = -112024;//手机号不存在
            } else {
                $return['errorcode'] = -112028;//用户名密码错误
            }
        }
        $this->jsonOut($return);
    }

    //app第三方用户登录
    public function thirdParty()
    {
        $return = $this->stdOut();
        $channelId = $this->getRequestParams("channelId");
        $accessToken = $this->getRequestParams("accessToken");
        $otherId = $this->getRequestParams("otherId");
        $openId = $this->getRequestParams("openId");
        $oauth_consumer_key = $this->getRequestParams("oauthConsumerKey", '');
        $unionId = $this->getRequestParams("unionId", '');

        //校验第三方账户和ACCESSTOKEN是否正确
        $verifyResponse = $this->service("AppUser")->verifyThirdPartUser($accessToken, $otherId, $openId, $oauth_consumer_key);
        if (!$verifyResponse) {
            $response['errorcode'] = -112028;
            $this->jsonOut($response);
        }
        //调用SDK进行用户注册
        $params = [
            'openId' => $openId,
            'otherId' => $otherId,
            'platForm' => I_PLATFORM,
            'nickname' => $this->getRequestParams("nickname", ''),
            'unionId' => $this->getRequestParams("unionId", ''),
            'avatar' => $this->getRequestParams("photo"),
            'channelId' => $channelId,
        ];
        $response = $this->sdk->call('user/register-by-openid', $params);

        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //获取用户信息
            $params = [
                'openId' => $openId,
                'otherId' => $otherId,
                'unionId' => $unionId,
                'channelId' => $channelId,
            ];
            $response = $this->sdk->call('user/get-userinfo-by-openid', $params);
            $registerTime = $this->sdk->call('user/get-register-time', $params);

            if ($response['ret'] == 0 && $response['sub'] == 0) {
                if (isset($response['data']['extUid'])) {
                    unset($response['data']['extUid']);
                }
                $response['data']['registTime'] = empty($registerTime['data']['registTime']) ? 0 : $registerTime['data']['registTime'];
                $userInfoByOpenId = $response['data'];
            } else {
                $userInfoByOpenId = false;
            }
            if ($userInfoByOpenId) {
                //判断是否具有手机号
                $userInfoByOpenId['hasMobile'] = (empty($userInfoByOpenId['mobileNo'])) ? false : true;
                //如果用户绑定手机号查看用户是否需要额外的短信验证【和靳松讨论一下这块的流程】


                //如果用户设置了手机号则生成token
                if (!$userInfoByOpenId['hasMobile']) {
                    $return['data'] = $this->service("User")->addUserToken($channelId, $userInfoByOpenId);
                    //激活优惠到人任务
                    $this->sdk->call("task/discount", ['channelId' => $channelId, 'openId' => $openId]);
                } else {
                    $userInfoByOpenId['token'] = "";
                    $return['data'] = $userInfoByOpenId;
                }
                $this->jsonOut($return);
            } else {
                $response['errorcode'] = -112004;
                $this->jsonOut($response);
            }
        } else {
            $response['errorcode'] = -112003;
            $this->jsonOut($response);
        }
    }
    /**
     * 微信小程序获取unionid调用用户中心open-register
     */
    public function getUnionId()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['encryptedData'] = $this->getRequestParams('encryptedData');//加密
        $params['iv'] = $this->getRequestParams('iv');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $return = $this->service('Login')->getUnionId($params);
        $this->jsonOut($return);
    }

}