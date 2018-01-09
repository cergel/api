<?php

namespace app\controllers;

use app\base\BaseController;

class SpaceController extends BaseController
{
    private $channelIdKey='commentchanneltypeid';
    private $ucidKey='commentucid';

    /**
     * 根据传递过来的参数获取ucid对应的openid及token
     */
    public function getUserOpenId(){
        $params=[];
        $params['channelId'] = $this->getRequestParams($this->channelIdKey,'');//被访问者的channelId
        $params['ucid'] = $this->getRequestParams($this->ucidKey,'');//名字叫openId，实际上是ucid
        //根据ucid获取openid
        $ret=$this->service('Space')->getUserOpenId($params);
        return $ret;
    }

    /**
     * 查询用户个人资料
     * 通过任意一项(uid、openId), 获取用户资料。之所以可以这样做, 是因为调用此接口的前提是, 此用户必须已经绑定到了用户中心
     *
     * @param string $memberId memberId 用户唯一uid，非必须
     * @param string $openId 用户openId
     *
     * @return array
     */
    public function getUserProfile()
    {
        $res=$this->getUserOpenId();
        if($res['ret']!=0){
            $ret=$res;
        }
        elseif(empty($res['data']['objOpenId'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['ucid'] = $this->getRequestParams($this->ucidKey,'');
            $params['channelId'] = $this->getRequestParams($this->channelIdKey,'');//渠道
            $params['openId'] = $res['data']['objOpenId'];
            $ret = $this->service('Space')->getUserProfile($params);
        }

        $this->jsonOut($ret);
    }

    /**
     * 获取他人的想看电影
     * 次接口只需要传ucId即可
     */
    public function getUserWants(){
        $params=[];
        //$params['channelId'] = CHANNEL_ID;
        $params['channelId'] = $this->getRequestParams($this->channelIdKey,'');//渠道
        $params['ucid'] = $this->getRequestParams($this->ucidKey,'');
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $params['sort'] = $this->getRequestParams("sort", 0);
        $params['cityId'] = $this->getRequestParams("cityId", 10);
        $params['method'] = $this->getRequestParams("method", 'desc');
        if (empty($params['ucid'])) {
            $this->jsonOut($this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR));
        }
        $ret = $this->service('Space')->getUserWants($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取想看电影总数，喜欢影人总数，观影秘籍总数
     */
    public function getCounts(){
        $params=[];
        $params['channelId'] = CHANNEL_ID;
        $params['objChannelId'] = $this->getRequestParams($this->channelIdKey,'');
        $res=$this->getUserOpenId();
        if($res['ret']!=0){
            $ret=$res;
        }
        elseif(!isset($res['data']['objUcId']) || !isset($res['data']['objOpenId'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['objUcid'] = $res['data']['objUcId'];//查看者的ucid
            $params['objOpenId'] = $res['data']['objOpenId'];//查看者的openId
            $ret = $this->service('Space')->getCounts($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 我跟待查看的好友共同看过的电影
     */
    public function watchSameMovies()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        //从cookie中获取openid
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['objChannelId'] = $this->getRequestParams($this->channelIdKey,'');
        if (empty($params['openId'])) {
            $ret=$this->getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }
        else{
            $res=$this->getUserOpenId();
            if($res['ret']!=0){
               $ret=$res;
            }
            elseif(!isset($res['data']['objOpenId']) || empty($res['data']['objOpenId'])){
                $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
            }
            else{
                $params['objOpenId'] = $res['data']['objOpenId'];//查看者的openId
                $ret = $this->service('Space')->watchSameMovies($params);
            }
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取他人的背景图
     * 根据ucid取模
     */
    public function backGround(){
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $background=[
            'https://baymax-cos.wepiao.com/upload/usercenter/homebg/1_2x.jpg',
            'https://baymax-cos.wepiao.com/upload/usercenter/homebg/2_2x.jpg',
            'https://baymax-cos.wepiao.com/upload/usercenter/homebg/3_2x.jpg',
            'https://baymax-cos.wepiao.com/upload/usercenter/homebg/4_2x.jpg',
        ];
        $params['objChannelId'] = $this->getRequestParams($this->channelIdKey,'');//被访问者的channelId
        $params['openId'] = $this->getRequestParams($this->ucidKey,'');//名字叫openId，实际上是ucid
        if(empty($params['openId'])){
            $ret=self::getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['background']=$background;
            $ret=$this->service('Space')->backGround($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取他人的观影轨迹
     */
    public function getUserTrace(){
        $params['channelId'] = CHANNEL_ID;
        $params['objChannelId'] = $this->getRequestParams($this->channelIdKey,'');
        $res=$this->getUserOpenId();
        if($res['ret']!=0){
            $ret=$res;
        }
        elseif(!isset($res['data']['objUcId']) || empty($res['data']['objToken'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['objUcid'] = $res['data']['objUcId'];//查看者的openId
            $params['objToken'] = $res['data']['objToken'];//查看者的openId
            $params['page'] = $this->getRequestParams("page", 1);
            $params['num'] = $this->getRequestParams("num", 10);
            $ret = $this->service('Space')->getUserTrace($params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取他人喜欢的影人
     */
    public function likeActor()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['objChannelId'] = $this->getRequestParams($this->channelIdKey,'');
        $params['page'] = $this->getRequestParams('page', 1);
        $params['num'] = $this->getRequestParams('num',5);
        $res=$this->getUserOpenId();
        if($res['ret']!=0){
            $ret=$res;
        }
        elseif(!isset($res['data']['objOpenId']) || empty($res['data']['objOpenId'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['openId'] = $res['data']['objOpenId'];//查看者的openId
            $ret = $this->service('Space')->getUserActor($params);
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
        $params['page'] = $this->getRequestParams("page", 1);
        $params['num'] = $this->getRequestParams("num", 10);
        $res=$this->getUserOpenId();
        if($res['ret']!=0){
            $ret=$res;
        }
        elseif(!isset($res['data']['objOpenId']) || empty($res['data']['objOpenId'])){
            $ret=$this->getErrorOut(ERRORCODE_USER_CENTER_MISS_OPENID_ERROR);
        }
        else{
            $params['openId'] = $res['data']['objOpenId'];//查看者的openId
            $ret = $this->service('Space')->getUserMovieGuideList($params);
        }
        $this->jsonOut($ret);
    }


}
