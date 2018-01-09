<?php

namespace app\services;


use app\base\BaseService;

class MovieGuideService extends BaseService
{
    /**
     * 获取某个片子的观影秘籍信息
     *
     * @param $movieId   int  影片id
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    public function getMovieGuide($params)
    {
        $response = $this->sdk->call('movie-guide/get-movie-guide', $params);
        
        return $response;
    }
    
    /**
     * 领取观影秘籍
     *
     * @param $movieId   int  影片id
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    public function takeMovieGuide($params)
    {
        $response = $this->sdk->call('movie-guide/take-movie-guide', $params);
        
        return $response;
    }
    
    /**
     * 删除用户对某个片子的观影秘籍
     *
     * @param $movieId   int  影片id
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    public function removeMovieGuide($params)
    {
        $response = $this->sdk->call('movie-guide/remove-movie-guide', $params);
        
        return $response;
    }
    
    /**
     * 查看用户已领取的观影秘籍列表(一个片子一个秘籍,多个数据,其实等于不同片子的秘籍列表)
     *
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    public function getUserMovieGuideList($params)
    {
        $response = $this->sdk->call('movie-guide/get-movie-guide-list', $params);
        
        return $response;
    }
    
    /**
     * 查看用户领取的,某个观影秘籍的详情
     *
     * @param $movieId   int  影片id
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    public function getUserMovieGuideInfo($params)
    {
        $response = $this->sdk->call('movie-guide/get-movie-guide-detail', $params);
        
        return $response;
    }
    
}