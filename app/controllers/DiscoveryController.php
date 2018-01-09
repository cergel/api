<?php

namespace app\controllers;

use app\base\BaseController;

class DiscoveryController extends BaseController
{

    /**
     * 手Q发现页，文章3、4之间推荐
     */
    public function recommend()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Discovery')->recommend($params);
        $this->jsonOut($ret);
    }

    /**
     * 个人资料页推荐位
     * 数据展示逻辑：优先从baymax后台中读取，如果没有配置过，则从即将上映列表中拉，直至填满三个.
     * 前端url获取逻辑：如果url字段存在，则取url地址；如果url不存在，则取prevue由前端拼接地址。
     */
    public function movieWill()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['num'] = $this->getRequestParams('num','3');
        $params['movieId'] = $this->getRequestParams('movieId','');
        $params['cityId'] = $this->getRequestParams('cityId', '10');
        if (empty($params['cityId'])) {
            $params['cityId'] = 10;
        }
        $ret = $this->service('Discovery')->movieWill($params);
        $this->jsonOut($ret);
    }
}