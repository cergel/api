<?php

namespace app\services;


use app\base\BaseService;

class SpaceService extends BaseService
{


    /**
     * 根据传递过来的参数解析出openId，ucid及ucid加密后的token
     * @param array $arrInput
     * @return array
     */
    public function getUserOpenId($arrInput = [])
    {
        $return = self::getStOut();
        //根据ucid获取openid
        $channelId = $arrInput['channelId'];
        $ucid = $arrInput['ucid'];
        $openId = '';
        $token = '';
        if ($channelId == '' || $ucid == '') {
            $return = self::getErrorOut(ERRORCODE_USER_CENTER_PARAMS_ERROR);
            return $return;
        }
        $strKey = $ucid . '_' . $channelId;
        //先优先从缓存中获取，不能获取到再走后面的逻辑
        $res = $this->model('Space')->getOpenInfoByUcid($strKey);
        if ($res != '') {
            $return['data'] = json_decode($res, true);
            return $return;
        }
        //查询关系树获取openId
        $resOpenid = $this->sdk->call('user/get-id-relation', ['id' => $ucid, 'channelId' => $channelId]);
        //print_r($resOpenid);
        if ($resOpenid['ret'] == 0 && $resOpenid['sub'] == 0) {
            if ($resOpenid['data']['idRelation']['idType'] == 0) {
                $openId = $resOpenid['data']['idRelation']['id'];
            } else {
                foreach ($resOpenid['data']['idRelation']['idUnderBound'] as $item) {
                    if ($item['idType'] == 11 && $channelId == 3) {
                        $openId = $item['id'];
                    }
                    elseif ($item['idType'] == 12 && $channelId == 28) {
                        $openId = $item['id'];
                    }
                    elseif ($item['idType'] == 13 && ($channelId == 8 || $channelId == 9)) {
                        $openId = $item['id'];
                    }
                    elseif ($item['idType'] == 30 && $channelId == 3) {
                        $openId = $item['idUnderBound'][0]['id'];
                    }
                }
            }
        }
        if ($openId == '') {
            $return = self::getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
            return $return;
        }
        //获取ucId加密的Token
        $response = $this->sdk->call('Common/encrypt', [
            'str' => $ucid,
            'channelId' => $channelId,
        ]);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $token = $response['data']['encryptStr'];
        }
        $data = [
            'objOpenId' => $openId,
            'objToken' => $token,
            'objUcId' => $ucid
        ];
        $return['data'] = $data;
        $this->model('Space')->setOpenInfoByUcid($strKey, json_encode($data));
        return $return;
    }

    /**
     * 我跟待查看的好友共同看过的电影
     */
    public function watchSameMovies($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrInput['channelId'];
        $params['myOpenId'] = $arrInput['openId'];
        $params['otherOpenId'] = $arrInput['objOpenId'];
        if (!empty($params['channelId']) && !empty($params['myOpenId']) && !empty($params['otherOpenId'])) {
            $res = $this->sdk->call('user/watch-same-movies', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /*
     * 他人的背景图片
     */
    public function backGround($arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['backImage'] = null;
        $params['channelId'] = $arrInput['channelId'];
        $params['openId'] = $arrInput['openId'];
        $params['background'] = $arrInput['background'];
        if (!empty($params['channelId']) && !empty($params['openId']) && !empty($params['background'])) {
            $crcOpenId = abs(crc32($params['openId']));
            $background = $params['background'];
            $finalbg = $background[$crcOpenId % count($background)];
            $return['data']['backImage'] = $finalbg;
        }
        return $return;
    }

    /**
     * 获取用户观影轨迹
     * @param $arrParams
     * @return array
     */
    public function getUserTrace($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['ucId'] = $arrInput['objUcid'];
        $params['channelId'] = $arrInput['objChannelId'];
        $params['token'] = $arrInput['objToken'];
        $params['page'] = $arrInput['page'];
        $params['num'] = $arrInput['num'];
        if (!empty($params['channelId']) && !empty($params['ucId'])) {
            $res = $this->sdk->call('user/get-trace-path', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取他人的想看清单
     * @param $arrParams
     * @return array
     */
    public function getUserWants($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = $arrInput['channelId'];
        $params['ucid'] = $arrInput['ucid'];
        $params['page'] = $arrInput['page'];
        $params['num'] = $arrInput['num'];
        $params['cityId'] = $arrInput['cityId'];
        $params['sort'] = $arrInput['sort'];
        $params['method'] = $arrInput['method'];
        if (!empty($params['channelId']) && !empty($params['ucid'])) {
            $res = $this->sdk->call('wants/get-user-want-movie-list', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 单独获取用户想看总数
     * @param array $arrParams
     * @return array
     */
    private function _getUserWantsCount($arrParams=[]){
        $return = self::getStOut();
        $params['channelId'] = $arrParams['objChannelId'];
        $params['ucid'] = $arrParams['objUcid'];
        if (!empty($params['channelId']) && !empty($params['ucid'])) {
            $res = $this->sdk->call('wants/get-user-want-movie-count', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取用户喜欢的影人列表
     * @param array $arrInput
     */
    public function getUserActor($arrInput = [])
    {
        return $this->sdk->call('msdb/actor-like-list', $arrInput);
    }


    /**
     * 单独获取用户喜欢的影人列表
     * @param array $arrParams
     * @return array
     */
    private function _getUserActorCount($arrInput = []){
        $return = self::getStOut();
        $params['openId'] = $arrInput['objOpenId'];
        $params['channelId'] = $arrInput['objChannelId'];
        if (!empty($params['openId'])) {
            $res = $this->sdk->call('msdb/actor-like-count', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
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
     * 查看用户已领取的观影秘籍列表(一个片子一个秘籍,多个数据,其实等于不同片子的秘籍列表)
     *
     * @param $openId    string openId
     * @param $channelId int 渠道id
     *
     * @return mixed
     */
    private function _getUserMovieGuideCount($arrInput = [])
    {
        $params=[];
        $params['channelId'] = $arrInput['objChannelId'];
        $params['openId'] = $arrInput['objOpenId'];
        $return = $this->sdk->call('movie-guide/get-movie-guide-count', $params);
        return $return;
    }


    /**
     * 获取用户个人资料
     * 此方法, 可通过 openId 或 unionId 或 uid 来获取用户资料
     * @param array $arrInput
     *
     * @return mixed
     */
    public function getUserProfile($arrInput = [])
    {
        $response = $this->sdk->call('user/get-user-profile', [
            'unionId' => $arrInput['ucid'],
            'openId'=>$arrInput['openId'],
            'channelId' => $arrInput['channelId'],
        ]);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //获取用户标签
            $params = [
                'openId' => $arrInput['openId'],
                'channelId' => $arrInput['channelId'],
            ];
            $tagResponse = $this->sdk->call('user/get-user-tag', $params);
            if (!empty($tagResponse['data'])) {
                $response['data']['summary'] = $tagResponse['data']['summary'];
                $response['data']['is_star'] = $tagResponse['data']['is_star'];
            } else {
                $response['data']['summary'] = '';
                $response['data']['is_star'] = 0;
            }
        }
        return $response;
    }

    /**
     * 获取想看及喜欢总数
     * @param array $arrInput
     */
    public function getCounts($arrInput = []){
        $return = self::getStOut();
        $return['data']=['wantCount'=>0,'actorCount'=>0,'guideCount'=>0];
        //获取想看电影总数
        $wantCount=$this->_getUserWantsCount($arrInput);
        $actorCount=$this->_getUserActorCount($arrInput);
        $guideCount=$this->_getUserMovieGuideCount($arrInput);
        if(isset($wantCount['data']['totalCount'])){
            $return['data']['wantCount']=$wantCount['data']['totalCount'];
        }
        if(isset($actorCount['data']['totalCount'])){
            $return['data']['actorCount']=$actorCount['data']['totalCount'];
        }
        if(isset($guideCount['data']['totalCount'])){
            $return['data']['guideCount']=$guideCount['data']['totalCount'];
        }
        return $return;
    }
}
