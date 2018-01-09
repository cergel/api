<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/21
 * Time: 17:36
 */
namespace app\base;
abstract class BaseFilter{


    protected function getReturn(){
        return ['ret'=>0,'sub'=>0];
    }

    //初始化方法， 如果有需要可以重写他， 比如放一些配置
    public function init()
    {

    }

    //基类的验证方法， 每个过滤器都要去实现
    abstract public function filter();
}