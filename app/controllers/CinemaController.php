<?php

namespace app\controllers;

use app\base\BaseController;

class CinemaController extends BaseController
{
    /**
     * 获取影院列表
     *
     * @param $cityId
     */
    public function getList($cityId)
    {
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $ret = $this->service('Cinema')->getCinemaList($params);
        $this->jsonOut($ret);
    }
    
    /**
     * 从大数据获取影院列表
     * 手q影院列表使用
     *
     * @param $cityId
     */
    public function getListFromEs($cityId)
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $cityId;
        $params['longitude'] = $this->getRequestParams('longitude', 999);
        $params['latitude'] = $this->getRequestParams('latitude', 999);
        $params['sortField'] = $this->getRequestParams('sortField', 'distance');
        $params['order'] = $this->getRequestParams('order', 'asc');
        $params['brand'] = $this->getRequestParams('brand');
        $params['special'] = $this->getRequestParams('special');
        $params['pageNum'] = $this->getRequestParams('pageNum');
        $params['pageSize'] = $this->getRequestParams('pageSize');
        $params['card'] = $this->getRequestParams('card');
        $params['label'] = $this->getRequestParams('label');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Cinema')->getCinemaListFromEs($params);
        $this->jsonOut($ret);
    }
    
    /**
     * 从大数据获取影院列表
     * 手q影院列表使用
     *
     * @param $cityId
     */
    public function getListFromEsV2($cityId)
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $cityId;
        $params['longitude'] = $this->getRequestParams('longitude', 999);
        $params['latitude'] = $this->getRequestParams('latitude', 999);
        $params['sortField'] = $this->getRequestParams('sortField', 'distance');
        $params['order'] = $this->getRequestParams('order', 'asc');
        $params['brand'] = $this->getRequestParams('brand');
        $params['special'] = $this->getRequestParams('special');
        $params['pageNum'] = $this->getRequestParams('pageNum');
        $params['pageSize'] = $this->getRequestParams('pageSize');
        $params['card'] = $this->getRequestParams('card');
        $params['label'] = $this->getRequestParams('label');
        $params['serv'] = $this->getRequestParams('serv');
        $params['minPrice'] = $this->getRequestParams('minPrice');
        $params['maxPrice'] = $this->getRequestParams('maxPrice');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Cinema')->getCinemaListFromEsV2($params);
        $this->jsonOut($ret);
    }
    
    /**
     * 影院搜索列表
     * 通过调用sdk（其实也是调用大数据的接口）的方式
     *
     * @param string cityId     城市id
     * @param string movieId    影片id
     */
    public function searchFilter()
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $this->getRequestParams('cityId');
        $params['movieId'] = $this->getRequestParams('movieId');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Cinema')->readSearchCinemaFilter($params);
        $this->jsonOut($ret);
    }

    /**
     * 影院搜索过滤——V2版本(返回值增加服务数据)
     * 通过调用sdk（其实也是调用大数据的接口）的方式
     *
     * @param string cityId     城市id
     * @param string movieId    影片id
     */
    public function searchFilterV2()
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $this->getRequestParams('cityId');
        $params['movieId'] = $this->getRequestParams('movieId');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Cinema')->readSearchCinemaFilterV2($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取在映某个影片的影院列表
     *
     * @param $cityId
     * @param $movieId
     */
    public function getListByMovieFromEs($cityId, $movieId)
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $cityId;
        $params['longitude'] = $this->getRequestParams('longitude', 999);
        $params['latitude'] = $this->getRequestParams('latitude', 999);
        $params['sortField'] = $this->getRequestParams('sortField', 'distance');
        $params['order'] = $this->getRequestParams('order', 'asc');
        $params['brand'] = $this->getRequestParams('brand');
        $params['special'] = $this->getRequestParams('special');
        $params['pageNum'] = $this->getRequestParams('pageNum', 1);
        $params['pageSize'] = $this->getRequestParams('pageSize', 20);
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['date'] = $this->getRequestParams('date', date('Y-m-d'));
        $params['movieId'] = $movieId;
        $params['recent'] = $this->getRequestParams('recent');
        $params['lable'] = $this->getRequestParams('lable');
        $params['card'] = $this->getRequestParams('card');
        $ret = $this->service('Cinema')->getCinemaListByMovieFromEs($params);
        $this->jsonOut($ret);
    }
    
    /**
     * 获取在映某个影片的影院列表
     *
     * @param $cityId
     * @param $movieId
     */
    public function getListByMovieFromEsV2($cityId, $movieId)
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cityId'] = $cityId;
        $params['longitude'] = $this->getRequestParams('longitude', 999);
        $params['latitude'] = $this->getRequestParams('latitude', 999);
        $params['sortField'] = $this->getRequestParams('sortField', 'distance');
        $params['order'] = $this->getRequestParams('order', 'asc');
        $params['brand'] = $this->getRequestParams('brand');
        $params['special'] = $this->getRequestParams('special');
        $params['pageNum'] = $this->getRequestParams('pageNum', 1);
        $params['pageSize'] = $this->getRequestParams('pageSize', 20);
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['date'] = $this->getRequestParams('date', date('Y-m-d'));
        $params['movieId'] = $movieId;
        $params['recent'] = $this->getRequestParams('recent');
        $params['lable'] = $this->getRequestParams('lable');
        $params['card'] = $this->getRequestParams('card');
        $params['serv'] = $this->getRequestParams('serv');
        $params['minPrice'] = $this->getRequestParams('minPrice');
        $params['maxPrice'] = $this->getRequestParams('maxPrice');
        $ret = $this->service('Cinema')->getCinemaListByMovieFromEsV2($params);
        $this->jsonOut($ret);
    }
    
    /**获取影院详情
     *
     * @param $cinemaId
     */
    public function getInfo($cinemaId)
    {
        //获取需要的参数
        $params = [];
        $params['cinemaId'] = $cinemaId;
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Cinema')->getCinemaInfo($params);
        if ($ret['ret'] == 0 && $ret['sub'] == 0 && in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
            //添加格瓦拉影院id
            $this->service("Cinema")->_formatAddGewaraCinemaId($ret['data']);
            $this->service("Cinema")->_formatAddGewaraCinemaPaper($ret['data']);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 支持某个会员卡的
     *
     * @param int $typeId 会员卡主类型id
     */
    public function getCardCinemaList($typeId)
    {
        //获取需要的参数
        $params = [];
        $params['areaId'] = $this->getRequestParams('areaId');
        $params['cardTypeId'] = $typeId;
        $params['longitude'] = $this->getRequestParams('longitude', 999);
        $params['cityId'] = $this->getRequestParams('cityId', '');
        $params['latitude'] = $this->getRequestParams('latitude', 999);
        $params['sortField'] = $this->getRequestParams('sortField', 'asc');
        $params['order'] = $this->getRequestParams('order', 'desc');
        $params['pageNum'] = $this->getRequestParams('pageNum');
        $params['pageSize'] = $this->getRequestParams('pageSize');
        $params['card'] = $this->getRequestParams('card');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Cinema')->getCardCinemaList($params);
        $this->jsonOut($ret);
    }

    //影院单独购买小吃
    public function cinemaSnackPayment($cinemaId)
    {
        $snackInfos = json_decode($this->getRequestParams("snackInfos"), true);
        if (!$snackInfos) {
            $snackInfos = [];
        }
        $param = [];
        $param['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $param['channelId'] = $this->getRequestParams("channelId");
        $param['payType'] = $this->getRequestParams("payType");
        $param['phone'] = $this->getRequestParams("phone");
        $param['cityNo'] = $this->getRequestParams("cityId");
        $param['snackInfos'] = $snackInfos;
        $param['cinemaNo'] = $cinemaId;
        $param['tradeType'] = 'APP';
        $param['orderSource'] = APP_ID;
        $resopnse = $this->sdk->call("pay/snack", $param);
        $this->jsonOut($resopnse);
    }

    //融合版会员卡接口[排期页会员卡状态]
    public function CinemaVipCard($cinemaId)
    {
        $params = [];
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['cinemaId'] = $cinemaId;
        $params['buySource'] = 3;
        $params['cityId'] = $this->getRequestParams("cityId", 10);
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $response = $this->service("Cinema")->CinemaVipCard($params);
        $this->jsonOut($response);
    }
    /**
     * 格瓦拉新放映
     */
    public function GewaraCinemasSort()
    {
        $return = [];
        $return['ret'] = 0;
        $return['sub'] = 0;
        $param['channelId'] = CHANNEL_ID;
        $param['city'] = $this->getRequestParams("cityId");
        $param['movieId'] = $this->getRequestParams("movieId");
        $return['data'] = $this->sdk->call("cinema/gewara-search-cinemasmov-sort", $param);
        $this->jsonOut($return);
    }

    /**
     * 获取格瓦影院拉全量
     */
    public function GewaraStaticAll()
    {
        $return = [];
        $return['ret'] = 0;
        $return['sub'] = 0;
        $channelId = CHANNEL_ID;
        $staticName = $this->getRequestParams("staticName",'movie');
        switch ($staticName) {
            case 'movie':
                $return['data'] = $this->sdk->call("movie/gewara-get-all-wx-movie-id-from-db", compact('channelId'));
                break;
            case 'cinema':
                $return['data'] = $this->sdk->call("cinema/get-gewala-cinema-id-all", compact('channelId'));
                break;
            default:
                $return['data'] = new \stdClass();
                break;
        }
        $this->jsonOut($return);
    }
}