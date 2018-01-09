<?php

namespace app\controllers;

use app\base\BaseController;

class MessageController extends BaseController
{
    public function getRedPoint(){
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['cityId'] = $this->getRequestParams("cityId", '');
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Message')->getRedPoint($params);
        }
        
        $this->jsonOut($ret);
    }

    public function clearRedPoint(){
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['position'] = $this->getRequestParams("position");
        if (empty( $params['position'] )) {
            $this->jsonError(ERRORCODE_ERROR_PARAM);
        }
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        } else {
            $ret = $this->service('Message')->clearRedPoint($params);
        }
        $this->jsonOut($ret);
    }

    /*获取消息列表首页 */
    public function messageList()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Message')->getMessageList($params);
        }
        $this->jsonOut($ret);
    }

    public function messageType($type)
    {
        $params['channelId'] = CHANNEL_ID;
        $params['type'] =$type;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['page'] = $this->getRequestParams("page", 1);
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Message')->getTypeMessageList($params);
        }
        $this->jsonOut($ret);
    }

    //获取消息体
    public function messageView($msgId)
    {
        $return = $this->stdOut();
        $params = [];
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        $params['channelId'] = CHANNEL_ID;
        $params['msgId'] =$msgId;
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Message')->getMessageView($params);
        }
        $this->jsonOut($return);
    }

    public function getToast()
    {
        $params['channelId'] = CHANNEL_ID;
        $params['openId'] = $this->service('Login')->getOpenIdFromCookie();
        //重新登录
        if (empty( $params['openId'] )) {
            $ret = self::getErrorOut(ERRORCODE_ERROR_RELOGIN);
        }else{
            $ret = $this->service('Message')->getToast($params);
        }
        $this->jsonOut($ret);
    }
}