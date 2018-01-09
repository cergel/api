<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
/**
 * app 模块 补丁 tu类
 */

namespace app\controllers;

use app\base\BaseController;

class AppResourceController extends BaseController
{
    /**
     * 配置APP的模块开关信息
     */
    public function getModuleSwitch()
    {
        $ret = $this->sdk->call("app-resource/get-module-switch");
        $this->jsonOut($ret);
    }

    /**
     * 配置APP的模块开关信息
     * */
    public function getPatch()
    {
        $params['channelId'] = CHANNEL_ID;
        //过滤指定的渠道
        if (in_array($params['channelId'], \wyCupboard::$config['app_get_patch_filter_list'])) {
            $this->jsonOut($this->getStOut());
        }

        $params['appver'] = $this->getRequestParams("appver");
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->sdk->call("app-resource/get-module-switch", $params);

        $this->jsonOut($ret);
    }

    /**
     * 取得Android最新版本
     */
    public function getVersionRelease()
    {
        //优先判断渠道是否正确
        if (CHANNEL_ID != 8 && CHANNEL_ID != 9 || empty($appver)) {
            $this->jsonError(ERRORCODE_ERROR_PARAM);
        }

        $arrInput = [];
        $arrInput['channelId'] = CHANNEL_ID;
        $arrInput['appver'] = $this->getRequestParams('appver');
        $arrInput['versionCode'] = $this->getRequestParams('versionCode', '');
        $ret = $this->sdk->call("app-resource/get-version-release", $arrInput);

        $this->jsonOut($ret);
    }


    //明星选座
    public function getCustomization()
    {
        $outputJson = $this->getStOut();
        //判断缓存中是否有信息如果由信息直接返回
        $ret = $this->sdk->call("app-resource/get-customization-seat", ['channelId' => CHANNEL_ID,]);

        if (empty($ret)) {
            $ret = [];
        }
        $outputJson['data'] = $ret;
        $this->jsonOut($outputJson);
    }


    //获取七日日签
    public function getDaysign()
    {
        $ret = $this->getStOut();
        $stdRet = $this->sdk->call("app-resource/get-daysign", ['channelId' => CHANNEL_ID,]);
        $ret['data'] = json_decode($stdRet);
        $this->jsonOut($ret);

    }

    //影片商业化详情
    public function getBiz()
    {
        $params['channelId'] = $this->getRequestParams("channelId");
        $ret = $this->sdk->call("app-resource/get-biz-list", $params);
        $this->jsonOut($ret);
    }

    //app热补丁
    public function jspatch()
    {
        $param = [];
        $params['appver'] = $this->getRequestParams("appver");
        $params['openId'] = $this->getRequestParams("openId");
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->sdk->call("app-resource/get-patch", $params);
        $this->jsonOut($ret);
    }


    //按月输出日签
    public function getDaySignMonth($month)
    {
        $return = $this->getStOut();
        $params['month'] = $month;
        $params['channelId'] = $this->getRequestParams("channelId", 8);
        $return['data'] = $this->sdk->call("app-resource/get-day-sign-month", $params);
        $this->jsonOut($return);
    }

    /**
     * app获取支付后红包数量
     * @author CHAIYUE
     */
    public function getRedPacketNum()
    {
        $return = $this->getStOut();
        $params['channelId'] = $this->getRequestParams("channelId");
        $return['data']['num'] = $this->sdk->call("app-resource/get-red-packet-num", $params);
        $this->jsonOut($return);
    }
    //获取历史日签 去年今天
    public function getDaySignLastYear()
    {
        $return = $this->getStOut();
        $day = date("Ymd",strtotime("-1 year"));
        $month = date('Ym',strtotime("-1 year"));
        $params['day'] = $day;
        $params['month'] = $month;
        $params['channelId'] = $this->getRequestParams("channelId", 8);
        $return['data'] = $this->sdk->call("app-resource/get-day-sign-lastyear", $params);
        $this->jsonOut($return);
    }
}