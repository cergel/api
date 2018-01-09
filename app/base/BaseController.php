<?php
namespace app\base;

use app\services\AnnounceService;
use app\services\BonusService;
use app\services\CinemaVipService;
use app\services\ExchangeService;
use app\services\FavoriteService;
use app\services\MovieGuideService;
use app\services\OrderService;
use app\services\PointCardService;
use app\services\RedSpotService;
use app\services\ScheService;
use app\services\SearchService;
use app\services\SnackService;
use app\services\SpaceService;
use app\services\TicketService;
use app\services\UserService;
use app\services\LoginService;
use app\services\MovieService;
use app\services\CinemaService;
use app\services\SmsService;
use app\services\WechatService;

/**
 * 基类controller，提供几个常用函数
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/3
 * Time: 15:49
 */
class BaseController extends Base
{
    
    public $logger;
    protected $sdk;
    
    public function __construct()
    {
        //初始化logger对象与SDK组件
        $this->sdk = \wyCupboard::$sdk;
    }
    
    //获取request参数
    protected function getRequestParams($index, $default = null)
    {
        if (array_key_exists($index, \wyCupboard::$request->params)) {
            return \wyCupboard::$request->params[$index];
        }
        else {
            return $default;
        }
    }
    
    /**
     * @param $serviceName
     *
     * @return MovieService|UserService|SpaceService|CinemaService|LoginService|AnnounceService|SmsService|OrderService|TicketService|CinemaVipService|ScheService|SnackService|SearchService|ExchangeService|BonusService|SmsService|WechatService|PointCardService|FavoriteService|MovieGuideService|RedSpotService
     */
    protected function service($serviceName)
    {
        $class = CLASS_PREFIX . "\\services\\" . $serviceName . 'Service';
        if (empty( \instanceVendor::$_arrInstance[$class] )) {
            \instanceVendor::$_arrInstance[$class] = new $class;
        }
        
        return \instanceVendor::$_arrInstance[$class];
    }
    
    /**
     * 获取SPM参数,优先取cookie,cookie没有,从REQUEST中取
     */
    public function getSpmParam()
    {
        $strSpm = !empty( $_COOKIE['_wepiao_spm'] ) ? $_COOKIE['_wepiao_spm'] : '';
        $strSpm = empty( $strSpm ) ? $this->getRequestParams('_wepiao_spm') : $strSpm;
        
        return $strSpm;
    }
    
}