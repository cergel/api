<?php

namespace app\services;

use app\base\BaseService;


class LoginService extends BaseService {

    const WXAPPOPENID = 'WxappOpenId';
    const OPEN_ID_EXPIRE = 3600;//openid 一小时
    private $appUserInfo = null;
    /**
     * 登陆入口
     * @param string $code
     * @param string $_client_redirect_
     * @param string $channelId
     * @return mixed
     */
    public function login($arrParams) {
        $arrRes = [];
        $arrParams['channelId'] = CHANNEL_ID;
        switch ($arrParams['channelId']) {
            case 3: //微信
                $arrParams['fromWx'] = 1;
                $arrRes = $this->sdk->call('wechat-login/login', $arrParams);
                if (!empty($arrRes['data']['openid'])) {
                    //userhash,前端会用它取末尾数字,做AB-test用
                    $arrRes['data']['userhash'] = $this->createHashString($arrRes['data']['openid']);
                }
                break;
            case 28: //手Q
                $arrParams['fromMqq'] = 1; //表示从手Q项目调用，会保存accessToken和cookie
                $arrRes = $this->sdk->call('mqq/login', $arrParams);
                if (isset($arrRes['data']['_client_redirect_'])) {
                    $arrRes['data']['redirectUrl'] = $arrRes['data']['_client_redirect_'];
                    unset($arrRes['data']['_client_redirect_']);
                }
                if (isset($arrRes['data']['openId'])) {
                    $arrRes['data']['openid'] = $arrRes['data']['openId'];
                    unset($arrRes['data']['openId']);
                }
                break;
            case 63:
            case 66:
            case 67:
            case 68:
            case 86:
                $arrParams['fromWx'] = 0;
                $arrRes = $this->sdk->call('wechat-login/wxapp-login', $arrParams);
                if (!empty($arrRes['data']['WxOpenId'])) {
                    $iExpireWxOpenId = time() + self::OPEN_ID_EXPIRE;
                    setcookie(self::WXAPPOPENID, $arrRes['data']['WxOpenId'], $iExpireWxOpenId, '/', '.wepiao.com');
                    $arrRes['data'][self::WXAPPOPENID] = $arrRes['data']['WxOpenId'];
                    unset($arrRes['data']['WxOpenId']);
                }
                break;
        }
        return $arrRes;
    }

    /**
     * 从cookie中读取openId，通用
     */
    public function getOpenIdFromCookie()
    {
        $mixOpenId = '';
        $arrParams['channelId'] = CHANNEL_ID;
        switch ($arrParams['channelId']) {
            case 3: //微信
                $arrParams['fromWx'] = 1;
                $arrRes = $this->sdk->call('wechat-login/get-openid-from-cookie', $arrParams);
                $mixOpenId = ( $arrRes['ret'] == 0 && !empty($arrRes['data']['openid']) ) ? $arrRes['data']['openid'] : '';
                break;
            case 28: //手Q
                $arrParams['fromMqq'] = 1; //表示从手Q项目调用，会保存accessToken和cookie
                $arrRes = $this->sdk->call('mqq/get-openid-from-cookie', $arrParams);
                $mixOpenId = ($arrRes['ret'] == 0 && !empty($arrRes['data']['openid'])) ? $arrRes['data']['openid'] : '';
                break;
            case 63:
            case 66:
            case 67:
            case 68:
            case 86:
                if (!empty($_COOKIE[self::WXAPPOPENID])) {
                    $mixOpenId = $this->checkCookieValue($_COOKIE[self::WXAPPOPENID]);
                } elseif (!empty($_REQUEST['WxappOpenId'])) {
                    $mixOpenId = $this->checkCookieValue($_REQUEST['WxappOpenId']);
                } else {
                    $mixOpenId = '';
                }
                break;
            case 8:
            case 9:
            case 80:
            case 84:
                $mixOpenId = $this->getAuthIdByToken();
                break;
        }
        return $mixOpenId;
    }

    /**
     * 从cookie中读取unionId，仅有微信使用
     */
    public function getUnionIdFromCookie()
    {
        $arrParams['channelId'] = CHANNEL_ID;
        $arrRes = $this->sdk->call('wechat-login/get-openid-from-cookie', $arrParams);
        $strUnionId = ( $arrRes['ret'] == 0 && !empty($arrRes['data']['unionid']) ) ? $arrRes['data']['unionid'] : '';

        return $strUnionId;
    }

    /**
     * userhash,前端会用它取末尾数字,做AB-test用。仅有微信使用
     */
    private function createHashString($str = '', $iMod = 10)
    {
        $key = crc32($str) % $iMod;

        return strtoupper(MD5($str)) . $key;
    }

    //check Cookie Value  微信小程序独有
    public function checkCookieValue($strCookieValue) {
        $arrParams['channelId'] = CHANNEL_ID;
        $arrParams['str'] = $strCookieValue;
        $arrRes = $this->sdk->call('common/decrypt', $arrParams);
        if ($arrRes['ret'] == 0) {
            $strOpenId = $arrRes['data']['decryptStr'];
        } else {
            $strOpenId = '';
        }
        return $strOpenId;
    }

    public function getUnionId($arrParams){
        $return = self::getStOut();
        $arrRes = $this->sdk->call('wechat-login/get-wxapp-user-token', $arrParams);
        if($arrRes['ret'] == 0) {
            $data = $this->decryptData($arrParams['encryptedData'], $arrParams['iv'],$arrRes['data']['access_token'])  ;
            if(intval($data) >= 0){
                $data = json_decode($data,true);
                if(isset($data['unionId'])){
                    \wyCupboard::$logger->addNode('decryptStr', $data);
                    $arrParams = array(
                        'openId'=>$data['openId'],
                        'nickName'=>$data['nickName'],
                        'subOtherId'=>3,
                        'otherId'=>11,
                        'photo'=>$data['avatarUrl'],
                        'unionId'=>$data['unionId'],
                        'channelId'=>CHANNEL_ID,
                    );
                    //通知靳松用户中心
                    $ret = $this->sdk->call('user/open-register', $arrParams);
                    return $ret;
                }else{
                    $return['ret']=$return['sub']=\app\helper\ErrorCode::$NoUnionId;
                    $return['msg']=\app\helper\ErrorCode::$NoUnionIdMsg;
                    $return['data'] = $data;
                    return $return;
                }
            }else{
                $return['ret']=$return['sub']=$return['msg']=$data;
                return $return;
            }
        }else{
            return $arrRes;
        }
    }

    /**
     * 获取unionId
     * @param $params
     * @return array
     */
    public function wxappGetUnionId($params){
        $return = self::getStOut();
        if($params['encryptedData']=='' || $params['iv']==''){
            $return = self::getErrorOut(ERRORCODE_USER_CENTER_PARAMS_ERROR);
        }
        else{
            $arrRes = $this->sdk->call('wechat-login/get-wxapp-user-token', ['openId'=>$params['openId'],'channelId'=>$params['channelId']]);
            if($arrRes['ret'] == 0 && isset($arrRes['data']['access_token'])) {
                $access_token=$arrRes['data']['access_token'];
                $data = $this->decryptData($params['encryptedData'], $params['iv'],$access_token)  ;
                if(intval($data) >= 0){
                    $data = json_decode($data,true);
                    if(isset($data['unionId'])){
                        $return['data']['unionId'] = $data['unionId'];
                    }
                    else{
                        $return = self::getErrorOut(ERRORCODE_USER_MISSS_UNIONID_ERROR);
                    }
                }
                else{
                    $return = self::getErrorOut(ERRORCODE_USER_MISSS_UNIONID_ERROR);
                }
            }
            else{
                $return = self::getErrorOut(ERRORCODE_USER_GET_ACCESSTOKEN_ERROR);
            }
        }
        return $return;
    }

    /**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @param $data string 解密后的原文
         *
	 * @return int 成功0，失败返回对应的错误码
	 */
	public function decryptData( $encryptedData, $iv ,$session_key='')
	{
            //$appid = 'wx4f4bc4dec97d474b1';
            if (strlen($session_key) != 24) {
                    return \app\helper\ErrorCode::$IllegalAesKey;
            }

            $session_key=base64_decode($session_key);
            $iv = base64_decode($iv);
            $encryptedData =base64_decode($encryptedData);
            $result = \app\helper\Prpcrypt::decrypt($encryptedData,$iv,$session_key);
            if ($result[0] != 0) {
                        return $result[0];
            }

            $dataObj=json_decode( $result[1] );
            if( $dataObj  == NULL )
            {
                return \app\helper\ErrorCode::$IllegalBuffer;
            }
//            if( $dataObj->watermark->appid != $appid )
//            {
//                //return \app\helper\ErrorCode::$IllegalBuffer;
//            }
            $data = $result[1];
            return $data;
	}

    /**
     *  App获取用户openId
     *  @return array 用户openId
     */
    private function getAuthIdByToken()
    {
        $userInfo = $this->getAppUserInfo();
        if (in_array(CHANNEL_ID, [8, 9])) {
            return $this->getParam($userInfo, "openId");
        }
        return $this->getParam($userInfo, "gewaraid");
    }

    /**
     *  解密Token
     *  @return array 用户信息
     */
    private function checkToken()
    {
        //解密用户的token如果解密成功日志记录用户的token
        $token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '';
        if ($token) {
            $param = [
                'channelId' => CHANNEL_ID,
                'str' => $token
            ];
            $ret = \wyCupboard::$sdk->call('common/decrypt', $param);
            if (isset($ret['ret']) && isset($ret['sub']) && $ret['ret'] == 0 && $ret['sub'] == 0) {
                $userInfo = json_decode($ret['data']['decryptStr'], true);
                $userInfo['valid'] = true;
            } else {
                $userInfo = ['openId' => '', 'uid' => '', 'unionId' => ''];
                $userInfo['valid'] = false;
            }
        } else {
            $userInfo = ['openId' => '', 'uid' => '', 'unionId' => ''];
            $userInfo['valid'] = false;
        }
        $this->setAppUserInfo($userInfo);
        \wyCupboard::$logger->addNode("user", $userInfo);
        return $userInfo;
    }

    /**
     *  App获取用户信息
     *  @return array 用户信息
     */
    public function getAppUserInfo()
    {
        return isset($this->appUserInfo) ? $this->appUserInfo : $this->checkToken();
    }

    /**
     * 设置App获取用户信息
     * @param $userInfo 用户信息
     * @return array 用户信息
     */
    public function setAppUserInfo($userInfo)
    {
        $this->appUserInfo = $userInfo;
    }

    /**
     * 通过token获取用户信息
     * @param $index
     * @return string
     */
    public function getAuthInfoByToken($index)
    {
        $user = $this->getAppUserInfo();
        $return = isset($user[$index]) ? $user[$index] : "";

        return $return;
    }

}
