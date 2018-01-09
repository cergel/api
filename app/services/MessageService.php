<?php
/**
 * create by bbq
 * date:2016.10.26
 * 消息中心Service
 */
namespace app\services;


use app\base\BaseService;

class MessageService extends BaseService
{
    /**
     * 获取红点提醒位置和状态
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getRedPoint($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("message/get-red-point", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }
    
    /**
     * 清除某个位置红点提醒状态
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function clearRedPoint($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['position'] = !empty($arrParams['position']) ? $arrParams['position'] : '';
        if (!empty($params['channelId']) && !empty($params['position']) ) {
            $res = $this->sdk->call("message/clear-red-point", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }

    /**
     * 获取消息列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getMessageList($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("message/get-message-list", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }

        return $return;
    }

    /**
     * 获取消息列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getToast($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (!empty($arrParams['channelId'])) {
            $res = $this->sdk->call("message/get-discount-card-message-once", $arrParams);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取某种类型消息列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getTypeMessageList($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['type'] = !empty($arrParams['type']) ? $arrParams['type'] : 0;
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['page'] = $arrParams['page'];
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("message/get-message-type-list", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 获取某种类型消息列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getMessageView($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['msgId'] = !empty($arrParams['msgId']) ? $arrParams['msgId'] : '';
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("message/message-view", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

}