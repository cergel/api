<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/21
 * Time: 17:35
 */

namespace app\filter;

use app\base\BaseFilter;

class RequestParamsFilter extends BaseFilter
{
    public function init()
    {
    }

    public function filter()
    {
        $controller = \wyCupboard::$request->controller;
        $action = \wyCupboard::$request->action;
        $paramsConfig = \wyCupboard::$config['requestParamsConfig'];

        //如果有此方法的配置则要进行验证
        if (isset($paramsConfig[$controller][$action])) {
            foreach ($paramsConfig[$controller][$action] as $k => $v) {
                if ($arrFunName = explode("|", $v)) {
                    foreach ($arrFunName as $funName) {
                        $fun = 'filter' . ucfirst($funName);
                        $filterRe = $this->$fun($k);
                        if ($filterRe['ret'] != 0 || $filterRe['sub'] != 0) {
                            return $filterRe;
                        }
                    }
                } else {
                    $fun = 'filter' . ucfirst($v);
                    $filterRe = $this->$fun($k);
                    if ($filterRe['ret'] != 0 || $filterRe['sub'] != 0) {
                        return $filterRe;
                    }
                }
            }
        }
        return $this->getReturn();
    }

    //验证参数必传
    protected function filterRequire($inputParam)
    {
        if (!isset(\wyCupboard::$request->params[$inputParam]) || empty(\wyCupboard::$request->params[$inputParam])) {
            return ['ret' => -1, 'sub' => -1, 'msg' => 'require ' . $inputParam];
        } else {
            return $this->getReturn();
        }
    }

    //对参数进行xss过滤
    protected function filterXss($inputParam)
    {
        \wyCupboard::$request->params[$inputParam] = strip_tags(\wyCupboard::$request->params[$inputParam]);
        return $this->getReturn();
    }
}