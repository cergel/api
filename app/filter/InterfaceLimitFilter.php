<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/21
 * Time: 17:35
 */

namespace app\filter;
use app\base\BaseFilter;

class InterfaceLimitFilter extends BaseFilter{
    protected $redisConf;

    public function init(){
        //注：因为redis配置已经在api项目中去除，所以这里想重新用的话需要再加回redis配置
        $this->redisConf=\wyCupboard::$config['redis'][STATIC_MOVIE_DATA]['common'];
        //$this->redisConf = \wyCupboard::$config['ipLimit']['redisConf'];
    }

    public function filter(){
        $controller = \wyCupboard::$request->controller;
        $action = \wyCupboard::$request->action;
        $config = \wyCupboard::$config['interfaceLimit'];
        $return = $this->getReturn();
        if(isset($config['rule'][$controller][$action])){
            $redisConf = $this->redisConf;
            $second = $config['rule'][$controller][$action]['second'];
            $times = $config['rule'][$controller][$action]['times'];
            $ip = \app\helper\Net::getRemoteIp();
            $redis=\redisManager\redisManager::getInstance($redisConf);
            $redisKey = "interfaceLimit:".$controller."_".$action;
            $num = $redis->WYget($redisKey);
            if($num==1){
                $redis->WYexpire($redisKey,$second);
            }
            $redis->WYincr($redisKey);
            if($num>$times){
                $return = ['ret'=>-1,'sub'=>-1,'请求过于频繁'];
            }
        }
        return $return;
    }
}