<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
/**
 * app用户资料类
 */

namespace app\controllers;

use app\base\BaseController;

class AppUserController extends BaseController
{
    //用户好友个人主页
    public function friend()
    {
        $return = $this->stdOut();
        $params = [];
        $params['channelId'] = $this->getRequestParams("channelId");
        $params['uid'] = $this->getRequestParams("uid");
        $params['openId'] = $this->getRequestParams("ucId");
        //查询出用户的基本资料如果设置uid则uid优先查询个人信息
        //因为评论的头像本身就拼接了nfs地址所以两个头像处理方式略有不同
        $profile = [];
        if (empty($params['uid'])) {
            $response = $this->sdk->call("comment/get-userinfo-by-ucid", $params);
            if ($response['ret'] == 0) {
                $profile['uid'] = $response['data']['uid'];
                $profile['photo'] = $response['data']['photo'];
                $profile['nickName'] = $response['data']['nickName'];
                //判断头像是否需要拼接cdn地址(针对uid用户)
                $profile['photo'] = str_replace("http://", "https://", $profile['photo']);
            }
        } else {
            $response = $this->sdk->call("user/get-userinfo-by-uid", $params);
            if ($response['ret'] == 0) {
                $profile['uid'] = $response['data']['UID'];
                $profile['photo'] = $response['data']['photoUrl'];
                $profile['nickName'] = $response['data']['nickName'];
                $profile['city'] = $response['data']['city'];
                $profile['sex'] = $response['data']['sex'];
                $profile['birthday'] = $response['data']['birthday'];
                //判断头像是否需要拼接cdn地址(针对uid用户)
                $profile['photo'] = str_replace("http://", "https://", $profile['photo']);
            }
        }
        //获取用户认证信息
        $tagResponse = $this->sdk->call('user/get-user-tag', $params);
        if (!empty($tagResponse['data'])) {
            $profile['authType']['summary'] = $tagResponse['data']['summary'];
            $profile['authType']['is_star'] = $tagResponse['data']['is_star'];
        } else {
            $profile['authType'] = new \stdClass();
        }
        //生成朋友的token方便调取用户中心
        $token = $this->service("user")->createToken($params['channelId'], $params['uid'], $params['openId']);

        //获取用户十条想看数量
        $want = $this->service("ucc")->getUserWantList($params['channelId'], 1, 10, $token, true);
        //获取用户十条观影轨迹
        $param = [
            'channelId' => $params['chinannelId'],
            'openId' => $params['openId'],
            'token' => $token,
        ];
        $response = $this->sdk->call("user/get-trace-path", $param);
        $trace = $response['data'];
        if (!empty($response['data']['trace'])) {
            array_walk($response['data']['trace'], function (&$item) {
                if (!empty($item['order'])) {
                    $item['order'] = new \stdClass();
                }
            });
        }

        $trace = $response['data'];
        $data = [];
        $data['profile'] = $profile;
        $data['want'] = $want;
        $data['trace'] = $trace;
        $return['data'] = $data;
        $this->jsonOut($return);
    }

    public function feedback()
    {
        $return = $this->stdOut();
        $params = [
            'uid' => \wyCupboard::$user['uid'],
            'mobileNo' => $this->getRequestParams("mobileNo"),
            'content' => $this->getRequestParams("content"),
            'version' => $this->getRequestParams("appver"),
            'channelId' => $this->getRequestParams("channelId"),
            'fromId' => $this->getRequestParams("fromId"),
            'device' => $this->getRequestParams("device"),
            'os' => $this->getRequestParams("os"),
            'network' => $this->getRequestParams("network"),
            'created' => time(),
            'updated' => time(),
        ];
        $response = $this->sdk->call("feedback/add-feedback", $params);
        if (!$response) {
            $return['errcode'] = -111002;
        }
        $this->jsonOut($return);
    }
}