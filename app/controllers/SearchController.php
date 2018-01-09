<?php

namespace app\controllers;

use app\base\BaseController;

class SearchController extends BaseController
{
    /**
     * 根据关键字搜索电影/影院
     * 目前只开发了影院搜索，没有开发影片搜索
     */
    public function keywords()
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;  //渠道编号
        $param['keyword'] = $this->getRequestParams('keyword', '');
        $param['cityId'] = $this->getRequestParams('cityId', '10'); //城市
        $cityName = $this->service("Search")->CityId2Name(CHANNEL_ID, $param['cityId']);
        $param['cityName'] = $cityName; //城市名称
        $param['longitude'] = $this->getRequestParams('longitude'); //经度
        $param['latitude'] = $this->getRequestParams('latitude'); //纬度
        $param['jsonpCallback'] = $this->getRequestParams("jsonpCallback"); //jsonp的回调函数名
        $param['pageNum'] = $this->getRequestParams('pageNum', 1);
        $param['pageSize'] = $this->getRequestParams('pageSize', 10);
        $param['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //目前只接入影院搜索，影片跟演出搜索暂时不加入
        if (empty( $param['longitude'] )) {
            unset( $param['longitude'] );
        }
        if (empty( $param['latitude'] )) {
            unset( $param['latitude'] );
        }
        $arrReturn = $this->service('Search')->searchCinema($param);
        if (empty( $param['jsonpCallback'] )) {
            $this->jsonOut($arrReturn);
        }
        else {
            $this->jsonpOut($arrReturn, $param['jsonpCallback']);
        }
    }
    
    /**
     * 综合搜索
     * 搜影片、影院、以及周边商城信息等
     */
    public function more()
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;  //渠道编号
        $param['keys'] = $this->getRequestParams('keyword', '');
        $param['city'] = $this->getRequestParams('cityId', '10'); //城市
        $param['lon'] = $this->getRequestParams('longitude', ''); //经度
        $param['lat'] = $this->getRequestParams('latitude', ''); //纬度
        $param['more'] = $this->getRequestParams('more', '');
        $param['pindex'] = $this->getRequestParams('page', 1);
        $param['pcount'] = $this->getRequestParams('num', 10);
        $param['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //参数校验
        if (empty( $param['keys'] )) {
            $ret = self::getErrorOut(ERRORCODE_BIG_DATA_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('Search')->searchMore($param);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 搜索推荐
     */
    public function recommend()
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;  //渠道编号
        
        $ret = $this->service('Search')->searchRecommend($param);
        $this->jsonOut($ret);
    }
    
    
}