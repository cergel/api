<?php
/**
 * Created by PhpStorm.
 * User: tanjunlin
 * Date: 2016/7/14
 * Time: 18:28
 */
namespace app\controllers;

use app\base\BaseController;

class ResourceController extends BaseController
{
    /**
     * 公告接口可以传入影院ID以及指定的位置
     * 采用ServiceSDK方式接入
     *
     */
    public function getAnnounce()
    {
        $cinemaId = $this->getRequestParams('cinemaId');
        $params = [
            'channelId' => $this->getRequestParams('channelId'),
            'position' => $this->getRequestParams('position'),
            'cinemaId' => isset($cinemaId) ? $cinemaId : 0,
        ];
        $arrData = $this->sdk->call('announce/get-announce', $params);
        $this->jsonOut($arrData);
    }

    /**
     * 明星选座功能
     */
    public function getCustomization()
    {
        $outputJson = ['errorcode' => 0, 'result' => []];
        //判断缓存中是否有信息如果由信息直接返回
        $Ret = $this->service('resource')->getResource();
        if (empty($Ret)) {
            $Ret = [];
        }
        $outputJson['result']['data'] = $Ret;
        $this->jsonOut($outputJson);
    }

    /**
     * 新版日签分页接口
     */
    public function getCalendar()
    {
        $outputJson = ['errorcode' => 0, 'result' => []];
        $channelId =
            //读取首页数据
        $Ret = $this->service('resource')->getDaySignModulePaging();
        $outputJson['result']['data'] = $Ret;
        $this->jsonOut($outputJson);
    }

    public function getIcon()
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;
        $Ret = $this->service('Resource')->getIconConfig($param);
        $this->jsonOut($Ret);
    }

    public function getIconV2()
    {
        $param = [];
        $param['channelId'] = CHANNEL_ID;
        $Ret = $this->service('Resource')->getIconConfigV2($param);
        $this->jsonOut($Ret);
    }
}