<?php
/**
 * cms】媒资库-影人相关
 * User: liulong
 * Date: 16/11/9
 * Time: 上午10:00
 */
namespace app\controllers;

use app\base\BaseController;

class MsdbController extends BaseController
{

    /**
     * 获取影人详情信息
     * @param $movieId
     */
    public function getActorInfo($actorId)
    {
        $params = [];
        $params['actorId'] = $actorId;
        $params['movieInfo'] = $this->getRequestParams('movieInfo','');
        $params['cityId'] = $this->getRequestParams('cityId','10');
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Msdb')->getActorInfo($params);
        $this->jsonOut($ret);
    }

    /**
     * 喜欢、取消喜欢影人
     * @param $movieId
     */
    public function likeActor($actorId)
    {
        $params = [];
        $params['actorId'] = $actorId;
        $params['status'] = $this->getRequestParams('status','0');
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Msdb')->likeActor($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取影片的影人列表--带评价信息
     * @param $movieId
     */
    public function getMovieActorListAndAppraise($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Msdb')->getMovieActorListAndAppraise($params);
        $this->jsonOut($ret);
    }
    /**
     * 修改、新增、删除 用户对指定影片的指定影人的评价
     * @param $movieId
     */
    public function saveActorAppraise($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['actorId'] = $this->getRequestParams('actorId');
        $params['status'] = $this->getRequestParams('status','0');
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Msdb')->saveActorAppraise($params);
        }
        $this->jsonOut($ret);
    }
    /**
     * 获取影片图片--分类图片
     * @param $movieId
     */
    public function getMoviePoster($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['posterType'] = $this->getRequestParams('posterType','');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Msdb')->getMoviePoster($params);
        $this->jsonOut($ret);
    }
    /**
     * 获取影片图片--分类图片
     * @param $movieId
     */
    public function getActorPoster($actorId)
    {
        $params = [];
        $params['actorId'] = $actorId;
        $params['posterType'] = $this->getRequestParams('posterType','');
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Msdb')->getActorPoster($params);
        $this->jsonOut($ret);
    }
    /**
     * 我的影人
     */
    public function myActor()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num',5);
        if (empty($params['openId'])) {//必须有openid
            $this->jsonOut(self::getErrorOut(ERRORCODE_ERROR_RELOGIN));
        }
        $ret =$this->service('Msdb')->getUserActor($params);
        $this->jsonOut($ret);
    }

}