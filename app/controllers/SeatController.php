<?php

namespace app\controllers;

use app\base\BaseController;

class SeatController extends BaseController
{
    /**
     * 获取座位图
     *
     * @param $cityId
     */
    public function getSeats($cinemaId, $roomId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cinemaId'] = $cinemaId;
        $params['roomId'] = $roomId;
        $params['movieId'] = $this->getRequestParams('movieId', 0);
        $ret = $this->service('Cinema')->getCinemaSeats($params);
        $this->jsonOut($ret);
    }
    
    //锁坐接口
    public function lockSeat()
    {
        $params = [];
        $params['phone'] = $this->getRequestParams("phone", '');
        $params['saleObjType'] = $this->service('User')->getSubChannelId();//子渠道，如8020000000
        $params['appId'] = $this->getRequestParams('iAppId', WX_MOVIE_APP_ID);
        $params['schedulePricingId'] = $this->getRequestParams("scheduleId", '');//以前的mpid参数，如76246001，影院排期ID
        $params['salePlatformType'] = $this->getRequestParams("salePlatformType", SALE_PLATFORM_TYPE);//售卖平台
        $params['ticket'] = $this->getRequestParams("ticket", 1);//票数
        $params['userId'] = $this->getRequestParams('uid', '');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['seatlable'] = $this->getRequestParams("seatlable", '');//座位标识，如8:12
        $params['seatlable'] = !empty($params['seatlable']) ? self::_formatStr($params['seatlable']) : '';  //参数转换
        $params['channelId'] = CHANNEL_ID;//渠道
        $params['cinemaNo'] = $this->getRequestParams("cinemaId", '');
        $params['bisServerId'] = $this->getRequestParams("bisServerId", '');
        $active_id = $this->getRequestParams("active_id", 0);
        $team_id = $this->getRequestParams("team_id", 0);
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $params['spm'] = $this->getSpmParam();
            $params['remarkJson'] = json_encode(['active_id' => $active_id, 'team_id' => $team_id]);
            $ret = $this->service("Seat")->lockSeat($params);
        }
        $this->jsonOut($ret);
    }
    
    //锁坐接口
    public function lockSeatV2()
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;//渠道
        $params['scheduleId'] = $this->getRequestParams("scheduleId", '');//以前的mpid参数，如76246001，影院排期ID
        $params['seatlable'] = $this->getRequestParams("seatlable", '');//座位标识，如8:12
        $params['seatlable'] = !empty( $params['seatlable'] ) ? self::_formatStr($params['seatlable']) : '';    //格式化锁座参数
        $params['cinemaNo'] = $this->getRequestParams("cinemaId", '');
        $params['movieId'] = $this->getRequestParams("movieId", '');
        $params['subChannelId'] = $this->service('User')->getSubChannelId();//子渠道，如8020000000
        $params['phone'] = $this->getRequestParams("phone", '');
        $params['spm'] = $this->getSpmParam();
        $params['seatLockType'] = $this->getRequestParams("seatLockType");//锁座类型,如果是改签,传1
        $params['fromOrderId'] = $this->getRequestParams("fromOrderId", '');//票数
        $params['unionOpenId'] = $this->getRequestParams('unionOpenId', '');
        $params['cardNo'] = $this->getRequestParams('cardNo', '');  //会员卡id
        $params['bisServerId'] = $this->getRequestParams('bisServerId', '');
        $active_id = $this->getRequestParams("active_id", "");
        $team_id = $this->getRequestParams("team_id", "");
        $params['remarkJson'] = json_encode(array('active_id' => $active_id, 'team_id' => $team_id));
        $params['ticket'] = (int)$this->getRequestParams('ticket', ''); //购票张数
        $params['cityId'] = $this->getRequestParams('cityId', '');
        //fromOrderId有值,fromOpenId才能有值
        if (empty( $params['fromOrderId'] )) {
            $params['unionOpenId'] = '';
        }
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            //会员卡逻辑如果有会员卡则自动填写卡号
            if (!empty($params['cinemaNo']) && !empty($params['cityId'])) {
                $cardParams = [];
                $cardParams['cityId'] = $params['cityId'];
                $cardParams['channelId'] = $params['channelId'];
                $cardParams['cinemaId'] = $params['cinemaNo'];
                $cardParams['openId'] = $params['openId'];
                $cardParams['buySource'] = 2;
                $cardInfo = $this->service("Cinema")->CinemaVipCard($cardParams);
                if (is_array($cardInfo['data']) && isset($cardInfo['data']['cardInfo']['cardNo']) && !empty($cardInfo['data']['cardInfo']['cardNo']) && $cardInfo['data']['status'] == "4") {
                    if ($cardInfo['data']['cardInfo']['intervalLeft'] >= $params['ticket']) {
                        $params['cardNo'] = $cardInfo['data']['cardInfo']['cardNo'];
                        $cardInfo['data']['cardInfo']['useCard'] = 1;
                        $cardInfo['data']['cardInfo']['intervalLeft'] = $cardInfo['data']['cardInfo']['intervalLeft'] - $params['ticket'];
                    } else {
                        $cardInfo['data']['cardInfo']['useCard'] = 0;
                    }
                }
            }
            $ret = $this->service("Seat")->lockSeatV2($params);
            if ($ret['ret'] == 0 && $ret['sub'] == 0) {
                $ret['seatinfo']['card'] = isset($cardInfo['data']) ? $cardInfo['data'] : new \stdClass();
                //处理APP非有偿退票信息
                if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                    $this->service("CinemaRefund")->refundLockSeat($params['cinemaNo'], $ret);
                    $ret['seatinfo']['discountMessage'] = $this->service("Resource")->alertDiscountMessage($ret['seatinfo']['iUnitPrice']);
                }
            }
        }
        $this->jsonOut($ret);
    }
    
    /*
     * $strFlag 1 -减少  2-增加
     * in ->1:5:07|1:6:05 out->5:07|6:05
     * in -> 5:07|6:05    out->1:5:07|1:6:05
     */
    private function _formatStr($str, $strFlag = 2, $note = "|")
    {
        $arrTmp = explode($note, $str);
        $arr = [];//1:4:08 去掉1  C++那边不要 区
        foreach ($arrTmp as $strV) {
            $arrTmp_2 = explode(":", $strV);
            if ($strFlag == 1) {
                unset($arrTmp_2[0]);
            } elseif (count($arrTmp_2) < 3) {
                array_unshift($arrTmp_2, "01");
            }
            $arr[] = implode(":", $arrTmp_2);
        }
        
        return implode($note, $arr);
    }
    
}