<?php

namespace app\controllers;

use app\base\BaseController;

class CinemaVipController extends BaseController
{
    /**
     * 获取会员卡列表
     *
     * @param $cityId
     */
    public function getCardList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $this->getRequestParams('cityId');
        $params['cinemaId'] = $this->getRequestParams('cinemaId');
        $ret = $this->service('CinemaVip')->getCardList($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取会员卡列表
     * 通过 cityId 查, 支持分页
     *
     * @param $cityId
     */
    public function getCityCardList($cityId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['pageNum'] = $this->getRequestParams('pageNum', '1');
        $params['pageSize'] = $this->getRequestParams('pageSize', '10');
        $params['more'] = intval($this->getRequestParams('more', 0));
        $params['promotionId'] = $this->getRequestParams('promotionId');
        $ret = $this->service('CinemaVip')->getCityCardList($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取用户已有的会员卡列表
     */
    public function getUserCardList()
    {
        //获取需要的参数
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['cardNo'] = $this->getRequestParams('cardNo');
        $params['cinemaId'] = $this->getRequestParams('cinemaId');
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service('CinemaVip')->getUserCardList($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取用户已有的会员卡列表
     * 这个是 我的-会员卡 页面, 查询用户所有的会员卡列表
     */
    public function getUserCardListByPage()
    {
        //获取需要的参数
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['pageNum'] = $this->getRequestParams('pageNum', '1');
        $params['pageSize'] = $this->getRequestParams('pageSize', '10');
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('CinemaVip')->getUserCardListByPage($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 会员卡详情
     */
    public function getCardInfo($typeId = '', $subTypeId = '')
    {
        //获取需要的参数
        $params = [];
        $params['typeId'] = $typeId;
        $params['subTypeId'] = $subTypeId;
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('CinemaVip')->getCardInfo($params);
        $this->jsonOut($ret);
    }

    /**
     * 会员卡支付
     *
     * @param  string  memberCardInfo 会员卡信息,数组类想的json串
     */
    public function payment()
    {
        //获取需要的参数
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['phone'] = $this->getRequestParams('phone');
        $params['cityId'] = $this->getRequestParams('cityId');
        $params['memberCardInfo'] = $this->getRequestParams('memberCardInfo');
        $params['channelId'] = CHANNEL_ID;
        $params['orderSource'] = $this->service('User')->getSubChannelId();//子渠道，如8020000000
        $params['payType'] = $this->getRequestParams("type");//App支付必须
        //是否是折扣卡3n活动
        $params['actType'] = $this->getRequestParams('actType');
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            if(in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])){
                //格瓦拉专属参数
                $params['reqSource'] = $this->getRequestParams("reqSource", '');
                $params['merchantCode'] = $this->getRequestParams("merchantCode", '');
                $params['bankCode'] = $this->getRequestParams("bankCode", '');
                $ret = $this->service('CinemaVip')->gwlPayment($params);
            }else{
                $ret = $this->service('CinemaVip')->payment($params);
            }
        }
        $this->jsonOut($ret);
    }

    //我的会员卡页面
    public function showMyCard()
    {
        $channelId = $this->getRequestParams("channelId");
        $token = $this->getRequestParams("token");
        $typeId = $this->getRequestParams("typeId");
        $subTypeId = $this->getRequestParams("subTypeId");
        $cardNo = $this->getRequestParams("cardNo", "");
        $cityId = $this->getRequestParams("cityId");
        if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            $_SERVER['HTTP_TOKEN'] = $token;
            $user = $this->service('Login')->getAppUserInfo();
            $openId = $this->service('Login')->getOpenIdFromCookie();
        } elseif (empty($token)) {
            exit("login!");
        } else {
            $user = $this->service("User")->getOpenIdFromStr($channelId, $token);
            $openId = $user['data'];
        }
        //如果没设置卡号切设置typeId和subTypeId则查询出卡号并且跳转到详情
        if (empty($cardNo) && !empty($typeId) && !empty($subTypeId)) {
            $params = [];
            $params['openId'] = $openId;
            $params['channelId'] = $channelId;
            $return = $this->sdk->call("cinema-vip/get-user-card-list", $params);
            if ($return['ret'] == 0 && $return['sub'] == 0) {
                $list = isset($return['data']['list']) ? $return['data']['list'] : [];
            }
            foreach ($list as $card) {
                if ($card['typeId'] == $typeId && $card['subTypeId'] == $subTypeId) {
                    $cardNo = $card['cardNo'];
                    break;
                }
            }
        }
        if(APP_ENV == 'dev'){
            setcookie("__env__", "appcinemavip_dev", strtotime("+1 days"), "/", ".wepiao.com");
        }elseif (APP_ENV == 'pre')
            setcookie("__env__", "appcinemavip", strtotime("+1 days"), "/", ".wepiao.com");
        setcookie("cityid", $cityId, strtotime("+30 days"), " / ", " .wepiao.com");
        $cityName = $this->service("Search")->CityId2Name("9", $cityId);
        setcookie("cityname", $cityName, strtotime("+30 days"), "/", ".wepiao.com");
        if ($cardNo) {
            $params = [];
            $params['page'] = "detail";
            $params['cardno'] = $cardNo;
            $params['token'] = $token;
            $params['channelId'] = $channelId;
        } else {
            $params = [];
            $params['page'] = "myvipcards";
            $params['cardno'] = $cardNo;
            $params['token'] = $token;
            $params['channelId'] = $channelId;
        }
        $url = "https://vip.wepiao.com/appcinemavip?" . http_build_query($params);
        header("Location: {$url}");
    }


    public function jump()
    {
        if(APP_ENV == 'pre'){
            setcookie("__env__", "appcinemavip", strtotime("+1 days"), "/", ".wepiao.com");
        }elseif(APP_ENV == 'dev'){
            setcookie("__env__", "appcinemavip_dev", strtotime("+1 days"), "/", ".wepiao.com");
        }
        $url = str_replace(" ", "+", $this->getRequestParams("url"));
        $url = strip_tags(base64_decode($url));


        if (!empty($url)) {
            $arr = [];
            parse_str($url, $arr);
            //如果url参数里有cityId则cookie中写入城市名称
            if (isset($arr['cityid'])) {
                $cityId = $arr['cityid'];
                $cityName = $cityName = $this->service("Search")->CityId2Name("9", $cityId);
                setcookie("cityid", $cityId, strtotime("+1 days"), "/", ".wepiao.com");
                setcookie("cityname", $cityName, strtotime("+1 days"), "/", ".wepiao.com");
            }
            echo "<head><meta charset=\"utf-8\"><title>折扣卡</title></head><script>window.location.href='" . $url . "'</script>";
        }
    }

    /**
     * 检测用户是否已经买过某个折扣卡
     */
    public function checkUserBuy($typeId = '')
    {
        //获取需要的参数
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['typeId'] = $typeId;
        $params['channelId'] = CHANNEL_ID;
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } elseif (empty($params['typeId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_PARAM);
        } else {
            $ret = $this->service('CinemaVip')->checkUserBuy($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 检测用户是否已经买过某个折扣卡
     */
    public function getMergeSimpleInfo($cinemaId = '')
    {
        //获取需要的参数
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['cinemaId'] = $cinemaId;
        $params['channelId'] = CHANNEL_ID;
        $return = $this->sdk->call("cinema-vip/get-merge-vip-card-info", $params);
        $this->jsonOut($return);
    }
    
}
