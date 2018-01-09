<?php

namespace app\controllers;

use app\base\BaseController;

/**
 * 手Q观影社区
 * Class FriendsWatchController
 * @package app\controllers
 */

class FriendsWatchController extends BaseController
{
    /**
     * 获取目前正在上映的电影全量
     */
    public function getHotMovieList(){
        $params = [];
        $params['channelId'] = $this->getRequestParams('channelId',28);
        $return = $this->service("FriendsWatch")->getHotMovieList($params);
        $this->jsonOut($return);
    }

    /**
     * 获取电影详情
     */
    public function getMovieInfo(){
        $return = self::getStOut();
        $params = [];
        $params['channelId'] = $this->getRequestParams('channelId',28);
        $params['movieId'] = $this->getRequestParams('sMovieId', '');
        if(empty($params['movieId']) || empty($params['channelId'])){
            $return=$this->getErrorOut(ERRORCODE_FRIENDS_WATCH_PARAMS_ERROR);
        }
        else{
            $res = $this->service("FriendsWatch")->getMovieInfo($params);
            if(!empty($res['data'])){
                $return['data']=$res['data'];
            }
        }
        $this->jsonOut($return);
    }

    public function getWatchFriends(){
        $return = self::getStOut();
        $params = [];
        $params['channelId'] = $this->getRequestParams('channelId',28);
        $params['movieId'] = $this->getRequestParams('movieId', '');
        $openId = $this->service('Login')->getOpenIdFromCookie();
        if(empty($params['movieId']) || empty($params['channelId'])){
            $return=$this->getErrorOut(ERRORCODE_FRIENDS_WATCH_PARAMS_ERROR);
        }
        elseif(empty($openId)){
            $return=$this->getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else{
            $strLatitude = $this->getRequestParams('latitude', '0');
            $strLongitude = $this->getRequestParams('longitude', '0');
            if(!empty($strLatitude)){
                $params['latitude'] = (string)$strLatitude;
            }
            if(!empty($strLongitude)){
                $params['longitude'] = (string)$strLongitude;
            }
            $params['openId']=$openId;
            $return = $this->service("FriendsWatch")->getWatchFriends($params);
        }
        $this->jsonOut($return);
    }
}