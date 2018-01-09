<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/9/7
 * Time: 19:49
 */

namespace app\models;
use app\base\BaseModel;

class HomePage extends BaseModel
{
    protected $redisType = GROUP_SHARE_FREQUENT;

    /**
     * 获取后台设置的背景图信息
     * @param $channelId
     * @return bool|string
     */
    public function getBackgroud($channelId){
        $redis = $this->getRedis($this->redisType);
        $keyTemplate = "homepage_background:{#channelId}";
        $input = ['channelId' => $channelId];
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $redis->WYget($redisKey);
    }
}