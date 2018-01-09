<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */

/**
 * app 版本
 */

namespace app\controllers;

use app\base\BaseController;

class AppMovieController extends BaseController
{
    /**
     * 取得Android最新版本
     */
    public function getList($cityId)
    {
        $return = $this->getStOut();
        $data = [];
        $onShowData = $this->_getCityOnshowData($cityId);
        $data['onShow'] = !empty($onShowData) ? $onShowData : new \stdClass();
        $onWillData = $this->_getCityWillShowData($cityId);
        $data['willShow']['list'] = !empty($onWillData) ? $onWillData : new \stdClass();
        $return['data'] = $data;
        $this->jsonOut($return);
    }


    public function recommend()
    {
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 10);
        if (empty($params['date'])) {
            $params['date'] = date('Ymd');
        }
        $params['channelId'] = CHANNEL_ID;
        $return = $this->getStOut();
        $ret = $this->sdk->call("app-resource/get-recommend", $params);
//        $recommend = json_decode($ret, 1);
        if(!empty($ret)){
            $return['data'] = $ret;
        } else {
            $return['data'] = new \stdClass();
        }

        $this->jsonOut($return);
    }

    /**
     * 获取城市下热映影片
     * @param $cityId
     * @return array|\stdClass
     */
    private function _getCityOnshowData($cityId)
    {
        $data = new \stdClass();
        //热映相关的数据
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['page'] = 1;
        $params['num'] = 15;
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params, 60);
        if (!empty($cacheData)) {
            $data = $cacheData;
        } else {
            $ret = $this->service('Movie')->readCityMovieByPageNewStaticV2($params);
            if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data'])) {
                //保存结果为文件缓存
                self::setCacheData($params, $ret['data']);
                $data = $ret['data'];
            }
        }
        return $data;
    }

    /**
     * 获取即将上映列表（20条）
     * @param $cityId
     * @return mixed
     */
    private function _getCityWillShowData($cityId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['num'] =
        $ret = $this->sdk->call("movie/get-movie-will-preview-list", $params);
        if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data'])) {
            //保存结果为文件缓存
            self::setCacheData($params, $ret['data']);
        }
        return $ret['data'];
    }
}