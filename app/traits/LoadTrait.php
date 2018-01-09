<?php
namespace app\traits;
trait LoadTrait
{
    /**
     * 调用service
     * @param $serviceName
     * @return MovieService|UserService|CinemaService|LoginService|SmsService|AnnounceService
     */
    protected function service($serviceName)
    {
        $class = CLASS_PREFIX . "\\services\\" . $serviceName . 'Service';
        if (empty(\instanceVendor::$_arrInstance[$class])) {
            \instanceVendor::$_arrInstance[$class] = new $class;
        }

        return \instanceVendor::$_arrInstance[$class];
    }

    //实例化model
    protected function model($modelName)
    {
        $class = CLASS_PREFIX . "\\models\\" . $modelName;
        if (empty(\instanceVendor::$_arrInstance[$class])) {
            \instanceVendor::$_arrInstance[$class] = new $class;
        }

        return \instanceVendor::$_arrInstance[$class];
    }
}