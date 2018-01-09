<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/3
 * Time: 15:50
 */
namespace app\base;

use app\services\AnnounceService;
use app\services\CinemaService;
use app\services\LoginService;
use app\services\MovieService;
use app\services\SmsService;
use app\services\UserService;


class BaseService extends Base
{
    protected $sdk;

    public function __construct()
    {
        //初始化logger对象与SDK组件
        $this->sdk = \wyCupboard::$sdk;
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

    /**
     * 参数转换，转换输入参数为Java接口需要的参数，并销毁部分内容
     *
     * @param  array $arrInput
     * @param  array $arrConvertMap 需要转换的Map
     * @param  array $arrDefaultValues
     * @param  array $arrUnsetMap 需要unset的map
     *
     * @return array
     */
    public function convertInputAndUnset(&$arrInput = [], $arrConvertMap = [], $arrUnsetMap = [], $arrDefaultValues = [])
    {
        //参数转换
        if (!empty($arrConvertMap)) {
            foreach ($arrConvertMap as $strKey => $strValue) {
                $arrInput [$strValue] = isset ($arrInput [$strKey]) ? $arrInput [$strKey] : (isset ($arrDefaultValues [$strKey]) ? $arrDefaultValues [$strKey] : '');
                if (isset($arrInput[$strKey])) {
                    unset($arrInput[$strKey]);
                }
            }
        }
        //参数销毁
        if (!empty($arrUnsetMap)) {
            foreach ($arrUnsetMap as $unsetKey => $unsetVal) {
                unset($arrInput[$unsetVal]);
            }
        }
    }
}