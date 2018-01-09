<?php

namespace app\controllers;

use app\base\BaseController;

class PointCardController extends BaseController
{
    
    /**
     * 获取点卡信息
     */
    public function getPointCardInfo($cardPass = '')
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;  //渠道编号
        $param['mobile'] = $this->getRequestParams('mobile', '');
        $param['cardPass'] = $cardPass;
        $param['orderId'] = $this->getRequestParams('orderId', ''); //经度
        $param['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $param['subChannelId'] = WX_SALE_APP_ID;
        //参数校验
        if (empty( $param['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $param['cardPass'] )) {
            $ret = self::getErrorOut(ERRORCODE_POINT_CARD_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('PointCard')->getPointCardInfo($param);
        }
        $this->jsonOut($ret);
    }
    
    
}