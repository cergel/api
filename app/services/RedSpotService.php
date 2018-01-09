<?php

namespace app\services;


use app\base\BaseService;

class RedSpotService extends BaseService
{
    
    /**
     * 获取红点信息
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getRedSpotInfo($params = [])
    {
        return $this->sdk->call('red-spot/get-red-spot-info', $params);
    }
    
}