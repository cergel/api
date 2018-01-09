<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/13
 * Time: 下午1:18
 */

namespace app\filter;

use app\base\BaseFilter;
use app\services\LoginService;
use app\traits\LoadTrait;

class RequestTokenFilter extends BaseFilter
{
    use LoadTrait;

    public function filter()
    {
        // TODO: Implement filter() method.
        $controller = \wyCupboard::$request->controller;
        $action = \wyCupboard::$request->action;
        $paramsConfig = \wyCupboard::$config['requestTokenConfig'];
        if ($paramsConfig['switch'] === "off") {
            return $this->getReturn();
        }

        if (isset($paramsConfig['required'][$controller]) && in_array($action, $paramsConfig['required'][$controller])) {
            //如果需要登录的接口检查token是否校验成功
            $user =  $this->service('Login')->getAppUserInfo();
            if (!$user['valid']) {
                return ['ret' => -4444, 'sub' => -1, 'msg' => '请您登录!'];
            } else {
                return $this->getReturn();
            }
        }

    }
}