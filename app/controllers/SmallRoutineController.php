<?php

/**
 * 小程序
 */

namespace app\controllers;

use app\base\BaseController;

class SmallRoutineController extends BaseController
{

    /**
     * 获取题目详情信息
     */
    public function getAProblemItem($problemId = '', $repoId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['repoId'] = $repoId;
        $params['itemId'] = $problemId;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = WX_SALE_APP_ID;
        $ret = $this->sdk->call('small-routine/get-a-problem-item', $params);
        if (empty($ret['data'])) {
            $ret['data'] = new \stdClass();
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取题库信息
     */
    public function getProblemRepos()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = WX_SALE_APP_ID;
        $ret = $this->sdk->call('small-routine/get-problem-repos', $params);
        $this->jsonOut($ret);
    }

    /**
     * 获取某个指定的题库信息
     */
    public function getAProblemRepo($repoId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['repoId'] = $repoId;
        $ret = $this->sdk->call('small-routine/get-a-problem-repos', $params);
        if (empty($ret['data']) && is_array($ret['data'])) {
            $ret['data'] = new \stdClass();
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取一个随机的题目信息
     */
    public function getRandomProblemItem($repoId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['repoId'] = $repoId;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $ret = $this->sdk->call('small-routine/get-random-problem-item', $params);
        if (empty($ret['data'])) {
            $ret['data'] = new \stdClass();
        }
        $this->jsonOut($ret);
    }

    /**
     * 提交答案
     */
    public function checkProblemAnswer($problemId = '', $repoId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['itemId'] = $problemId;
        $params['repoId'] = $repoId;
        $params['answer'] = $this->getRequestParams('answer', '');
        $params['reward'] = $this->getRequestParams('reward', 1);   //是否发奖（0为不发奖）
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        if(empty($params['openId'])){
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->sdk->call('small-routine/check-problem-answer', $params);
        }
        $this->jsonOut($ret);
    }

    /**
     * 获取用户答对的题目总数
     */
    public function getUserCrackCount()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->sdk->call('small-routine/get-user-crack-count', $params);
        $this->jsonOut($ret);
    }

    /**
     * 获取某个题目被破解次数
     */
    public function getProblemCrackCount($problemId = '', $repoId = '')
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['itemId'] = $problemId;
        $params['repoId'] = $repoId;
        $params['answer'] = $this->getRequestParams('answer', '');
        $params['reward'] = $this->getRequestParams('reward', 1);   //是否发奖（0为不发奖）
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->sdk->call('small-routine/get-problem-crack-count', $params);
        $this->jsonOut($ret);
    }

    /**
     * 根据文字，获取emoji标识符
     */
    public function getEmojiesByText()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['text'] = $this->getRequestParams('text', '');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $ret = $this->sdk->call('small-routine/get-emojies-by-text', $params);
        $this->jsonOut($ret);
    }

    /**
     * 创建用户级别的题目
     */
    public function createEmojiesProblem()
    {
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['subChannelId'] = WX_SALE_APP_ID;
        $params['text'] = $this->getRequestParams('text', '');
        $params['awardType'] = $this->getRequestParams('awardType', 0);
        $params['emojiIds'] = $this->getRequestParams('emojiIds', '');
        $params['emojiToken'] = $this->getRequestParams('emojiToken', '');
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['formId'] = $this->getRequestParams('formId', '');
        $ret = $this->sdk->call('small-routine/create-emojies-problem', $params);
        $this->jsonOut($ret);
    }


}