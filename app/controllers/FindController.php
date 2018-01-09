<?php
/**
 * 发现相关
 * User: liulong
 * Date: 16/11/9
 * Time: 上午10:00
 */
namespace app\controllers;

use app\base\BaseController;

class FindController extends BaseController
{

    /**
     * 获取发现头部信息
     */
    public function getFindInfo()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['cityId'] = $this->getRequestParams('cityId');
        $params['type'] = $this->getRequestParams('type');
        $ret = $this->service('Find')->getFindInfo($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取发现列表
     * @param $typeId
     */
    public function getFindList($typeId)
    {
        $params = [];
        $params['type'] = $this->getRequestParams('type');
        $params['typeId'] = $typeId;
        $params['channelId'] = CHANNEL_ID;
        $params['page'] = $this->getRequestParams('page',1);
        $params['num'] = $this->getRequestParams('num',5);
        $ret = $this->service('Find')->getFindList($params);
        $this->jsonOut($ret);
    }

    /**
     * 获取发现导流 baymax-微信-发现导流
     */
    public function getFindGuide()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $ret = $this->service('Find')->getFindGuide($params);
        $this->jsonOut($ret);
    }
}