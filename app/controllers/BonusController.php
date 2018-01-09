<?php

namespace app\controllers;

use app\base\BaseController;

class BonusController extends BaseController
{
    private $bind_bonus_info=array(
        '1'=>['id'=>1242,'status'=>1],
        '5'=>['id'=>1242,'status'=>1],
        '6'=>['id'=>1171,'status'=>1],
    );



    /**
     * 查询可用优惠信息
     */
    public function getBonus()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['salePlatformType'] = SALE_PLATFORM_TYPE;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 1000);
        $params['userId'] = "";
        $params['scheduleId'] = $this->getRequestParams('scheduleId', '');//排期
        $params['ticket'] = $this->getRequestParams('ticket', 0);//购买的电影票数量
        $params['cinemaId'] = $this->getRequestParams('cinemaId', 0);//影院id
        $params['sTempOrderID'] = $this->getRequestParams('orderId');//临时订单id
        $params['invalidFlg'] = $this->getRequestParams('invalidFlg', 1);//返回订单不可用红包列表标记
        $params['saleObjType'] = $this->service('User')->getSubChannelId();
        //朋友的券需要的参数
        $params['cardId'] = $this->getRequestParams('cardId', '');
        $params['encyCode'] = $this->getRequestParams('encyCode', '');
        //获取手机号
        $params['phone'] = $this->getRequestParams('phone', '');
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Bonus')->getBonus($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 查询未支付订单、并获取可用优惠信息
     */
    public function getBonusV2()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        //$params['salePlatformType'] = WX_MOVIE_APP_ID;
        $params['salePlatformType'] = 1;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['appId'] = $this->getRequestParams('iAppId', 1);
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['phone'] = $this->getRequestParams('phone', '');
        $params['scheduleId'] = $this->getRequestParams('scheduleId', '');//排期
        $params['invalidFlg'] = $this->getRequestParams('invalidFlg', 0);
        $params['tktCnt'] = $this->getRequestParams('ticket', 0);//购买的电影票数量
        $params['cinemaId'] = $this->getRequestParams('cinemaId', 0);//影院id
        $params['sTempOrderID'] = $this->getRequestParams('orderId');//临时订单id
        $params['invalidFlg'] = $this->getRequestParams('invalidFlg', 1);//是否返回不可用优惠，默认返回
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $userAuthInfo = $this->service('User')->getUserChannelAuthInfo();
            $params = array_merge($params, $userAuthInfo);
            $params['saleObjType'] = $params['iSubChannelId'];
            $ret = $this->service('Bonus')->getBonusV2($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 绑定手机领取红包
     */
    public function getBindBonus(){
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['channelId'] = CHANNEL_ID;
        $params['phone'] = $this->getRequestParams('phone', '');
        $params['deviceId'] = $this->getRequestParams("DeviceId", '');
        $params['imei'] = $this->getRequestParams("imei", '');
        $params['appver']=$this->getRequestParams("appver",'');
        $params['value'] = $this->getRequestParams('value', '');//红包金额
        $bonusPosition=$this->getRequestParams('bonusPosition', '');//领取红包位置
        $bind_bonus_info=$this->bind_bonus_info;
        if(empty($bonusPosition) || !isset($bind_bonus_info[$bonusPosition])){
            $ret = self::getErrorOut(ERRORCODE_BIND_BONUS_RES_ERROR);
        }
        elseif (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $userAuthInfo = $this->service('User')->getUserChannelAuthInfo();
            $params = array_merge($params, $userAuthInfo);
            $params['subChannelId'] = $params['iSubChannelId'];
            $ret = \wyCupboard::$sdk->call('bonus/get-suit-bonus', $params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取绑定手机领取红包红包的信息
     */
    public function getBindBonusInfo(){
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['channelId'] = CHANNEL_ID;
        $bonusPosition=$this->getRequestParams('bonusPosition', '');//领取红包位置
        $bind_bonus_info=$this->bind_bonus_info;
        $suitId="";
        $ret=[];
        //如果以逗号分割的红包位置，则去判断每个红包资源是否存在
        if(strpos($bonusPosition,',')){
            $newBonusPosition=explode(',',$bonusPosition);
            foreach($newBonusPosition as $val){
                if(!isset($bind_bonus_info[$val]) || !isset($bind_bonus_info[$val]['id'])){
                    $ret = self::getErrorOut(ERRORCODE_BIND_BONUS_RES_ERROR);
                    break;
                }
                else{
                    $suitId=empty($suitId)?$bind_bonus_info[$val]['id']:$suitId.','.$bind_bonus_info[$val]['id'];
                }
            }
        }
        else{
            if(empty($bonusPosition) || !isset($bind_bonus_info[$bonusPosition]) || !isset($bind_bonus_info[$bonusPosition]['id'])){
                $ret = self::getErrorOut(ERRORCODE_BIND_BONUS_RES_ERROR);
            }
            $suitId=$bind_bonus_info[$bonusPosition]['id'];
        }
        if(!empty($ret)){
            $this->jsonOut($ret);
        }
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $params['suitId'] = $suitId;//套装红包id
            $params['bonusPosition'] = $bonusPosition;//套装红包位置
            $ret = \wyCupboard::$sdk->call('bonus/suit-bonus-info', $params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 查询所有有优惠信息，包含可用不可用
     */
    public function getAllBonus()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        //$params['salePlatformType'] = WX_MOVIE_APP_ID;
        $params['salePlatformType'] = 1;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['appId'] = $this->getRequestParams('iAppId', 1);
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['scheduleId'] = $this->getRequestParams('scheduleId', '');//排期
        $params['sTempOrderID'] = $this->getRequestParams('orderId');//临时订单id
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 1);
        $params['invalidFlg'] = $this->getRequestParams('invalidFlg', 1);//是否返回不可用优惠，默认不返回
        $params['phone'] = $this->getRequestParams('phone', '');//手机号
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $userAuthInfo = $this->service('User')->getUserChannelAuthInfo();
            $params = array_merge($params, $userAuthInfo);
            $params['saleObjType'] = $params['iSubChannelId'];
            $ret = $this->service('Bonus')->getAllBonus($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 优惠到人接口
     */
    public function discount()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Bonus')->discount($params);
        }
        $this->jsonOut($ret);
    }

    // 微信小程序独有首页获取红包
    public function getStatus()
    {
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Bonus')->getStatus($params);
        $this->jsonOut($ret);
    }

    /**
     * 格瓦拉用户个人中心获取可用优惠信息
     *
     * $params
     * queryType : 查询类型（0：格瓦拉查询所有票券，1：红包，2：选座卷，3：现金券）
     */
    public function getUserBonusList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 1);
        $params['iPlatForm'] = $this->getRequestParams('iPlatForm', 1);
        $params['subChannelId'] = $this->getRequestParams('from', '');
        $params['queryType'] = $this->getRequestParams('queryType', 0);
        $params['status'] = $this->getRequestParams('status', 0);
        $ret = $this->service('Bonus')->getUserBonusList($params);
        $this->jsonOut($ret);
    }


    /**
     * 格瓦拉渠道支付页查询可用票券&运营特价活动&银行特价活动
     * params
     * mpId 必传，场次
     * ordId 订单ID
     * tktCnt
     */
    public function getPayBonusList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }

        $params['subChannelId'] = $this->getRequestParams('from', '');
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 1000);
        $params['iPlatForm'] = $this->getRequestParams('iPlatForm', 1);
        $params['scheduleId'] = $this->getRequestParams('scheduleId');
        $params['orderId'] = $this->getRequestParams('orderId', '');
        $params['tktCnt'] = $this->getRequestParams('ticket', 0);//购买的电影票数量
        $params['cinemaId'] = $this->getRequestParams('cinemaId', 0);//影院id

        $deviceId = $this->getRequestParams('deviceId', '');
        if (empty($deviceId)) {
            $deviceId = $this->getRequestParams('ifda', '');
        }
        $params['deviceid'] = $deviceId;
        $params['imei'] = $this->getRequestParams('imei', '');//IMEI
        $params['invalidFlg'] = $this->getRequestParams('invalidFlg', 0);//返回订单不可用红包列表标记
        $params['mobile'] = $this->getRequestParams('mobile');
        $ret = $this->service('Bonus')->getPayBonusList($params);
        $this->jsonOut($ret);
    }

    /**
     * 兑换码兑换
     */
    public function exchangeCode()
    {
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['code'] = $this->getRequestParams('code');
        $params['platId'] = $this->getRequestParams('plateId');
        $params['channelId'] = $this->getRequestParams('channelId');
        $params['subChanId'] = $this->getRequestParams('from');
        $params['mobile'] = $this->getRequestParams('mobile');
        $ret = $this->service('Bonus')->exchangeCode($params);
        $this->jsonOut($ret);
    }


    /**
     * v卡列表
     */
    public function vcardList()
    {
        $params['suin'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['suin'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['status'] = $this->getRequestParams('status');
        $params['mpid'] = $this->getRequestParams('scheduleId');
        $params['channelId'] = $this->getRequestParams('channelId');
        $params['sub_chanid'] = $this->getRequestParams('from');
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 10);
        $ret = $this->service('Bonus')->getVcardList($params);
        $this->jsonOut($ret);
    }

    /**
     * 激活V卡
     */
    public function vcardActive()
    {
        $params['suin'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['suin'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['code'] = $this->getRequestParams('code');
        $params['plat_id'] = $this->getRequestParams('plat_id');
        $params['channelId'] = $this->getRequestParams('channelId');
        $params['sub_chanid'] = $this->getRequestParams('from');
        $params['verify_code'] = $this->getRequestParams('verifyCode');
        //验证验证码是否正确
        $arrInput['code'] = $params['verify_code'];
        $arrInput['id'] = $params['suin'];
        $arrInput['channelId'] = $params['channelId'];
        $checkResult = $this->service('Security')->verifyPictureCode($arrInput);
        if ($checkResult['ret'] == 0 && $checkResult['sub'] == 0 && $checkResult['data'] == 1) {
            $ret = $this->service('Security')->clearVerifyCodeDate($arrInput);
        } else {
            //验证失败
            $this->jsonError(ERRORCODE_VERIFY_CODE_FAIL, '验证码输入有误');
        }
        unset($params['verify_code']);
        $ret = $this->service('Bonus')->activeGewaraVcard($params);
        $this->jsonOut($ret);
    }

    public function vcardInfo()
    {
        $params['suin'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty($params['suin'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
            $this->jsonOut($ret);
        }
        $params['card_no'] = $this->getRequestParams('card_no');
        $ret = $this->service('Bonus')->vcardInfo($params);
        $this->jsonOut($ret);
    }

}