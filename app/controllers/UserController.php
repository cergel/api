<?php

namespace app\controllers;

use app\base\BaseController;

class UserController extends BaseController
{
    private $bind_bonus_info = array(
        '1' => ['id' => 1242, 'status' => 1],
        '5' => ['id' => 1242, 'status' => 1],
        '6' => ['id' => 1171, 'status' => 1],
    );

    /**
     * 获取用户手机号接口
     */
    public function getUserMobile()
    {
        $return = self::getStOut();
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['salePlatformType'] = !empty($arrParams['salePlatformType']) ? $arrParams['salePlatformType'] : SALE_PLATFORM_TYPE;
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['userId'] = $this->getRequestParams('memberId'); //暂时保留，后续需要去掉
        $ret = \wyCupboard::$sdk->call('user/get-mobile', $params);
        if ($ret['sub'] == 0 AND $ret['ret'] == 0) {
            $cityArr = !empty($ret['data']) ? $ret['data'] : new \stdClass();
            $return['data'] = $cityArr;
        } else {
            $return['ret'] = $ret['ret'];
        }
        //输出
        $this->jsonOut($return);
    }

    /**
     * 获取用户信息, 并更新到用户中心
     * 该接口要求必须是用户登录态
     */
    public function getUserInfo()
    {
        switch (\wyCupboard::$channelId) {
            case 3: //微信
                $serviceName = 'WxUser';
                break;
            case 28: //手Q
                $serviceName = 'MqqUser';
                break;
            default:
                $serviceName = 'User';
        }
        $strOpenId = $this->service('Login')->getOpenIdFromCookie();
        $arrUserInfo = $this->service($serviceName)->getUserInfoFromCookie();
        //必须得有openId
        if (empty($strOpenId)) {
            $this->jsonOut(self::getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        //如果Cookie中有信息,并且needUserInfo参数为0,则认为是不需要强制获取用户新的数据信息
        $iNeedUserInfo = $this->getRequestParams('needUserInfo', 0);
        if (!empty($arrUserInfo['nickname']) && !empty($arrUserInfo['headimgurl']) && empty($iNeedUserInfo)) {
            $res = self::getStOut();
            $res['data'] = $arrUserInfo;
        } //强制获取用户信息,并将用户信息更新到用户中心
        else {
            $params['channelId'] = CHANNEL_ID;
            $params['openId'] = $strOpenId;
            $params['needUserInfo'] = $iNeedUserInfo;
            $params['redirectUrl'] = $this->getRequestParams('redirectUrl', '');;
            $params['code'] = $this->getRequestParams('code', '');
            $res = $this->service($serviceName)->getUserInfo($params);
        }
        if (!empty($res['data']) && $this->getRequestParams('page_pv', 0)) {
            $res['data']['page_pv'] = $this->service('User')->setStarPairPv(['channelId' => CHANNEL_ID]);
        }
        $this->jsonOut($res);
    }

    /**
     * 获取设置的用户背景图
     */
    public function getUserHomeBackground()
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('User')->getUserHomeBackground($params);
        $this->jsonOut($ret);
    }


    /**
     * 获取用户信息, 并更新到用户中心
     * (调整中，还未加routes，目前问题在于一个用户只会拉取一次信息)
     * 该接口要求必须是用户登录态
     * 流程：先查cookie，再查用户中心，再从腾讯拉并注册。每一步查到则直接返回，并存入cookie。
     * @params string redirectUrl   走腾讯授权时的回调地址，强制授权时必传！
     * @params string code   走腾讯授权时的授权码
     * @params int needUserInfo 从腾讯授权时如果静默授权未拿到，是否需要强制授权，默认0
     */
    public function getUserInfoV2()
    {
        $channelId = CHANNEL_ID;
        $needUserInfo = $this->getRequestParams('needUserInfo', 0);
        $res = self::getStOut();
        switch ($channelId) {
            case 3: //微信
                $serviceName = 'WxUser';
                break;
            case 28: //手Q
                $serviceName = 'MqqUser';
                break;
            default:
                $serviceName = 'User';
        }
        //登录态检查
        $strOpenId = $this->service('Login')->getOpenIdFromCookie();
        if (empty($strOpenId)) {
            $this->jsonOut(self::getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        //1、查询cookie是否存有用户信息
        $arrUserInfo = $this->service($serviceName)->getUserInfoFromCookie();
        if (!empty($arrUserInfo['nickname']) && !empty($arrUserInfo['headimgurl'])) {
            $res['data'] = $arrUserInfo;
        } else {
            //2、根据openid查询用户中心，查到了则直接存cookie并返回
            $ucInfo = $this->service('User')->getUserinfoByOpenid(['channelId' => $channelId, 'openId' => $strOpenId]);
            if ($ucInfo['ret'] == 0 && !empty($ucInfo['data']['nickName']) && !empty($ucInfo['data']['photoUrl'])
            ) {
                $arrUserInfo = [
                    'nickname' => $this->getParam($ucInfo['data'], 'nickName'),
                    'headimgurl' => $this->getParam($ucInfo['data'], 'photoUrl'),
                    'memberId' => $this->getParam($ucInfo['data'], 'memberId'),
                    //'vipInfo' => $this->getParam($ucInfo['data'], 'vipInfo'),
                ];
                $res['data'] = $arrUserInfo;
                $this->service($serviceName)->setUserInfoToCookie($arrUserInfo);
            } else {
                //3、从腾讯拉取，并注册至用户中心，最后存入cookie
                $params['channelId'] = $channelId;
                $params['openId'] = $strOpenId;
                $params['needUserInfo'] = $needUserInfo;
                $params['redirectUrl'] = $this->getRequestParams('redirectUrl', '');;
                $params['code'] = $this->getRequestParams('code', '');
                $res = $this->service($serviceName)->getUserInfo($params);
            }
        }
        //添加明星配对信息
        if (!empty($res['data']) && $this->getRequestParams('page_pv', 0)) {
            $res['data']['page_pv'] = $this->service('User')->setStarPairPv(['channelId' => CHANNEL_ID]);
        }
        $this->jsonOut($res);
    }

    /*     * *******************************************************
     * ******************* 用户中心迁移 START ******************
     * ****************************************************** */

    /**
     * 判断手机号状态（1 未注册、2 已使用手机号注册、3 已注册但无密码）
     * 注：2与3都表示已注册，但想判断是否绑定过需要看platforms有无对应平台
     *
     * @param string $mobileNo 手机号
     * @param string $otherId 第三方平台编号，传入时返回是否绑定过binded
     *
     * @return array
     */
    public function checkMobileStatus()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['mobileNo'] = $this->getRequestPhone('mobile');
        if (CHANNEL_ID != 8 && CHANNEL_ID != 9) {
            $params['otherId'] = WX_ID_TYPE_SERVICE;
        }
        //重新登录
        if (empty($params['mobileNo'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_PARAM);
        } else {
            $ret = $this->service('User')->checkMobileStatus($params);
        }
        $this->jsonOut($ret);
    }


    /**
     * 判断openid状态（1 未绑定、2 已绑定手机号、3 已绑定但无密码）
     *
     * @param string $openId 第三方帐号
     *
     * @return array
     */
    public function checkOpenidStatus()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['otherId'] = WX_ID_TYPE_SERVICE;
        //从cookie中获取openid
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret = $this->service('User')->checkOpenidStatus($params);
        $this->jsonOut($ret);
    }

    /**
     * 通过手机号查询用户
     *
     * @param string $mobileNo 手机号
     *
     * @return array
     */
    public function getUserinfoByMobile()
    {
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('User')->getUserinfoByMobile($params);
        $this->jsonOut($ret);
    }

    /**
     * 通过UID查询用户
     *
     * @param  string $memberId 用户UID
     *
     * @return array
     */
    public function getUserinfoByUid()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['userId'] = $this->getRequestParams('memberId');
        $ret = $this->service('User')->getUserinfoByUid($params);
        $this->jsonOut($ret);
    }

    /**
     * 通过OpenID查询用户
     *
     * @param string $openId 第三方UID，从cookie中解出
     *
     * @return array
     */
    public function getUserinfoByOpenid()
    {
        //从cookie中获取openid
        $openId = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $openId;
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret = $this->service('User')->getUserinfoByOpenid($params);
        $this->jsonOut($ret);
    }

    /**
     * 通过手机号查询用户openid的集合
     *
     * @param string $mobileNo 手机号
     *
     * @return array
     */
    public function getOpenidListByMobile()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        $ret = $this->service('User')->getOpenidListByMobile($params);
        $this->jsonOut($ret);
    }

    /**
     * 查询用户个人资料
     * 通过任意一项(uid、openId), 获取用户资料。之所以可以这样做, 是因为调用此接口的前提是, 此用户必须已经绑定到了用户中心
     *
     * @param string $memberId memberId 用户唯一uid，非必须
     * @param string $openId 用户openId
     *
     * @return array
     */
    public function getUserProfile()
    {
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                $uid = $this->service('Login')->getAuthInfoByToken("uid");
                $params['userId'] = $uid;
                $ret = $this->service('AppUser')->getProfile($params);
            } else {
                $ret = $this->service('User')->getUserProfile($params);
            }
        }
        $this->jsonOut($ret);
    }

    /**
     * 手机号密码登陆接口
     *
     * @param string $password 密码
     * @param string $mobileNo 手机号
     * @param boolean $bind 是否开启“登录并绑定”，开启时会从cookie中取openId、unionId进行绑定
     *
     * @return string array
     */
    public function LoginAndBind()
    {
        switch (\wyCupboard::$channelId) {
            case 3: //微信
                $serviceName = 'WxUser';
                break;
            case 28: //手Q
                $serviceName = 'MqqUser';
                break;
            default:
                $serviceName = 'User';
        }
        $params['channelId'] = CHANNEL_ID;
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        $params['password'] = $this->getRequestParams('password', '');
        $params['bind'] = $this->getRequestParams('bind', ''); //是否开启登录并绑定
        //如果传入了otherId，则认为是“登录并自动绑定第三方”。
        if (!empty($params['bind'])) {
            $params['otherId'] = WX_ID_TYPE_SERVICE;
            //如果是微信来的但是没有unionId，直接报错(手Q这里直接跳过)
            $openid = $this->service('Login')->getOpenIdFromCookie();
            $unionId = ($params['channelId'] == 3) ? $this->service("Login")->getUnionIdFromCookie() : '';
            if (empty($openid) || ($params['channelId'] == 3 && empty($unionId))) {
                return $this->getErrorOut(ERRORCODE_ERROR_RELOGIN);
            }
            $params['openId'] = $openid;
            $params['unionid'] = $unionId;
            //头像昵称从cookie中取
            $userinfoCookie = $this->service($serviceName)->getUserInfoFromCookie();
            $params['openId'] = $openid;
            $params['nickname'] = $this->getRequestParams('nickname', $userinfoCookie['nickname']);
            $params['headimgurl'] = $this->getRequestParams('headimgurl', $userinfoCookie['headimgurl']);
        }

        $ret = $this->service('User')->LoginAndBind($params);
        $this->jsonOut($ret);
    }

    /**
     * 绑定手机号
     *
     * @param string $phone 绑定手机号
     * @param string $code 手机验证码
     * @param string $openId 第三方唯一ID，从cookie中解出
     * @param int $otherId 第三方平台的编号id 10：新浪微博，11：微信，12:QQ
     * @param string $unionId 微信授权成功唯一ID
     *
     * @return array
     */
    public function Bind()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['mobileNo'] = $this->getRequestPhone('phone');
        $params['code'] = $this->getRequestParams('code', '');
        $params['otherId'] = WX_ID_TYPE_SERVICE;
        $params['unionId'] = $this->service('Login')->getUnionIdFromCookie();
        $bonusPosition = $this->getRequestParams('bonusPosition', '');//领取红包位置
        $bind_bonus_info = $this->bind_bonus_info;
        //从cookie中获取openId
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['encryptedData'] = $this->getRequestParams('encryptedData');//加密
        $params['iv'] = $this->getRequestParams('iv');
        if (empty($params['openId']) || ($params['channelId'] == 3 && empty($params['unionId']))) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            //绑定领取红包逻辑
            if (isset($bonusPosition) && isset($bind_bonus_info[$bonusPosition]) && $bind_bonus_info[$bonusPosition]['status'] == 1) {
                $params['suitId'] = $bind_bonus_info[$bonusPosition]['id'];
                $params['phone'] = $this->getRequestParams('phone', '');
                $params['rcvDev'] = $this->getRequestParams('deviceId'); //领取用户设备ID（app必传）
                $params['rcvImei'] = $this->getRequestParams('imei'); //IMEI（APP必传）
                $params['appVers'] = $this->getRequestParams('appver'); //app版本号，iOS或Android渠道必传，全网渠道如果为app环境也必传
            }
            //如果是小程序要去获取unionId
            if (\wyCupboard::$channelId == 63 || \wyCupboard::$channelId == 86) {
                $ret = $this->service('Login')->wxappGetUnionId($params);
                if ($ret['ret'] == 0 && isset($ret['data']['unionId'])) {
                    $params['unionId'] = $ret['data']['unionId'];
                    $ret = $this->service('User')->Bind($params);
                }
            } else {
                $ret = $this->service('User')->Bind($params);
            }
        }
        $this->jsonOut($ret);
    }

    //已登录绑定第三方账户 本接口直接读取token反查获得手机号码
    public function bindThirdParty()
    {
        $return = $this->getStOut();
        $channelId = $this->getRequestParams("channelId");
        $accessToken = $this->getRequestParams("accessToken");
        $nickName = $this->getRequestParams("nickName");
        $oauthConsumerKey = $this->getRequestParams("oauthConsumerKey");
        $openId = $this->getRequestParams("openId");
        $unionId = $this->getRequestParams("unionId");
        $otherId = $this->getRequestParams("otherId");
        $photo = $this->getRequestParams("photo");
        //校验第三方账户和ACCESSTOKEN是否正确
        $verifyResponse = $this->service("AppUser")->verifyThirdPartUser($accessToken, $otherId, $openId, $oauthConsumerKey);
        if (!$verifyResponse) {
            $response['errorcode'] = -112028;
            $this->jsonOut($response);
        }
        //注册第三方账户
        //调用SDK进行用户注册
        $params = [
            'openId' => $openId,
            'otherId' => $otherId,
            'platForm' => I_PLATFORM,
            'nickname' => $nickName,
            'unionId' => $unionId,
            'avatar' => $photo,
            'channelId' => $channelId,
        ];
        $response = $this->sdk->call('user/register-by-openid', $params);

        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //获取用户信息
            $userInfoByOpenId = $this->service("User")->getUserInfoByOpenId($channelId, $openId, $otherId, $unionId);
            if ($userInfoByOpenId) {
                $userInfoByOpenId['hasMobile'] = (empty($userInfoByOpenId['mobileNo'])) ? false : true;
                //已经登录的用户则一定可以获取到手机号码
                $return['data'] = $this->service("User")->addUserToken($channelId, $userInfoByOpenId);
                //激活优惠到人任务
                $this->sdk->call("task/discount", ['channelId' => $channelId, 'openId' => $openId]);
                //调用SDK绑定用户
                $params = [
                    'mobileNo' => $userInfoByOpenId['mobileNo'],
                    'openId' => $openId,
                    'otherId' => $otherId,
                    'unionId' => $unionId,
                    'channelId' => $channelId,
                ];
                $response = $this->sdk->call('user/bind-mobile', $params);
                if ($response['ret'] == 0 && $response['sub'] == 0) {
                    $return = $response;
                } else {
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
     * 根据手机号、密码进行注册 用户信息
     *
     * @param string $mobileNo 手机号
     * @param string $password 密码
     * @param string $nickname 昵称，选传，从cookie中解出
     * @param string $headimgurl 头像，选传，从cookie中解出
     *
     * @return array
     */
    public function MobileRegister()
    {
        switch (\wyCupboard::$channelId) {
            case 3: //微信
                $arrUserinfo = $this->service('WxUser')->getUserInfoFromCookie();
                break;
            case 28: //手Q
                $arrUserinfo = $this->service('MqqUser')->getUserInfoFromCookie();
                break;
        }
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        $params['password'] = $this->getRequestParams('password', '');
        $params['nickname'] = $this->getRequestParams('nickname', '');
        $params['channelId'] = CHANNEL_ID;
        //从cookie中解出用户信息
        //昵称优先级：1、直接传入的；2、cookie中保存的手Q昵称；3、手机用户_4位尾号
        if (empty($params['nickname'])) {
            $params['nickname'] = !empty($arrUserinfo['nickname']) ? $arrUserinfo['nickname'] : '手机用户_' . substr($params['mobileNo'],
                    -4, 4);
        }
        //头像取cookie中保存的手Q头像
        if (!empty($arrUserinfo['headimgurl'])) {
            $params['headimgurl'] = $arrUserinfo['headimgurl'];
        }
        $ret = $this->service('User')->MobileRegister($params);
        $this->jsonOut($ret);
    }

    /**
     * 修改用户信息, 并且返回修改后的数据
     *
     * @param string $memberId 用户UID
     * @param string $city 城市
     * @param string $nickName 昵称
     * @param string $userName 真实姓名
     * @param int $sex 性别
     * @param string $email 邮箱
     * @param string $photoUrl 头像地址
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
     *
     * @return array
     */
    public function UserEdit()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //登陆判断
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $params['nickname'] = $this->getRequestParams('nickName');
            $params['email'] = $this->getRequestParams('email');
            $params['sex'] = $this->getRequestParams('sex');
            $params['name'] = $this->getRequestParams('userName');
            $params['userKey'] = $this->getRequestParams('userKey');
            $params['avatar'] = $this->getRequestParams('photoUrl');
            $params['city'] = $this->getRequestParams('city');
            $params['signature'] = $this->getRequestParams('signature');
            $params['maritalStat'] = $this->getRequestParams('maritalStat');
            $params['carrer'] = $this->getRequestParams('carrer');
            $params['enrollmentYear'] = $this->getRequestParams('enrollmentYear');
            $params['highestEdu'] = $this->getRequestParams('highestEdu');
            $params['school'] = $this->getRequestParams('school');
            $params['birthday'] = $this->getRequestParams('birthday');
            $params['watchCPNum'] = $this->getRequestParams('watchCPNum');
            $params['hobbies'] = $this->getRequestParams('hobbies');
            if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                $params['userId'] = $this->service('Login')->getAuthInfoByToken("uid");
            }
            $ret = $this->service('User')->UserEdit($params);
            //判断是否更新成功,如果是,更新一下Cookie中的nickname
            if (isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data']['nickName'])) {
                switch ($params['channelId']) {
                    case 3:
                        setcookie('WxNickname', $ret['data']['nickName'], time() + 3600, '/', '.wepiao.com');
                        break;
                    case 28:
                        setcookie('MqqNickname', $ret['data']['nickName'], time() + 3600, '/', '.wepiao.com');
                        break;
                }
            } else {
                if (CHANNEL_ID == 8 || CHANNEL_ID == 9) {
                    $ret['errorcode'] = -112000;
                }
            }
        }
        $this->jsonOut($ret);
    }

    /**
     * 手机号修改
     *
     * @param string $memberId 唯一标识
     * @param string $mobileNoOld 原手机号
     * @param string $mobileNo 新手机
     * @param string $code 新手机的短信验证码，因页面关系，只验证新手机号
     */
    public function EditMobile()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //登陆判断
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_USER_CENTER_UID_ERROR);

        } else {
            $params['mobileNo'] = $this->getRequestPhone('phone');
            $params['code'] = $this->getRequestParams('code');
            $params['mobileNoOld'] = $this->getRequestParams('oldPhone');
            $ret = $this->service('User')->EditMobile($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 修改密码
     *
     * @param string $memberId 用户唯一标识uid
     * @param string $passwordOld 原密码
     * @param string $password 新密码
     * @param string $mobileNo 手机号，防黄牛加的验证
     *
     * @return array
     */
    public function EditPassword()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            $params['userId'] = $this->service('Login')->getAuthInfoByToken("uid");
        }
        $params['passwordOld'] = $this->getRequestParams('passwordOld');
        $params['password'] = $this->getRequestParams('password');
        $params['mobileNo'] = $this->getRequestPhone('phone');
        $ret = $this->service('User')->EditPassword($params);
        $this->jsonOut($ret);
    }

    /**
     * 密码重置
     *
     * @param string $memberId 用户唯一标识uid
     * @param string $mobileNo 手机号
     * @param string $code 手机验证码
     * @param string $password 新密码
     *
     * @return array;
     */
    public function EditReset()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        if (CHANNEL_ID == 8 || CHANNEL_ID == 9) {
            $response = $this->service('AppUser')->getUserInfoByPhone(CHANNEL_ID, $params['mobileNo']);
            if ($response['sub'] == -20008) {
                $return['errorcode'] = -112024;
                $this->jsonOut($return);
            }
            $params['memberId'] = $response['data']['memberId'];
        }

        $params['password'] = $this->getRequestParams('password');
        $params['code'] = $this->getRequestParams('code');
        $ret = $this->service('User')->EditReset($params);
        $this->jsonOut($ret);
    }

    /**
     * 设置密码（仅对无密码用户有用）
     *
     * @param string $memberId 用户唯一标识uid
     * @param string $mobileNo 手机号
     * @param string $password 密码
     *
     * @return array
     */
    public function EditSetPassword()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($openid)) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['mobileNo'] = $this->getRequestPhone('mobileNo');
        $params['password'] = $this->getRequestParams('password');
        $ret = $this->service('User')->EditSetPassword($params);
        $this->jsonOut($ret);
    }

    /**
     * 通过明星配对游戏来更新用户生日和性别
     * @param int $sex 性别，1：男，2：女
     * @param string $birthday 生日：2017-01-22
     * @param string $count 获取明星个数
     */
    public function setStarPair()
    {
        $params = [];
        $params['channelId'] = $this->getRequestParams('channelId');;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['birthday'] = $this->getRequestParams('birthday');
        $params['gender'] = $this->getRequestParams('sex');
        $params['count'] = $this->getRequestParams('count', 5);
        $ret = $this->service('User')->setStarPair($params);
        $this->jsonOut($ret);
    }

    /* *******************************************************
     * ******************* 用户中心迁移 END *******************
     * ****************************************************** */
    /**
     * 获取用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开
     */
    public function getMqqPushSwitch()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret = $this->service('User')->getMqqPushSwitch($params);
        $this->jsonOut($ret);
    }

    /**
     * 
     * 获取用户观影轨迹
     */
    public function getUserTrace()
    {
        $params['channelId'] = CHANNEL_ID;
        $openid = $this->service('Login')->getOpenIdFromCookie();
        $unionId = ($params['channelId'] == 3) ? $this->service("Login")->getUnionIdFromCookie() : '';
        if (CHANNEL_ID == 3) {
            $params['token'] = isset($_COOKIE[COOKIE_WX_OPEN_ID]) ? $_COOKIE[COOKIE_WX_OPEN_ID] : '';
        }
        if (CHANNEL_ID == 28) {
            $params['token'] = isset($_COOKIE[COOKIE_MQQ_OPEN_ID]) ? $_COOKIE[COOKIE_MQQ_OPEN_ID] : '';
        }
        if (empty($openid) || ($params['channelId'] == 3 && empty($unionId))) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['token'] = $this->getRequestParams("token", '');
        $params['openId'] = $openid;
        $ret = $this->service('User')->getUserTrace($params);
        $this->jsonOut($ret);
    }

    /**
     * 删除观影轨迹
     */
    public function deleteUserTrace(){
        $params['channelId'] = CHANNEL_ID;
        $params['token'] = $this->getRequestParams("token", '');//客户端的token是传过来的
        $params['orderId'] = $this->getRequestParams("orderId", '');
        $params['movieId'] = $this->getRequestParams("movieId", '');
        $params['traceId'] = $this->getRequestParams("traceId", '');
        $openid = $this->service('Login')->getOpenIdFromCookie();
        if (CHANNEL_ID == 3) {
            $params['token'] = isset($_COOKIE[COOKIE_WX_OPEN_ID]) ? $_COOKIE[COOKIE_WX_OPEN_ID] : '';
            $params['arrCookies']=[COOKIE_WX_OPEN_ID=>$params['token']];
        }
        elseif (CHANNEL_ID == 28) {
            $params['token'] = isset($_COOKIE[COOKIE_MQQ_OPEN_ID]) ? $_COOKIE[COOKIE_MQQ_OPEN_ID] : '';
            $params['arrCookies']=[COOKIE_MQQ_OPEN_ID=>$params['token']];
        }
        $unionId = ($params['channelId'] == 3) ? $this->service("Login")->getUnionIdFromCookie() : '';
        if (empty($openid) || ($params['channelId'] == 3 && empty($unionId))) {
            $ret=$this->getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif(empty($params['movieId']) || empty($params['traceId'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_TRACE_PARAMS_ERROR);
        }
        else{
            $params['openId'] = $openid;
            $ret = $this->service('User')->deleteUserTrace($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取用户想看清单
     */
    public function getUserWants()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['sort'] = $this->getRequestParams("sort", 0);
        $params['cityId'] = $this->getRequestParams("cityId", 10);
        $params['method'] = $this->getRequestParams("method", 'desc');
        $openid = $this->service('Login')->getOpenIdFromCookie();
        $unionId = ($params['channelId'] == 3) ? $this->service("Login")->getUnionIdFromCookie() : '';
        if (empty($openid) || ($params['channelId'] == 3 && empty($unionId))) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['openId'] = $openid;
        $params['ucid'] = $params['channelId'] == 3 ? $unionId : $openid;
        $ret = $this->service('User')->getUserWants($params);
        $this->jsonOut($ret);
    }

    /*
     * 设置用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开
     * 开关顺序：( 11 ) => ( 放映后引导评论，评论有回复 )
     */
    public function setMqqPushSwitch()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['pushAfterShowed'] = $this->getRequestParams('pushAfterShowed');
        $params['replyOnComment'] = $this->getRequestParams('replyOnComment');
        $ret = $this->service('User')->setMqqPushSwitch($params);
        $this->jsonOut($ret);
    }

    /**
     * 兼容获取手机号，支持phone与mobileNo
     * @return null
     */
    public function getRequestPhone($oldParam)
    {
        $phone = $this->getRequestParams('phone');
        $phone = !empty($phone) ? $phone : $this->getRequestParams('mobileNo');
        $phone = !empty($phone) ? $phone : $this->getRequestParams('mobile');
        return $phone;
    }

    /**
     * 检测用户是否在黑名单中
     */
    public function blackCheck()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['userId'] = $this->getRequestParams('memberId'); //暂时保留，后续需要去掉
        $params['salePlatformType'] = !empty($arrParams['salePlatformType']) ? $arrParams['salePlatformType'] : SALE_PLATFORM_TYPE;
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret = $this->service('User')->blackCheck($params);
        $this->jsonOut($ret);
    }

    /**
     * 微信电影票支付页面调用，当支付查询朝伟可用优惠中没有返回V卡  获取推荐的会员卡
     * @return array
     */
    public function payVipCard()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if (empty($params['openId'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret = $this->service('User')->payVipCard($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取想看电影总数，喜欢影人总数，观影秘籍总数
     */
    public function getCounts()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $openid = $this->service('Login')->getOpenIdFromCookie();
        $unionId = ($params['channelId'] == 3) ? $this->service("Login")->getUnionIdFromCookie() : '';
        if (empty($openid) || ($params['channelId'] == 3 && empty($unionId))) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $params['openId'] = $openid;
        $params['ucid'] = $params['channelId'] == 3 ? $unionId : $openid;//想看用unionid，其他用openId
        $ret = $this->service('User')->getCounts($params);
        $this->jsonOut($ret);
    }

}
