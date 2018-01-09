<?php

namespace app\services;


use app\base\BaseService;

class AnnounceService extends BaseService
{


    /**
     * 获取公告信息
     *
     * @param array $arrParams
     *              position    int     公告位置
     *              cinemaId    sgring  影院id（有些公告位置，是需要影院id的）
     *
     *
     * @return array
     */
    public function getInfo($params = [])
    {
        $return = $this->sdk->call("announce/get-announce", $params);
        if (empty($return['data'])) {
            $return['data'] = new  \stdClass();
        }
        
        return $return;
    }
    
    public function getSimpleInfo($params = [])
    {
        $return = $this->sdk->call("announce/get-user-simple-info", $params);
        if (empty($return['data'])) {
            $return['data'] = new  \stdClass();
        }
        
        return $return;
    }


}