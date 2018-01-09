<?php

namespace app\services;


use app\base\BaseService;

class CmsService extends BaseService
{
    /**
     * 获取影片的资讯信息
     * @param $param
     * @return array
     */
    public function getCmsNewsList($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('cms/get-cms-news-list', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }

}