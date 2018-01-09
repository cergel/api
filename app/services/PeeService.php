<?php

namespace app\services;


use app\base\BaseService;

class PeeService extends BaseService
{
    /**
     * 获取影片的尿点信息
     * @param $param
     * @return array
     */
    public function getMoviePee($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('pee/get-movie-pee-info', $param);
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
    public function clikePee($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('pee/click-pee', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }


}