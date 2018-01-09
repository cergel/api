<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/8/18
 * Time: 18:19
 */

namespace app\services;

use app\base\BaseService;

class PointCardService extends BaseService
{
    
    /**
     * 获取点卡信息
     *
     * @param $strOpenId
     *
     * @return mixed
     */
    public function getPointCardInfo($params = [])
    {
        return $this->sdk->call('card/get-point-card-info', $params);
    }
    
    
}