<?php

namespace app\services;


use app\base\BaseService;

class SearchService extends BaseService
{
    /**
     * 影院关键字搜索
     *
     * @param $param
     *
     * @return array
     */
    public function searchCinema($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('search/search-cinema-v2', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
            //处理APP非有偿退票信息
            if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                $this->service("CinemaRefund")->refundCinemaEs($arrReturn);
            }
        }

        return $arrReturn;
    }

    /**
     * 综合搜索
     *
     * @param $param
     *
     * @return array
     */
    public function searchMore($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('search/search-more', $param);
        if ($response['ret'] === 0) {
            if (is_array($response['data']) && !empty($response['data'])) {
                $this->moreStripTags($response['data']);
            }
            $arrReturn['data'] = !empty($response['data']) ? $response['data'] : new \stdClass();
            $arrReturn['extra'] = !empty($response['extra']) ? $response['extra'] : new \stdClass();
        }

        return $arrReturn;
    }

    //去除演员字段中html标签
    public function moreStripTags(&$arrData)
    {
        if (!empty($arrData['movies'])) {
            foreach ($arrData['movies'] as &$value) {
                if (isset($value['actor'])) {
                    $value['actor'] = strip_tags($value['actor']);
                }
            }
        }
    }

    /**
     * 搜索推荐
     *
     * @param $param
     *
     * @return array
     */
    public function searchRecommend($param)
    {
        return $this->sdk->call('search/get-search-recommend', $param);
    }

    public function CityId2Name($channelId, $cityId)
    {
        $params = [];
        $params['channelId'] = $channelId;
        $ret = $this->sdk->call('city/read-city-v3', $params);
        $cityArr = $ret['data'];
        $cityList = [];
        foreach ($cityArr['list'] as $listKey => $listValue) {
            foreach ($listValue as $cityValue) {
                $Id = (string)$cityValue['id'];
                $cityList[$Id] = $cityValue['name'];
            }
        }

        $cityId = (string)$cityId;
        if (isset($cityList[$cityId])) {
            return $cityList[$cityId];
        } else {
            return $cityList["10"];
        }
    }
}