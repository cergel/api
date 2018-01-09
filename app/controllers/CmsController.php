<?php
/**
 * cms】资讯相关
 * User: liulong
 * Date: 16/11/9
 * Time: 上午10:00
 */
namespace app\controllers;

use app\base\BaseController;

class CmsController extends BaseController
{

    /**
     * 获取影片资讯
     * @param $movieId
     */
    public function getMovieNews($movieId)
    {
        $params = [];
        $params['page'] = $this->getRequestParams('page',1);
        $params['num'] = $this->getRequestParams('num',5);
        $params['movieId'] = $movieId;
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->service('Cms')->getCmsNewsList($params);
        $this->jsonOut($ret);
    }
}