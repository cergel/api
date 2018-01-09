<?php

namespace app\services;


use app\base\BaseService;

class WechatService extends BaseService
{
    
    /**
     * 获取用户是否关注了公众号
     *
     * @param $iActorId
     * @param $iMovieInfo
     *
     * @return mixed
     */
    public function getFollowStatus($arrParams)
    {
        $arrParams['channelId'] = CHANNEL_ID;
        $res = $this->sdk->call('user/check-user-is-follo-movie', $arrParams);
        
        return $res;
    }
    
}