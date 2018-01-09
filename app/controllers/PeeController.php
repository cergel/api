<?php
/**
 * 尿点相关
 * User: liulong
 * Date: 16/11/9
 * Time: 上午10:00
 */
namespace app\controllers;

use app\base\BaseController;

class PeeController extends BaseController
{

    /**
     * 获取影片尿点
     * @param $movieId
     */
    public function getMoviePee($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Pee')->getMoviePee($params);
        $this->jsonOut($ret);
    }
    /**
     * 点击、取消点击 尿点
     * @param $movieId
     */
    public function clikePee($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['p_id'] = $this->getRequestParams('peeId');
        $params['status'] = $this->getRequestParams('status','0');
        $ret = $this->service('Pee')->clikePee($params);
        $this->jsonOut($ret);
    }
}