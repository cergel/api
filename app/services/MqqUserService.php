<?php
/**
 * Created by PhpStorm.
 * User: zhou di
 * Date: 2017/2/23
 * Time: 18:38
 */
namespace app\services;

class MqqUserService extends UserService
{
    /**
     * 获取用户昵称头像信息
     * @param array $arrParams
     * @return array
     * @throws \Exception
     */
    public function getUserInfo($arrParams = [])
    {
        $return = $this->getStOut();
        $accessToken = $this->sdk->call('mqq/get-mqq-user-token',
            ['channelId' => $arrParams['channelId'], 'openId' => $arrParams['openId']]);//获取access_token
        if (empty($accessToken)) {
            return $this->getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        //调用腾讯接口获取用户手Q昵称与头像
        $userinfo = $this->sdk->call("mqq/get-userinfo-by-accessToken", [
            'access_token' => $accessToken,
            'channelId' => $arrParams['channelId'],
            'openid' => $arrParams['openId']
        ]);
        $userinfo['data'] = ($userinfo['ret'] == 0) ? $userinfo['data'] : [];
        $arrUserInfo = [
            'nickname' => $this->getParam($userinfo['data'], 'MqqNickname'),
            'headimgurl' => $this->getParam($userinfo['data'], 'MqqHeadimg'),
        ];
        //首先调用注册接口，如果没有该用户则直接注册成功，如果有该用户，则返回该用户在接口中的信息，并更新对应openid信息
        $data = [
            'openId' => $arrParams['openId'],
            'otherId' => WX_ID_TYPE_SERVICE, //手Q用户中心渠道
            'platForm' => 1,    //1电影票，2演出。默认1
            'nickName' => $arrUserInfo['nickname'],
            'photo' => $arrUserInfo['headimgurl'],
            'channelId' => $arrParams['channelId'],
        ];
        $userinfoFromucenter = $this->OpenRegister($data);
        if (!isset($userinfoFromucenter['ret']) || $userinfoFromucenter['ret'] != 0 || !isset($userinfoFromucenter['data'])) {
            return $this->getErrorOut(ERRORCODE_REG_USER_FAILED);
        }
        //用户中心头像昵称获取成功才返回，否则还是返回腾讯那边获取到的头像昵称
        $userinfoFromucenter = $userinfoFromucenter['data'];
        $arrUserInfo = [
            'nickname' => $this->getParam($userinfoFromucenter, 'nickName', $arrUserInfo['nickname']),
            'headimgurl' => $this->getParam($userinfoFromucenter, 'photoUrl', $arrUserInfo['headimgurl']),
            'memberId' => $this->getParam($userinfoFromucenter, 'memberId'),
            //'vipInfo' => $this->getParam($userinfoFromucenter, 'vipInfo'),
        ];
        //如果昵称未取到，给默认值（这种情况应该极少）
        if (empty($arrUserInfo['nickname'])) {
            $arrUserInfo['nickname'] = '娱票儿用户';
        }
        $this->setUserInfoToCookie($arrUserInfo);
        $return['data'] = $arrUserInfo;
        $this->jsonOut($return);
    }

    // 获取cookie中的用户信息
    public function getUserInfoFromCookie()
    {
        $arrUserInfo = [];
        if (isset($_COOKIE['MqqNickname'])) {
            $arrUserInfo['nickname'] = $_COOKIE['MqqNickname'];
        }
        if (isset($_COOKIE['MqqHeadimg'])) {
            $arrUserInfo['headimgurl'] = $_COOKIE['MqqHeadimg'];
        }
        if (isset($_COOKIE['MqqMemberid'])) {
            $arrUserInfo['memberId'] = $_COOKIE['MqqMemberid'];
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
            setcookie('MqqNickname', $arrUserInfo['nickname'], time() + 3600 * 24, '/',
                '.wepiao.com'); //设置cookie，默认保存1天
        }
        if (isset($arrUserInfo['headimgurl'])) {
            setcookie('MqqHeadimg', $arrUserInfo['headimgurl'], time() + 3600 * 24, '/',
                '.wepiao.com'); //设置cookie，默认保存1天
        }
        $memberId = $this->getParamMemberid($arrUserInfo);
        if (!empty($memberId)) {
            setcookie('MqqMemberid', $memberId, time() + 3600 * 24, '/', '.wepiao.com'); //设置cookie，默认保存1天
        }
    }
}