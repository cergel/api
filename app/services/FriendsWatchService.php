<?php
namespace app\services;
/**
 * 手Q观影社区
 * Class FriendsWatchService
 * @package app\services
 */
class FriendsWatchService extends \app\base\BaseService
{
    private $signKey='gPnvrGcQYGKNVpxWOgsYaikcb';

    /**
     * 获取热映电影ids
     * 腾讯方调用
     * @param $arrParams
     * @return array
     */
    public function getHotMovieList($arrParams)
    {
        $return = self::getStOut();
        $return['movieIdList']=new \stdClass();
        $params['channelId'] = $arrParams['channelId'];
        if (!empty($params['channelId'])) {
            $response = $this->sdk->call("movie/get-hot-movie-ids",$params);
            if(isset($response['data']['movieIdList'])){
                $return['movieIdList'] = $response['data']['movieIdList'];
            }
        }
        return $return;
    }

    /**
     * 获取电影详情
     * 腾讯方调用
     * @param $arrParams
     * @return array
     */
    public function getMovieInfo($arrParams){
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['movieId'] = $arrParams['movieId'];
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("movie/read-movie-info", $params);
            if($res['ret']==0 && !empty($res['data'])){
                $info=$res['data'];
                $data=[
                    'sMovieId'=>$info['id'],
                    'sPoster'=>$info['poster_url_size3'],
                    'sMovieName'=>$info['name'],
                    'sMovieDesc'=>$info['simple_remarks'],
                    'sReleaseDate'=>$info['date'],
                    'sActors'=>$info['actor'],
                    'sMovieScore'=>(string)($info['initScore']/10),
                    'sDirector'=>$info['director'],
                    'sType'=>$info['tags'],
                    'sProductContry'=>$info['country'],
                    'sLength'=>$info['longs'],
                    'sEnMovieName'=>$info['en_name']
                ];
                $return['data']=$data;
            }
        }
        return $return;
    }


    /**
     * 详情页获取好友观影情况
     * 我方调用腾讯
     * @param $arrParams
     * @return array
     */
    public function getWatchFriends($arrParams){
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['sOpenId'] = $arrParams['openId'];
        $params['sMovieId']=$arrParams['movieId'];
        $params['sLongitude']=$arrParams['longitude'];
        $params['sLatitude']=$arrParams['latitude'];
        if (!empty($params['channelId']) && !empty($arrParams['openId']) && !empty($arrParams['movieId'])) {
            $accessToken = $this->sdk->call("mqq/get-mqq-user-token", array('openId'=>$arrParams['openId'],'channelId'=>$arrParams['channelId']));
            if(empty($accessToken)){
                return self::getErrorOut(ERRORCODE_FRIENDS_WATCH_FAIL_ACCESSTOKEN_ERROR);
            }
            $params['sOpenKey']=$accessToken;
            $ts=time();
            $sSign=$this->_makeTencentSign($params,$ts);
            $url=MQQ_FRIENDS_WATCH_URL."/cgi-bin/wpMovie.fcgi?f=json&cmd=getFriendDetail&ts={$ts}&sign={$sSign}";
            $ret=$this->_postDataTencent($params,$url);
            if($ret['ret']==0){
                $return['data'] = $ret['rsp']['data']['stMovieStatisticsInfo'];
            }
        }
        else{
            return self::getErrorOut(ERRORCODE_FRIENDS_WATCH_PARAMS_ERROR);
        }
        return $return;
    }


    /**
     * 腾讯提供的签名算法
     * @param array $arrData
     * @param string $strAppSecret
     * @return string
     */
    private function _makeTencentSign($arrData = array(),$time)
    {
        $arrData=array('req'=>$arrData);
        $strKey=json_encode($arrData,JSON_UNESCAPED_UNICODE);
        $originStr=$this->signKey.$strKey.$time;
        $strMd5 = strtoupper(MD5($originStr));
        return $strMd5;
    }

    /**
     * post内容给腾讯
     * @param $data
     * @param int $type
     */
    private function _postDataTencent($data,$url){
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = array('req'=>$data);
        $params['jsonUnicode']=true;//不用unicode处理json
        $params['iTryTimes']=2;//设置尝试两次
        $data = $this->http($url, $params,false);//不传RequestId
        return $data;
    }
}