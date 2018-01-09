<?php

namespace app\services;
use app\base\BaseService;

class FindService extends BaseService
{
    /**
     * 获取影片的尿点信息
     * @param $param
     * @return array
     */
    public function getFindInfo($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('find/get-find-other', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }
    /**
     * 点击、取消点击 尿点
     * @param $param
     * @return array
     */
    public function getFindList($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('find/get-find-list', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }

    /**
     * 获取发现导流
     */
    public function getFindGuide($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('find/get-find-guide', $param);
        if(!empty($response)) {
            $arrReturn['data'] = $response;
        }
        return $arrReturn;
    }

}