<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/8
 * Time: 下午5:28
 */

namespace app\controllers;


use app\base\BaseController;

class MovieController extends BaseController
{
    /**
     * 获取正在热映的影片列表
     *
     * @param $cityId
     */
    public function getList($cityId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params, 60);
        if (!empty($cacheData)) {
            $this->jsonOut($cacheData);
        }
        $ret = $this->service('Movie')->readCityMovieByPage($params, 2);
        if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data'])) {
            //保存结果为文件缓存
            self::setCacheData($params, $ret);
        }
        $this->jsonOut($ret);
    }

    /**
     * 新版即将上映接口 自定义排序方式
     */
    public function getWillWithDate($cityId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = '';
        $params['page'] = $this->getRequestParams("page", 1);
        $params['year'] = $this->getRequestParams("year", '');
        $params['month'] = $this->getRequestParams("month", '');
        $params['state'] = $this->getRequestParams("state", 0);
        $ret = $this->service('Movie')->getMovieWillWithDate($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取影片详情
     * @param $movieId
     */
    public function getInfo($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['cityId'] = $this->getRequestParams("cityId", 0);
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params, 60);
        if (!empty($cacheData)) {
            $this->jsonOut($cacheData);
        }
        //app 调用不同的接口
        $ret = $this->service('Movie')->getMovieInfo($params);
        if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data'])) {
            if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                if (is_array($ret['data']) && isset($ret['data']['prevue']) && empty($ret['data']['prevue'])) {
                    $ret['data']['prevue'] = new \stdClass();
                    $ret['data']['date_stamp'] = strtotime($ret['data']['date']);
                }
            }
            //保存结果为文件缓存
            self::setCacheData($params, $ret);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取影片阵营购票数
     * 如《魔兽》联盟/部落
     * @param number $movieId
     */
    public function getMovieCamp($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $ret = $this->service("Movie")->getMovieCamp($params);
        $this->jsonOut($ret);
    }

    /**
     * 增加影片阵营购票数，默认加1
     * 如《魔兽》联盟/部落
     * @param number $movieId 影片ID
     * @param number $campName 阵营名称
     */
    public function addMovieCamp($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $params['campName'] = $this->getRequestParams("campName", '');
        $ret = $this->service("Movie")->incrMovieCamp($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取电影主创
     * @param $movieId
     */
    public function getActor($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        $ret = $this->service("Movie")->getActor($params);
        $this->jsonOut($ret);
    }

    /**
     * 喜欢、取消喜欢影人
     * @param $actorId
     */
    public function likeActor($actorId)
    {
        $params = [];
        $params['actorId'] = $actorId;
        $params['status'] = $this->getRequestParams("status", 0);
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        //重新登录
        if (empty($params['openId'])) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Movie')->actorLike($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 批量接口-影片详情页
     */
    public function batchMovieInfo($movieId)
    {
        $return = self::getStOut();
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
//        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
//        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
//        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params);
        if (!empty($cacheData)) {
            $this->jsonOut($cacheData);
        }
        //$params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //获取影片的影人列表--带评价信息
        $ret_actor_list = $this->service('Msdb')->getMovieActorListAndAppraise($params);
        $return['data']['actor_list'] = ($ret_actor_list['ret'] == 0) ? $ret_actor_list['data'] : '';

        //获取影片资讯
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num', 5);
        $ret_news = $this->service('Cms')->getCmsNewsList($params);
        $return['data']['news'] = ($ret_news['ret'] == 0) ? $ret_news['data'] : '';

        //尿点信息
        $ret_peeinfo = $this->service('Pee')->getMoviePee($params);
        $return['data']['pee_info'] = ($ret_peeinfo['ret'] == 0) ? $ret_peeinfo['data'] : '';

        //观影秘籍
        $ret_movie_guide = $this->service('MovieGuide')->getMovieGuide($params);
        $return['data']['movie_guide'] = ($ret_movie_guide['ret'] == 0) ? $ret_movie_guide['data'] : '';
        //片单
        $filmList = $this->sdk->call("film-list/get-film-list-by-movie-id", $params);
        $return['data']['filmList'] = isset($filmList['data']) && !empty($filmList['data']) ? $filmList['data'] : new \stdClass();
        //音乐
        $movieMusic = $this->sdk->call("music/get-music-info", $params);
        $return['data']['music'] = isset($movieMusic['data']) && !empty($movieMusic['data']) ? $movieMusic['data'] : new \stdClass();;
        //保存结果为文件缓存
        self::setCacheData($params, $return);
        $this->jsonOut($return);
    }

    //原声音乐
    public function music($movieId)
    {
        $params = [
            'channelId' => $this->getRequestParams("channelId"),
            'movieId' => $movieId,
        ];
        $ret = $this->sdk->call("music/get-music-info", $params);
        $this->jsonOut($ret);
    }

    //片单详情
    public function getFilmList($listId)
    {
        $params = [
            'channelId' => CHANNEL_ID,
            'page' => $this->getRequestParams("page", 1),
            'num' => $this->getRequestParams("num", 10),
            'filter' => $this->getRequestParams("filter", ""),
            'listId' => $listId
        ];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        $ret = $this->sdk->call("film-list/get-film-list", $params);
        $this->jsonOut($ret);

    }

//包含本影片的片单
    public function getFilmListByMovie($movieId)
    {
        $params = [];
        $params['movieId'] = $movieId;
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        $response = $this->sdk->call("film-list/get-film-list-by-movie-id", $params);
        $this->jsonOut($response);
    }


    //收藏片单
    public function favouriteFilmList($listId)
    {
        $params = [];
        $params['listId'] = $listId;
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        $params['action'] = "1";
        $response = $this->sdk->call("film-list/favourite", $params);
        $this->jsonOut($response);
    }

    //取消收藏片单
    public function delFavouriteFilmList($listId)
    {
        $params = [];
        $params['listId'] = $listId;
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        $params['action'] = "0";
        $response = $this->sdk->call("film-list/favourite", $params);
        $this->jsonOut($response);
    }

    //获取收藏片单
    public function getFavouriteList()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['page'] = $this->getRequestParams("page", '1');
        $params['num'] = $this->getRequestParams("num", '10');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['unionId'] = $this->service('Login')->getAuthInfoByToken("unionId");
        $params['uid'] = $this->service('Login')->getAuthInfoByToken("uid");
        $response = $this->sdk->call("film-list/get-favourite-list", $params);
        $this->jsonOut($response);
    }

    /**
     * 获取正在热映的影片列表V2版本
     * V2不同于V1，V2是直接读取crontask_new数据源，且新增了一些字段（http://wiki.intra.wepiao.com/pages/viewpage.action?pageId=16581162）
     *
     * @param $cityId
     */
    public function getListV2($cityId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $cityId;
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params, 60);
        if (!empty($cacheData)) {
            $this->jsonOut($cacheData);
        }
        $ret = $this->service('Movie')->readCityMovieByPageNewStaticV2($params);
        if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data'])) {
            //保存结果为文件缓存
            self::setCacheData($params, $ret);
        }
        $this->jsonOut($ret);
    }

    /**
     * 格瓦拉影片id获取娱票儿影片id
     * @param $movieId
     */
    public function getWxMovieId($movieId)
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $movieId;
        if (empty($movieId)) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_PARAM);
        } else {
            $ret = $this->sdk->call("movie/gewara-get-wx-movie-id-from-db", $params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 格瓦拉影人id获取娱票儿影人id
     * @param $actorId
     */
    public function getWxActorId($actorId)
    {
        $channelId = CHANNEL_ID;
        if (empty($actorId)) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_PARAM);
        } else {
            $ret = $this->sdk->call("movie/gewara-get-wx-actor-id", compact('channelId', 'actorId'));
        }
        $this->jsonOut($ret);
    }

    public function GewaraMovieIdPush()
    {
        $channelId = CHANNEL_ID;
        $gewaraMovieId = $this->getRequestParams("gewaraMovieId");
        $ypMovieId = $this->getRequestParams("ypMovieId");
        $gewaraMovieName = $this->getRequestParams("gewaraMovieName");
        $ypMovieName = $this->getRequestParams("ypMovieName");
        if (empty($gewaraMovieId) || empty($ypMovieId)) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_PARAM);
        } else {
            $ret = $this->sdk->call("movie/gewara-set-wx-movie-id-to-db",
                compact('channelId', 'gewaraMovieId', 'ypMovieId'));
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取媒资库预告片列表
     *
     * @param $iMovieId
     */
    public function getMsdbVideos($iMovieId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['movieId'] = $iMovieId;
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['cityId'] = $this->getRequestParams("cityId", '');
        //读取结果的文件缓存，有效则直接返回
        $cacheData = self::getCacheData($params, 60);
        if (!empty($cacheData)) {
            $this->jsonOut($cacheData);
        }
        $ret = $this->sdk->call('movie/read-movie-videos-new-static', $params);
        if (!empty($ret) && isset($ret['ret']) && ($ret['ret'] == 0) && !empty($ret['data']['list'])) {
            //保存结果为文件缓存
            self::setCacheData($params, $ret);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取媒资库预告片列表
     *
     * @param $iMovieId
     */
    public function getMovieWillPreview($iCityId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $iCityId;
        //读取结果的文件缓存，有效则直接返回
        if (!empty($params[$iCityId])) {
            $this->jsonError('-1', '无效的参数');
        }
        $ret = $this->sdk->call('movie/get-movie-will-preview', $params);
        if (empty($ret['data'])) {
            $ret['data'] = new \stdClass();
        }

        $this->jsonOut($ret);
    }

}
