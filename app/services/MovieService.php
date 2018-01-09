<?php

namespace app\services;


use app\base\BaseService;

class MovieService extends BaseService
{
    /**
     * 获取分页的影片列表信息
     *
     * @param array $arrInput 需要的参数（从controller传入）
     * @param int $type 数据源获取方式。1是电影票自己从redis读取，2表示通过service来后去，3表示直接读取影片列表的原来的整个数据
     *
     * @return array 返回结构如：['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
     */
    public function readCityMovieByPage($arrInput, $type = 2)
    {
        //电影票自己逻辑获取
        $return = self::getStOut();
        $return['data'] = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ($type == 1) {
        } //调用service获取
        elseif ($type == 2) {
            $res = $this->sdk->call('movie/read-city-movie-by-page', $arrInput);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data']['list'])) {
                $return['data'] = $res['data'];
            }
        } //读取原有的整个数据（这种情况一般不会用到，只有前2种方式出问题的后背方案）
        else {
            $iCityId = $this->getParam($arrInput, 'cityId');
            $strData = $this->readCityMovie($iCityId);
            if (!empty($strData)) {
                $arrData = json_decode($strData, true);
                $return['data']['list'] = !empty($arrData['info']) ? $arrData['info'] : [];
                $return['data']['total_page'] = $return['data']['page'] = 1;
                $return['data']['total_row'] = $return['data']['num'] = count($return['data']['list']);
            }
        }

        return $return;
    }

    /**
     * 获取按日期分组的即将上映列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getMovieWillWithDate($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = ['list' => [], 'dimension' => [], 'page_info' => ['total' => 1, 'current' => 1,]];
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['unionId'] = !empty($arrParams['unionId']) ? $arrParams['unionId'] : '';
        $params['page'] = $arrParams['page'];
        $params['year'] = !empty($arrParams['year']) ? $arrParams['year'] : '';
        $params['month'] = !empty($arrParams['month']) ? $arrParams['month'] : '';
        $params['state'] = !empty($arrParams['state']) ? $arrParams['state'] : 1;
        if (!empty($params['channelId']) && !empty($params['cityId'])) {
            $res = $this->sdk->call('movie/get-movie-will-with-date', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data']['list'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }

    public function readMovieWill($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';

        $arrSdk = $this->sdk->call("movie/read-movie-will", $params);
        $arrData = $arrSdk['data'];
    }

    /**
     * 获取影片详情
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getMovieInfo($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
        $params['cityId'] = $arrParams['cityId'];
        if (!empty($params['channelId']) && !empty($params['movieId'])) {
            $res = $this->sdk->call('movie/read-movie-info', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }

    /**
     * 格式化影片详情
     * @param $arrData
     * @return mixed
     */
    private function _formatMovieInfo($arrData)
    {
        if (isset($arrData['date'])) {
            $arrData['date_stamp'] = strtotime($arrData['date']);
        }

        //处理主预告片
        if ((isset($arrData['prevue']) && empty($arrData['prevue'])) || !isset($arrData['prevue'])) {
            $arrData['prevue'] = new \stdClass();
        }
        return $arrData;
    }

    /**
     * 获取影片阵营购票数
     */
    public function getMovieCamp($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
        if (!empty($params['channelId']) && !empty($params['movieId'])) {
            $res = $this->sdk->call('movie/get-movie-camp', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }


    /**
     * 增加影片阵营购票数, 默认加1
     * @param int $movieId 影片ID
     * @param string $campName 阵营名称
     * @param int $seatNum 座位数，仅在内部调用时传入
     */
    public function incrMovieCamp($arrParams = [])
    {
        $return = self::getStOut();
        $params['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
        $params['campName'] = !empty($arrParams['campName']) ? $arrParams['campName'] : '';
        $params['seatNum'] = !empty($arrParams['seatNum']) ? $arrParams['seatNum'] : 1;
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        if (!empty($params['channelId']) && !empty($params['movieId']) && !empty($params['campName'])) {
            $res = $this->sdk->call('movie/incr-movie-camp', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    public function getActor($arrParams)
    {
        $return = self::getStOut();
        $params['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        if (!empty($params['channelId']) && !empty($params['movieId'])) {
            $res = $this->sdk->call('movie/get-movie-actor-list', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 喜欢、取消喜欢影人
     * @param $iActorId
     * @param $iMovieInfo
     * @return mixed
     */
    public function actorLike($arrParams)
    {
        $return = self::getStOut();
        $params['actorId'] = $arrParams['actorId'];
        $params['channelId'] = $arrParams['channelId'];
        $params['status'] = $arrParams['status'];
        $params['openId'] = $arrParams['openId'];
        if (!empty($params['channelId']) && !empty($params['actorId'])) {
            $res = $this->sdk->call('msdb/actor-like', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取分页的影片列表信息crontasK_new数据源V2版本
     *
     * @param array $arrInput 需要的参数（从controller传入）
     *
     * @return array 返回结构如：['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
     */
    public function readCityMovieByPageNewStaticV2($arrInput)
    {
        //电影票自己逻辑获取
        $return = self::getStOut();
        $return['data'] = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];

        $res = $this->sdk->call('movie/read-city-movie-by-page-new-static-v2', $arrInput);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data']['list'])) {
            $return['data'] = $res['data'];
        } else {
            if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
                //默认返回北京的排期
                $arrInput['cityId'] = 10;
                $res = $this->sdk->call('movie/read-city-movie-by-page-new-static-v2', $arrInput);
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }


    /**
     * 获取用户已经评论过的电影
     * @param $channelId
     * @return array
     */
    public function getUserCommentList($channelId)
    {
        $return = [];
        $url = COMMENT_CENTER_URL.'/v1/users/want-movies';
        $param = [];
        $param['sMethod'] = "GET";
        $param['iTimeout'] = 2;
        $param['arrData'] = [
            'token' => $_SERVER['HTTP_TOKEN'],
            'channelId' => $channelId,
        ];
        $response = $this->http($url, $param);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $return = $response['data'];
        }
        return $return;
    }

}