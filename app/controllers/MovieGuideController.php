<?php

namespace app\controllers;

use app\base\BaseController;

class MovieGuideController extends BaseController
{
    /**
     * 获取某个片子的观影秘籍信息
     *
     * @param $movieId int 影片id
     */
    public function getMovieGuide($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['movieId'] )) {
            $ret = self::getErrorOut(ERRORCODE_MOVIE_GUIDE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('MovieGuide')->getMovieGuide($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 领取观影秘籍
     *
     * @param $movieId int 影片id
     */
    public function takeMovieGuide($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['movieId'] )) {
            $ret = self::getErrorOut(ERRORCODE_MOVIE_GUIDE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('MovieGuide')->takeMovieGuide($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 删除某个片子的观影秘籍
     *
     * @param $movieId int 影片id
     */
    public function removeMovieGuide($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['movieId'] )) {
            $ret = self::getErrorOut(ERRORCODE_MOVIE_GUIDE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('MovieGuide')->removeMovieGuide($params);
        }
        $this->jsonOut($ret);
    }
    
    
    /**
     * 查看用户领取的观影秘籍列表
     *
     * @param $movieId int 影片id
     */
    public function getUserMovieGuideList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else {
            $ret = $this->service('MovieGuide')->getUserMovieGuideList($params);
        }
        $this->jsonOut($ret);
    }
    
    /**
     * 获取用户领取的某个片子的观影秘籍详情
     *
     * @param $movieId int 影片id
     */
    public function getUserMovieGuideInfo($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        elseif (empty( $params['movieId'] )) {
            $ret = self::getErrorOut(ERRORCODE_MOVIE_GUIDE_PARAMS_ERROR);
        }
        else {
            $ret = $this->service('MovieGuide')->getUserMovieGuideInfo($params);
        }
        $this->jsonOut($ret);
    }
    
}