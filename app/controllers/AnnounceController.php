<?php

namespace app\controllers;

use app\base\BaseController;

class AnnounceController extends BaseController
{
    /**
     * 获取公告
     *
     * @param $cityId
     */
    public function getInfo()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['position'] = $this->getRequestParams('position', 0);
        $params['cinemaId'] = $this->getRequestParams('cinemaId', '');
        $params['movieId'] = $this->getRequestParams('movieId', 0);
        if (empty($params['position'])) {
            $ret=$this->getErrorOut(ERRORCODE_MISS_ARGUMENT);
        }
        else{
            $ret = $this->service('Announce')->getInfo($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 用户简略信息统一接口
     * 用于首页等展示的简略信息，如红包数量，最近观影影院等
     * @params string $openId 用户第三方账户，从cookie中取
     * @params string $channelId 渠道编号，目前支持手Q28与微信3
     * @params string $type 获取功能，用逗号分隔，如“1,2”
     * 功能列表，需要的填入:
     * 1 获取最近观影影院last_cinema
     * 2 获取可用红包数量bonus

     */
    public function getSimpleInfo()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $this->getRequestParams('movieId', '');
        $params['type'] = $this->getRequestParams('type', '1,2');   //1表示最近观影影院,2表示红包数量, 如果要查多种,则传入类似于: '1,2'
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Announce')->getSimpleInfo($params);
        }
        
        $this->jsonOut($ret);
    }


}