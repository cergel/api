<?php

namespace app\services;


use app\base\BaseService;

class UserService extends BaseService
{
    const WX_SUB_CHANNEL_ID_COOKIE = '_wepiao_from';    //cookie中的渠道id标识
    const WX_SUB_CHANNEL_ID_REQUEST = 'from';    //url中的渠道id标识

    /**
     * 获取用户认证体系信息
     *
     * @return  array $userAuthInfo
     */
    public function getUserChannelAuthInfo()
    {
        $userAuthInfo = [];
        //子渠道获取方式修改，优先从cookie获取，cookie没有，从URL上，再没有，用默认值（前端认为传值不够方便，所以他们会种一个cookie，这样后端自己处理就好）
        $iSubChannelId = $this->getSubChannelId();
        //$iSubChannelId = isset(\wyCupboard::$request->params['from']) ? (is_numeric(\wyCupboard::$request->params['from']) ? \wyCupboard::$request->params['from'] : 0) : $this->WX_SALE_APP_ID;
        $iAppId = (isset(\wyCupboard::$request->params['iAppId']) && !empty(\wyCupboard::$request->params['iAppId'])) ? \wyCupboard::$request->params['iAppId'] : 1;
        //用户用户uuid
        $strUuidKey = 'uuid';
        $strUuid = (isset($_COOKIE[$strUuidKey]) && !empty($_COOKIE[$strUuidKey])) ? $_COOKIE[$strUuidKey] : '';
        $userAuthInfo['sUserId'] = ''; //用户userid
        $userAuthInfo['iAppId'] = $iAppId; //登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
        $userAuthInfo['iChannelId'] = CHANNEL_ID; //红包应用渠道，1为高鹏，2为豆瓣，3为微信电影票...
        $userAuthInfo['iSubChannelId'] = $iSubChannelId; //渠道应用子来源，10位数字
        $userAuthInfo['iPlatForm'] = 1; //应用平台（1：电影票，2：演出票）
        $userAuthInfo['uuid'] = $strUuid; //用户uuid，它不是用户终身唯一id，而是用户交互行为中的id
        $userAuthInfo['sRemoteIp'] = \app\helper\Net::getRemoteIp(); //用户的Ip地址
        return $userAuthInfo;
    }

    public function getSubChannelId()
    {
        //子渠道获取方式修改，优先从cookie获取，cookie没有，从URL上，再没有，用默认值（前端认为传值不够方便，所以他们会种一个cookie，这样后端自己处理就好）
        $iSubChannelId = (!empty($_COOKIE[self::WX_SUB_CHANNEL_ID_COOKIE]) && is_numeric($_COOKIE[self::WX_SUB_CHANNEL_ID_COOKIE])) ? $_COOKIE[self::WX_SUB_CHANNEL_ID_COOKIE] : (!empty(\wyCupboard::$request->params[self::WX_SUB_CHANNEL_ID_REQUEST]) ? \wyCupboard::$request->params[self::WX_SUB_CHANNEL_ID_REQUEST] : WX_SALE_APP_ID);
        return $iSubChannelId;
    }

    /*********************************************************
     ******************** 用户中心迁移 START ******************
     ********************************************************/
    /**
     * 判断手机号状态（1 未注册、2 已使用手机号注册、3 已注册但无密码）
     * 注：2与3都表示已注册，但想判断是否绑定过需要看platforms有无对应平台
     *
     * @param string $mobileNo 手机号
     * @param string $otherId 第三方平台编号，传入时返回是否绑定过binded
     *
     * @return array
     */
    public function checkMobileStatus($arrInput = [])
    {
        return $this->sdk->call('user/check-mobile-status', $arrInput);
    }

    /**
     * 判断openid状态（1 未注册、2 已注册但未绑定、3 已绑定） --bbq已更改调用新版service接口
     *
     * @param string $mobileNo 手机号
     * @param string $appkey 渠道编号
     *
     * @return array
     */
    public function checkOpenidStatus($arrInput = [])
    {
        return $this->sdk->call('user/check-openid-status', $arrInput);
    }

    /**
     * 通过手机号查询用户
     *
     * @param string $mobileNo 手机号
     *
     * @return array
     */
    public function getUserinfoByMobile($arrInput = [])
    {
        return $this->sdk->call('user/get-userinfo-by-mobile', $arrInput);
    }

    /**
     * 通过UID查询用户
     *
     * @param  string $memberId 用户UID
     *
     * @return array
     */
    public function getUserinfoByUid($arrInput = [])
    {
        return $this->sdk->call('user/get-userinfo-by-uid', $arrInput);
    }

    /**
     * 通过OpenID查询用户
     *
     * @param  string $openId 第三方UID
     *
     * @return array
     */
    public function getUserinfoByOpenid($arrInput = [])
    {
        return $this->sdk->call('user/get-userinfo-by-openid', $arrInput);
    }

    /**
     * 通过UID查询用户个人资料
     *
     * @param string $memberId 用户UID
     *
     * @return array
     */
    public function getUserProfileByUid($arrInput = [])
    {
        return $this->sdk->call('user/get-user-profile-by-uid', $arrInput);
    }

    /**
     * 获取用户个人资料
     * 此方法, 可通过 openId 或 unionId 或 uid 来获取用户资料
     * @param array $arrInput
     *
     * @return mixed
     */
    public function getUserProfile($arrInput = [])
    {
        $response = $this->sdk->call('user/get-user-profile', $arrInput);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //获取用户标签
            $params = [
                'openId' => $arrInput['openId'],
                'channelId' => $arrInput['channelId'],
            ];
            $tagResponse = $this->sdk->call('user/get-user-tag', $params);
            if (!empty($tagResponse['data'])) {
                $response['data']['summary'] = $tagResponse['data']['summary'];
                $response['data']['is_star'] = $tagResponse['data']['is_star'];
            } else {
                $response['data']['summary'] = '';
                $response['data']['is_star'] = 0;
            }
        }
        return $response;
    }

    /**
     * 通过手机号查询用户openid的集合
     *
     * @param string $mobileNo 手机号
     *
     * @return array
     */
    public function getOpenidListByMobile($arrInput = [])
    {
        return $this->sdk->call('user/get-openid-list-by-mobile', $arrInput);
    }

    /**
     * 根据手机号、密码进行注册 用户信息
     *
     * @param string $mobileNo 手机号
     * @param string $password 密码
     * @param string $nickname 昵称，选传，从cookie中解出
     * @param string $headimgurl 头像，选传，从cookie中解出
     *
     * @return array
     */
    public function MobileRegister($arrInput = [])
    {

        return $this->sdk->call('user/mobile-register', $arrInput);
    }

    /**
     * 根据第三方openId 进行注册 用户信息
     * 注：并不对外使用。目前仅在评论App/Api/User/getUserInfo中使用
     *
     * @param string $openId 第三方UID
     * @param int $otherId 第三方平台的编号
     * @param string $unionId 微信用户唯一编号
     * @param string $nickName 昵称
     *
     * @return array
     */
    public function OpenRegister($arrInput = [])
    {
        return $this->sdk->call('user/open-register', $arrInput);
    }


    /**
     * 获取设置的用户背景图
     * @param array $arrParams
     * @return array
     */
    public function getUserHomeBackground($arrParams = [])
    {
        $return = self::getStOut();
        $res = $this->model('HomePage')->getBackgroud($arrParams['channelId']);
        if (!empty($res)) {
            $data = json_decode($res, true);
            $return['data']['backImage'] = isset($data['back_image']) ? $data['back_image'] : null;
        }
        return $return;
    }

    /**
     * 修改用户信息
     *
     * @param string $memberId 用户UID
     * @param string $city 城市
     * @param string $nickname 昵称
     * @param string $name 真实姓名
     * @param int $sex 性别
     * @param string $email 邮箱
     * @param string $avatar 头像地址
     * @param string $userKey 用户身份证号
     * @param string $signature 用户签名
     * @param int $maritalStat 婚恋状态，具体数值由前端定义
     * @param int $carrer 职业，具体数值由前端定义
     * @param string $enrollmentYear 入学年份
     * @param string $highestEdu 最高学历，具体数值由前端定义
     * @param string $school 学校
     * @param string $birthday 生日
     * @param string $watchCPNum 共同观影人数
     * @param string $hobbies 兴趣爱好，数字1~15，可多选，用逗号分隔
     * @return array
     */
    public function UserEdit($arrInput = [])
    {
        return $this->sdk->call('user/update-userinfo', $arrInput);
    }

    /**
     * 手机号密码登陆接口
     *
     * @param string $password 密码
     * @param string $mobileNo 手机号
     * @param string $otherId 可选，第三方平台编号，登陆并绑定时传入
     * @param string $openId 可选，第三方帐号，登陆并绑定时需要，从cookie中获取，不需传入
     * @param string $unionId 可选，微信唯一ID，otherId为11时需要，从cookie中获取，不需传入
     *
     * @return string array
     */
    public function LoginAndBind($arrInput = [])
    {
        return $this->sdk->call('user/login-and-bind', $arrInput);
    }

    /**
     * 绑定手机号
     *
     * @param string $mobileNo 绑定手机号
     * @param string $code 手机验证码
     * @param string $openId 第三方唯一ID，从cookie中解出
     * @param int $otherId 第三方平台的编号id 10：新浪微博，11：微信，12:QQ
     * @param string $unionId 微信授权成功唯一ID
     *
     * @return array
     */
    public function Bind($arrInput = [])
    {
        $userinfo = $this->sdk->call('user/bind', $arrInput);
        //bind接口返回是对的再去调红包接口
        if ($userinfo['ret'] == 0 && isset($userinfo['data'])) {
            //如果配置了资源id
            if (isset($arrInput['suitId'])) {
                $arrInput['subChannelId'] = $this->getSubChannelId();
                $bonus = $this->sdk->call('bonus/get-suit-bonus', $arrInput);
                if ($bonus['ret'] == 0) {
                    $userinfo['data']['bonusinfo'] = array('ret' => '0', 'data' => $bonus['data']['list']);
                } else {
                    //红包过期，资源不存在，被抢光都返回这个错误
                    $userinfo['data']['bonusinfo'] = array('ret' => '-1', 'msg' => '红包被抢光了');
                }
            } else {
                $userinfo['data']['bonusinfo'] = null;
            }
        }
        return $userinfo;
    }

    /**
     * 手机号修改 --bbq已更改调用新版service接口
     *
     * @author songlin
     *
     * @param string $uid 唯一标识
     * @param string $mobileNoOld 原手机号
     * @param string $mobileNo 新手机
     * @param string $code 新手机的短信验证码
     * @param int $appkey 来源（5：IOS，6：安卓）
     * @param int $t 请求当前时间戳（秒）
     */
    public function EditMobile($arrInput = [])
    {
        return $this->sdk->call('user/edit-mobile', $arrInput);
    }

    /**
     * 修改密码 --bbq已更改调用新版service接口
     *
     * @author songlin
     *
     * @param string $uid 唯一标识
     * @param string $passwordOld 原密码
     * @param string $password 新密码
     * @param int $appkey 来源（5：IOS，6：安卓）
     *
     * @return array
     */
    public function EditPassword($arrInput = [])
    {
        return $this->sdk->call('user/edit-password', $arrInput);
    }

    /**
     * 密码重置 --bbq已更改调用新版service接口
     *
     * @param string $uid 唯一标识
     * @param string $mobileNo 手机号
     * @param string $password 新密码
     * @param int $appkey 来源 （5：IOS，6：安卓）
     *
     * @return array;
     */
    public function EditReset($arrInput = [])
    {
        return $this->sdk->call('user/edit-reset', $arrInput);
    }

    /**
     * 设置密码（仅对无密码用户有用） --bbq已更改调用新版service接口
     *
     * @param string $uid 唯一标识
     * @param string $password 密码
     * @param int $appkey 来源 （5：IOS，6：安卓）
     *
     * @return array
     */
    public function EditSetPassword($arrInput = [])
    {
        return $this->sdk->call('user/edit-set-Password', $arrInput);
    }

    /**
     * 通过ucid获取用户头像与昵称（用户中心中openid上有对应的就取，没有就取uid上的）
     * 再没取到，则返回“路人甲”与默认头像
     *
     * @param $input ['uid'] 三个参数至少传入一个即可
     * @param $input ['openId']
     * @param $input ['unionId']
     * @param $input ['channelId']
     *
     * @return mixed
     */
    public function getUserinfoByUcid($input)
    {
        $return = [];
        $response = $this->sdk->call("comment/get-userinfo-by-ucid", $input);
        if (isset($response['ret']) && $response['ret'] == 0) {
            $return = $response['data'];
        }

        return $return;
    }

    /**
     * 取入参时兼容memberId与uid，优先取memberId
     *
     * @param $arrInput
     *
     * @return string $memberId
     */
    public function getParamMemberid($arrInput)
    {
        return !empty($arrInput['memberId']) ? $arrInput['memberId'] : self::getParam($arrInput, 'uid');
    }

    /**
     * 通过明星配对游戏来更新用户生日和性别
     * @param array $arrInput
     * @return mixed
     */
    public function setStarPair($arrInput = [])
    {
        $arrInput['gender'] = $arrInput['gender'] == '1' ? '男' : '女';
        return $this->sdk->call('msdb/get-star-pair', $arrInput);
    }

    /**
     * 通过明星配对游戏来更新用户生日和性别
     * @param array $arrInput
     * @return mixed
     */
    public function setStarPairPv($arrInput = [])
    {
        $page_pv = $this->sdk->call('msdb/get-star-pair-pv', $arrInput);
        $page_pv = !empty($page_pv['data']['page_pv']) ? $page_pv['data']['page_pv'] : 0;
        return $page_pv;
    }

    /**
     * 获取用户观影轨迹
     * @param $arrParams
     * @return array
     */
    public function getUserTrace($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $params['token'] = $arrParams['token'];
        $params['page'] = $arrParams['page'];
        $params['num'] = $arrParams['num'];
        if (!empty($params['channelId']) && !empty($params['openId'])) {
            $res = $this->sdk->call('user/get-trace-path', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     *  删除观影轨迹
     * @return array
     */
    public function deleteUserTrace($arrParams){
        $return = self::getStOut();
        //调评论中心删除看过，因订单观影轨迹要求删除评论评分，因此为了数据一致性把点看过的轨迹也删除
        if (!empty($arrParams['channelId']) && !empty($arrParams['movieId']) && !empty($arrParams['openId']) && !empty($arrParams['token'])) {
            $url = COMMENT_CENTER_URL . "/v1/movies/{$arrParams['movieId']}/seen";
            $param = [];
            $param['sMethod'] = "POST";
            $param['iTimeout'] = 2;
            $param['arrCookies']=$arrParams['arrCookies'];
            $param['arrData'] = [
                'token' => $arrParams['token'],
                'channelId' => $arrParams['channelId'],
                'movieId' => $arrParams['movieId'],
                'seen' => 0,
            ];
            $res=$this->http($url, $param);
            if($res['ret']==0){
                //如果有orderId，则去删除订单的观影轨迹
                if(!empty($arrParams['orderId'])){
                    $return = $this->sdk->call('user/delete-trace-path', $arrParams);
                }
            }
            else{
                $return=$this->getErrorOut(ERRORCODE_DELETE_USER_TRACE_PATH_ERROR);
            }
        }
        else{
            $return=$this->getErrorOut(ERRORCODE_USER_TRACE_PARAMS_ERROR);
        }
        return $return;
    }

    /**
     * 获取用户观影轨迹
     * @param $arrParams
     * @return array
     */
    public function getUserWants($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $params['ucid'] = $arrParams['ucid'];
        $params['page'] = $arrParams['page'];
        $params['num'] = $arrParams['num'];
        $params['cityId'] = $arrParams['cityId'];
        $params['sort'] = $arrParams['sort'];
        $params['method'] = $arrParams['method'];
        if (!empty($params['channelId']) && !empty($params['openId'])) {
            $res = $this->sdk->call('wants/get-user-want-movie-list', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开。
     */
    public function getMqqPushSwitch($arrInput = [])
    {
        return $this->sdk->call('mqq/get-mqq-push-switch', $arrInput);
    }

    /**
     * 设置用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开
     * 开关顺序：( 11 ) => ( 放映后引导评论，评论有回复 )
     */
    public function setMqqPushSwitch($arrInput = [])
    {
        return $this->sdk->call('mqq/set-mqq-push-switch', $arrInput);
    }

    /**
     * 根据渠道解密openId
     * @param $channelId
     * @param $str
     * @return bool
     */
    public function getOpenIdFromStr($channelId, $str)
    {
        $return = [];
        $response = \wyCupboard::$sdk->call("common/decrypt", ['str' => $str, 'channelId' => $channelId]);
        if ($response['ret'] != 0) {
            $return['data'] = "";
            return $return;
        }
        if (in_array($channelId, [8, 9])) {
            $obj = json_decode($response['data']['decryptStr'], true);
            $openId = isset($obj['openId']) ? $obj['openId'] : "";
        } else {
            $openId = isset($response['data']['decryptStr']) ? $response['data']['decryptStr'] : "";
        }
        $return['data'] = $openId;
        return $return;
    }

    /**
     * 检测用户是否在黑名单中
     *
     * @param array $arrInput
     *
     * @return mixed
     */
    public function blackCheck($arrInput = [])
    {
        return $this->sdk->call('user/check-black', $arrInput);
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

    /**
     * 微信电影票支付页面调用，当支付查询朝伟可用优惠中没有返回V卡  获取推荐的会员卡
     *
     * @param array $arrInput
     *
     * @return mixed
     */
    public function payVipCard($arrInput = [])
    {
        return $this->sdk->call('user/pay-vip-card', $arrInput);
    }

    /**
     * 获取想看及喜欢总数
     * @param array $arrInput
     */
    public function getCounts($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = ['wantCount' => 0, 'actorCount' => 0, 'guideCount' => 0];
        //获取想看电影总数
        $wantCount = $this->_getUserWantsCount($arrInput);
        $actorCount = $this->_getUserActorCount($arrInput);
        $guideCount = $this->_getUserMovieGuideCount($arrInput);
        if (isset($wantCount['data']['totalCount'])) {
            $return['data']['wantCount'] = $wantCount['data']['totalCount'];
        }
        if (isset($actorCount['data']['totalCount'])) {
            $return['data']['actorCount'] = $actorCount['data']['totalCount'];
        }
        if (isset($guideCount['data']['totalCount'])) {
            $return['data']['guideCount'] = $guideCount['data']['totalCount'];
        }
        return $return;
    }


    /**
     * 单独获取用户想看总数
     * @param array $arrParams
     * @return array
     */
    private function _getUserWantsCount($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['ucid'] = $arrParams['ucid'];
        if (!empty($params['channelId']) && !empty($params['ucid'])) {
            $res = $this->sdk->call('wants/get-user-want-movie-count', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 单独获取用户喜欢的影人列表
     * @param array $arrParams
     * @return array
     */
    private function _getUserActorCount($arrParams = [])
    {
        $return = self::getStOut();
        $params['openId'] = $arrParams['openId'];
        $params['channelId'] = $arrParams['channelId'];
        if (!empty($params['openId'])) {
            $res = $this->sdk->call('msdb/actor-like-count', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }


    /**
     * 查看用户已领取的观影秘籍列表(一个片子一个秘籍,多个数据,其实等于不同片子的秘籍列表)
     *
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    private function _getUserMovieGuideCount($arrParams = [])
    {
        $params = [];
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $return = $this->sdk->call('movie-guide/get-movie-guide-count', $params);
        return $return;
    }

}
