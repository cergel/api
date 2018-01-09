<?php
/**
 * Created by PhpStorm.
 * User: bbq
 * Date: 2017/3/6
 * Time: 20:03
 */

namespace app\services;

use app\base\BaseService;

/**s
 * Class serviceCommentNew
 * @package sdkService\service
 */
class ResourceService extends BaseService
{
    //获取首页图标列表
    public function getIconConfig($arrInput)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (!empty($arrInput['channelId'])) {
            $res = $this->sdk->call("Resource/get-icon-config", $arrInput);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    //获取商业化影片详情
    public function getBizList($channelId)
    {
        return $this->model('Appresource')->getBizList($channelId);
    }

    //获取app模块开关
    public function getAppModuleSwitch($channelId)
    {
        return $this->model('Appresource')->getAppModuleSwitch($channelId);
    }

    //获取热更新补丁
    public function getPatch($channelId, $appver, $openId)
    {
        return $this->model('Appresource')->getPatch($channelId, $appver, $openId);
    }

    //获取首页图标列表
    public function getIconConfigV2($arrInput)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (!empty($arrInput['channelId'])) {
            $res = $this->sdk->call("resource/get-icon-config-v2", $arrInput);
            if (!empty($res) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 格瓦拉侧,优惠调控弹出内容
     * @param $money
     */
    public function alertDiscountMessage($money)
    {
        $moneyLimit = 4000;
        $Message = '';
        if ($money > $moneyLimit) {
            $Message = '该场次为特殊场次，不参加特价抢票活动';
        }
        return $Message;
    }
}