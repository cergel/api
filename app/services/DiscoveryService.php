<?php

namespace app\services;


use app\base\BaseService;

class DiscoveryService extends BaseService
{
    /**
     * 手Q发现页，文章3、4之间推荐
     */
    public function recommend($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrParams['channelId'];
        $res = $this->sdk->call('mqq/get-discovery-recommend', $params);
        if (!empty($res)) {
            $return['data'] = $res;
        }
        return $return;
    }

    /**
     * 个人资料页推荐位
     * 数据展示逻辑：优先从baymax后台中读取，如果没有配置过，则从即将上映列表中拉，直至填满三个.
     * 前端url获取逻辑：如果url字段存在，则取url地址；如果url不存在，则取prevue由前端拼接地址。
     */
    public function movieWill($arrParams = []){
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrParams['channelId'];
        $params['num'] = $arrParams['num'];
        $params['movieId'] = $arrParams['movieId'];
        $params['cityId'] = $arrParams['cityId'];
        $res = $this->sdk->call('mqq/get-movie-will-recommend', $params);
        if (!empty($res)) {
            $return['data'] = $res;
        }
        return $return;
    }

}