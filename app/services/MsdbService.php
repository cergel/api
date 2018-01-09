<?php

namespace app\services;


use app\base\BaseService;

class MsdbService extends BaseService
{
    /**
     * 获取影人详情
     * @param $param
     * @return array
     */
    public function getActorInfo($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/get-actor-info', $param);
        if ($response['ret'] === 0 && !empty($response['data'])) {
            $arrReturn['data'] = $response['data'];
            $arrReturn['data']['gewara_id'] = !empty($response['data']['gewara_id']) ? $response['data']['gewara_id'] : (string)$response['data']['id'];
        } else {
            $arrReturn['data'] = new \stdClass();
        }
        return $arrReturn;
    }

    /**
     * 喜欢、取消喜欢影人
     * @param $param
     * @return array
     */
    public function likeActor($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/actor-like', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }
    /**
     * 获取影片的影人列表--带评价信息
     * @param $param
     * @return array
     */
    public function getMovieActorListAndAppraise($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/get-movie-actor-list-and-appraise', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        else {
            $arrReturn['data'] = new \stdClass();
        }
        return $arrReturn;
    }
    /**
     * 修改、新增、删除 用户对指定影片的指定影人的评价
     * @param $param
     * @return array
     */
    public function saveActorAppraise($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/save-appraise', $param);
        if ($response['ret'] === 0) {
            $arrReturn['data'] = $response['data'];
        }
        return $arrReturn;
    }
    /**
     * 获取影片图片
     * @param $param
     * @return array
     */
    public function getMoviePoster($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/get-movie-poster', $param);
        if ($response['ret'] === 0) {
            if(empty($response['data'])){
                $arrReturn['data'] = new \stdClass();
            } else {
                $arrReturn['data'] = $response['data'];
            }
        }
        return $arrReturn;
    }
    /**
     * 获取影人图片
     * @param $param
     * @return array
     */
    public function getActorPoster($param)
    {
        $arrReturn = self::getStOut();
        $response = $this->sdk->call('msdb/get-actor-photo', $param);
        if ($response['ret'] === 0) {
            if(empty($response['data'])){
                $arrReturn['data'] = new \stdClass();
            } else {
                $arrReturn['data'] = $response['data'];
            }
        }
        return $arrReturn;
    }

    /**
     * 获取用户喜欢的影人列表
     * @param array $arrInput
     */
    public function getUserActor($arrInput = [])
    {
        return $this->sdk->call('msdb/actor-like-list', $arrInput);
    }
}