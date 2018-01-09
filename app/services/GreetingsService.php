<?php

namespace app\services;


use app\base\BaseService;

class GreetingsService extends BaseService
{
    public function getGreet($arrParams)
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        if (!empty($params['channelId'])) {
            $res = $this->sdk->call("star-greeting/get-greet", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }
}