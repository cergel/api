<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/9/7
 * Time: 19:49
 */

namespace app\models;
use app\base\BaseModel;

class Space extends BaseModel
{
    protected $redisType = GROUP_SHARE_FREQUENT;

    /**
     * 获取某个ucid对应的openid信息
     * @param $order_id
     * @return mixed
     */
    public function getOpenInfoByUcid($key){
        $redisKey = "openid_token_{$key}";
        $res=$this->getRedis()->WYget($redisKey);
        return $res;
    }

    /**
     * 设置某个ucid对应的openid信息
     * @param $order_id
     * @return mixed
     */
    public function setOpenInfoByUcid($key,$data){
        $redisKey = "openid_token_{$key}";
        $res=$this->getRedis()->WYset($redisKey,$data,180);//缓存3分钟
        return $res;
    }
}