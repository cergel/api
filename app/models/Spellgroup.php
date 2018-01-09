<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/9/7
 * Time: 19:49
 */

namespace app\models;
use app\base\BaseModel;

class SpellGroup extends BaseModel
{
    protected $redisType = GROUP_SHARE_FREQUENT;
    /**
     * 根据订单号查询用户参与拼团活动的active_id及Team_id
     * @param $order_id
     * @return mixed
     */
    public function queryOrderPintuan($order_id){
        $redisKey = "order_info_team_{$order_id}";
        $res=$this->getRedis()->WYget($redisKey);
        return $res;
    }
}