<?php
/**
 * Created by PhpStorm.
 * User: zhou di
 * Date: 2017/2/23
 * Time: 18:38
 */
namespace app\services;

class WxUserService extends UserService
{
    /**
     * 获取用户昵称头像信息
     *
     * @param array $arrParams
     *
     * @return array
     * @throws \Exception
     */
    public function getUserInfo($arrParams = [])
    {
        // 尝试用公众号方法获取用户信息（用户需关注公众号）
        $arrUserInfo = $this->getUserInfoFromWeixin($arrParams['openId']);
        //如果没有nickname,说明微信禁止静默授权获取用户昵称信息了,则看是否需要强制登陆
        if (empty($arrUserInfo['nickname'])) {    // 如果没获取到用户信息
            if (!empty($arrParams['needUserInfo'])) {    // 如果前端需要用户信息，则去通过授权获取（或cookie）
                $arrUserInfo = $this->getUserInfoBySnsapi($arrParams);
                // 跳转授权页
                if (isset($arrUserInfo['data']['redirectUrl'])) {
                    return $arrUserInfo;
                }
                if (empty($arrUserInfo['nickname'])) {
                    $arrUserInfo = self::getErrorOut(ERRORCODE_USER_WECHAT_INFO_ERROR);
                    return $arrUserInfo;
                }
            }
        }
        // 获取unionid
        $unionid = isset($arrUserInfo['unionid']) ? $arrUserInfo['unionid'] : (isset($_COOKIE['unionid']) ? $_COOKIE['unionid'] : '');
        // 1. 首先调用注册接口，如果没有该用户则直接注册成功，如果有该用户，则返回该用户在接口中的信息，对比微信接口获取到的信息，如果不同，则再调用编辑接口进行编辑
        $data = [
            'openId' => $arrUserInfo['openid'],
            'unionId' => $unionid,
            'otherId' => WX_ID_TYPE_SERVICE,
            'platForm' => 1,    //1电影票，2演出。默认1
            'nickName' => self::getParam($arrUserInfo, 'nickname'),
            'photo' => self::getParam($arrUserInfo, 'headimgurl'),
            'channelId' => CHANNEL_ID,
        ];
        if (empty($data['openId']) || empty($data['unionId'])) {
            $ret = self::getErrorOut(ERRORCODE_USER_CENTER_INFO_REGISTER_ERROR);
            return $ret;
        }
        //想用户中心注册或更新信息
        $userinfoFromucenter = $this->OpenRegister($data);
        if ($userinfoFromucenter['ret'] != 0) {
            $ret = self::getErrorOut(ERRORCODE_USER_CENTER_INFO_REGISTER_ERROR);
            return $ret;
        }
        //用户中心头像昵称获取成功才返回，否则还是返回腾讯那边获取到的头像昵称
        $userinfoFromucenter = $userinfoFromucenter['data'];
        if (!isset($arrUserInfo['nickname'])) {
            $arrUserInfo['nickname'] = '';
        }
        if (!isset($arrUserInfo['headimgurl'])) {
            $arrUserInfo['headimgurl'] = '';
        }
        $arrUserInfo = [
            'nickname' => self::getParam($userinfoFromucenter, 'nickName', $arrUserInfo['nickname']),
            'headimgurl' => self::getParam($userinfoFromucenter, 'photoUrl', $arrUserInfo['headimgurl']),
            'memberId' => self::getParam($userinfoFromucenter, 'memberId'),
            //'vipInfo' => self::getParam($userinfoFromucenter, 'vipInfo'),
            'unionid' => $unionid,
        ];
        //如果昵称未取到，给默认值（这种情况应该极少）
        if (empty($arrUserInfo['nickname'])) {
            $arrUserInfo['nickname'] = '娱票儿用户';
        }
        $this->setUserInfoToCookie($arrUserInfo);
        $ret = self::getStOut();
        $ret['data'] = $arrUserInfo;

        return $ret;
    }

    /**
     * 通过openId,获取用户信息(前提是,用户关注了公众号才行),这种是静默方式
     *
     * @param string $strOpenId
     */
    public function getUserInfoFromWeixin($strOpenId = '')
    {
        $arrUserInfo = [];
        if (empty($strOpenId)) {
            return $arrUserInfo;
        }
        //获取accesstoken
        $accesstoken = $this->sdk->call('wechat/get-access-token', ['channelId' => CHANNEL_ID]);
        if (empty($accesstoken)) {
            return $arrUserInfo;
        }
        //通过accesstoken,获取用户信息
        $arrUserInfo = $this->sdk->call('wechat/get-user-info',
            ['channelId' => CHANNEL_ID, 'openid' => $strOpenId, 'access_token' => $accesstoken]);
        if (empty($arrUserInfo['openid'])) {
            $arrUserInfo = [];
        }

        return $arrUserInfo;
    }

    /**
     * 强制登陆获取用户信息,这个方法一定能获取到unionId
     *
     * @param  string code
     */
    public function getUserInfoBySnsapi($arrParams = [])
    {
        $arrUserInfo = [];
        $strScope = isset($arrParams['scope']) ? $arrParams['scope'] : 'snsapi_userinfo';
        $strCode = isset($arrParams['code']) ? $arrParams['code'] : '';
        $strRedirectUrl = self::getParam($arrParams, 'redirectUrl', '');
        //如果code为空,返回跳转链接
        if (!empty($strCode)) {
            $arrUserInfo = $this->sdk->call('wechat/get-open-id-from-we-chat',
                ['channelId' => CHANNEL_ID, 'code' => $strCode, 'scope' => $strScope]);
        } else {
            $arrRes = $this->sdk->call('wechat/get-redirect-url',
                ['channelId' => CHANNEL_ID, 'scope' => $strScope, 'redirectUrl' => $strRedirectUrl]);
            if (!empty($arrRes['data']['redirectUrl'])) {
                $arrUserInfo = $arrRes;
            }
        }

        return $arrUserInfo;
    }

    // 获取cookie中的用户信息
    public function getUserInfoFromCookie()
    {
        $arrUserInfo = [];
        if (isset($_COOKIE['WxNickname'])) {
            $arrUserInfo['nickname'] = $_COOKIE['WxNickname'];
        }
        if (isset($_COOKIE['WxHeadimg'])) {
            $arrUserInfo['headimgurl'] = $_COOKIE['WxHeadimg'];
        }
        if (isset($_COOKIE['WxMemberid'])) {
            $arrUserInfo['memberId'] = $_COOKIE['WxMemberid'];
        }
        if (isset($_COOKIE['unionid'])) {
            $arrUserInfo['unionId'] = $_COOKIE['unionid'];
        }

        return $arrUserInfo;
    }

    // 设置userinfo到cookie
    public function setUserInfoToCookie($arrUserInfo)
    {
        $arrOriginalUserInfo = $this->getUserInfoFromCookie();
        if (empty($arrUserInfo['nickname']) && !empty($arrOriginalUserInfo['nickname'])) {
            return;
        }
        if (isset($arrUserInfo['nickname'])) {
            setcookie('WxNickname', $arrUserInfo['nickname'], time() + 3600 * 24, '/', '.wepiao.com'); //设置cookie，默认保存1天
        }
        if (isset($arrUserInfo['headimgurl'])) {
            setcookie('WxHeadimg', $arrUserInfo['headimgurl'], time() + 3600 * 24, '/',
                '.wepiao.com'); //设置cookie，默认保存1天
        }
        if (isset($arrUserInfo['unionid'])) {
            setcookie('unionid', $arrUserInfo['unionid'], time() + 3600 * 24, '/', '.wepiao.com'); //设置cookie，默认保存1天
        }
        $memberId = $this->getParamMemberid($arrUserInfo);
        if (!empty($memberId)) {
            setcookie('WxMemberid', $memberId, time() + 3600 * 24, '/', '.wepiao.com'); //设置cookie，默认保存1天
        }
    }

}