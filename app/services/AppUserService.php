<?php
/**
 * Created by PhpStorm.
 * User: zhou di
 * Date: 2017/2/23
 * Time: 18:38
 */
namespace app\services;

class AppUserService extends UserService
{
    public function getUserInfoByPhone($channelId, $phone)
    {
        $params = [
            'channelId' => $channelId,
            'mobileNo' => $phone,
        ];
        $userInfo = $this->sdk->call('user/get-userinfo-by-mobile', $params);

        $verifyDevice = $this->sdk->call('user/verify-mobile-device', $params);
        if ($verifyDevice['ret'] == 0 && $verifyDevice['sub'] == 0) {
            $needverify = $verifyDevice['data']['devicechanged'];
        } else {
            $needverify = false;
        }
        //
        if ($userInfo['ret'] == 0 && $userInfo['sub'] == 0) {
            if (isset($response['data']['extUid'])) {
                unset($response['data']['extUid']);
            }
            $userInfo['data']['devicechanged'] = (int)$needverify;
            $this->formatAvatar($userInfo['data']);
        }
        return $userInfo;
    }

    /**
     * 格式化用户头像
     * @param $data
     */
    public function formatAvatar(&$data)
    {
        $PHOTO_DOMAIN = 'https://appnfs.wepiao.com';
        $PHOTO_DEFAULT = 'https://appnfs.wepiao.com/dataImage/photo.png';
        $AVATAR_NAMES = ['photo', 'photoUrl'];
        foreach ($AVATAR_NAMES as $k) {
            if (!empty($data[$k])) {
                if (!stristr($data[$k], 'http')) {
                    $data[$k] = $PHOTO_DOMAIN . $data[$k];
                } else {
                    $data[$k] = str_replace("http://", "https://", $data[$k]);
                }
            } elseif (isset($data[$k]) && is_array($data)) {
                $data[$k] = $PHOTO_DEFAULT;
            }
        }
    }


    /**
     * 获取APP用户的个人资料
     * @param $params
     */
    public function getProfile($params)
    {
        $response = $this->sdk->call('user/get-user-profile-by-uid', $params);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            if (isset($response['data']['extUid'])) {
                unset($response['data']['extUid']);
            }
            //获取用户的未读消息数
            $message = $this->sdk->call("message/get-message-list", $params);
            //获取用户最早的ID注册时间
            $registerTime = $this->sdk->call('user/get-register-time', $params);

            $unread = 0;
            if ($message['ret'] == 0 and $message['sub'] == 0) {
                foreach ($message['data'] as $msgtype) {
                    $unread += $msgtype['unread'];
                }
            }
            //获取用户标签
            $tagResponse = $this->sdk->call('user/get-user-tag', $params);
            if (!empty($tagResponse['data'])) {
                $response['data']['authType']['summary'] = $tagResponse['data']['summary'];
                $response['data']['authType']['is_star'] = $tagResponse['data']['is_star'];
            } else {
                $response['data']['authType'] = new \stdClass();
            }
            //未读消息数
            $response['data']['registTime'] = empty($registerTime['data']['registTime']) ? time() : $registerTime['data']['registTime'];
            $response['data']['unread'] = $unread;
            //头像处理https

            $response['data']['photoUrl'] = str_replace("http://", "https://", $response['data']['photoUrl']);

        }
        return $response;
    }

    /**
     * 校验用户第三方的ACCESSTOKEN是否正确
     * @param $accessToken
     * @param $otherId
     * @param $openId
     * @param $oauth_consumer_key
     * @return array
     */
    public function verifyThirdPartUser($accessToken, $otherId, $openId, $oauth_consumer_key)
    {
        $arrUserInfo = [];
        if ($otherId == '11' && !empty($accessToken)) {
            $arrUserInfo = $this->verifyWeChatAccessToken($accessToken, $openId);
        } elseif ($otherId == '12' && !empty($accessToken) && !empty($oauth_consumer_key)) {
            $arrUserInfo = $this->verifyQQAccessToken($accessToken, $openId, $oauth_consumer_key);
        } elseif ($otherId == '10' && !empty($accessToken) && !empty($openId)) {
            $arrUserInfo = $this->verifyWeiboAccessToken($accessToken, $openId);
        }
        return $arrUserInfo;
    }

    /**
     * 校验微信的ACCESSTOKEN
     * @param $accessToken
     * @param $openId
     * @return array
     */
    public function verifyWeChatAccessToken($accessToken, $openId)
    {
        $arrUserInfo = [];
        if (!empty($accessToken) && !empty($openId)) {
            $data = [];
            $data['sMethod'] = "GET";
            $data['arrData'] = [
                'access_token' => $accessToken,
                'openid' => $openId,
                'lang' => 'zh_CN',
            ];
            $arrUserInfo = $this->http(WEIXIN_TOKEN_INFO_URL, $data);
            if (empty($arrUserInfo['openid']) || $arrUserInfo['openid'] != $openId)
                $arrUserInfo = [];
        }
        return $arrUserInfo;
    }

    /**
     * 校验腾讯QQ的ACCESSTOKEN
     * @param $accessToken
     * @param $openId
     * @param $consumerKey
     * @return array
     */
    public function verifyQQAccessToken($accessToken, $openId, $consumerKey)
    {
        $arrUserInfo = [];
        if (!empty($accessToken) && !empty($openId)) {

            $data = [];
            $data['sMethod'] = "GET";
            $data['arrData'] = [
                'access_token' => $accessToken,
                'oauth_consumer_key' => $consumerKey,
                'openid' => $openId,
            ];
            $arrUserInfo = $this->http(QQ_TOKEN_INFO_URL, $data);
            if (isset($arrUserInfo['ret']) && $arrUserInfo['ret'] != 0)
                $arrUserInfo = [];
        }
        return $arrUserInfo;
    }

    /**
     * 校验新浪微博的ACCESSTOKEN以及UID的真实性
     * @param $accessToken
     * @param $weiboUid
     * @return array
     */
    public function verifyWeiboAccessToken($accessToken, $weiboUid)
    {
        $arrUserInfo = [];
        if (!empty($accessToken) && !empty($weiboUid)) {
            $data = [];
            $data['sMethod'] = "POST";
            $data['arrData'] = [
                'access_token' => $accessToken,
            ];
            $arrUserInfo = $this->http(SINA_TOKEN_INFO_URL, $data);
            if (empty($arrUserInfo['uid']) || $arrUserInfo['uid'] != $weiboUid)
                $arrUserInfo = [];
        }
        return $arrUserInfo;
    }
}