<?php
ob_start();
ini_set("display_errors", "off");
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

defined('DS') || define('DS', DIRECTORY_SEPARATOR);//定义系统分割路径
define('APP_DIR', realpath(__DIR__ . '/../app/') . DS);//定义APP目录的路径
define('CONFIG_DIR', realpath(__DIR__ . '/../app/config/') . DS);//定义配置文件路径
define('SYSTEM_DIR', realpath(__DIR__ . '/../system/') . DS);//定义系统目录路径
define('ROOT_DIR', realpath(__DIR__ . '/../') . DS);//定义根路径
// composerAutoload 自动载入
require '../vendor/autoload.php';
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (strpos($origin, 'wepiao.com') !== false) {
    header('Access-Control-Allow-Origin:' . $origin);
    header('Access-Control-Allow-Credentials: true');//设置响应头，让客户端可以带上cookie
    header("Access-Control-Allow-Headers:X-Requested-With,X-Request-Id");
    // 告诉浏览器我支持这些方法(后端不支持的方法可以从这里移除，当然你也可以在后边加上OPTIONS方法。。。)
    header("Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS");
    // 告诉浏览器我已经记得你了，一天之内不要再发送OPTIONS请求了
    header('Access-Control-Max-Age: ' . 3600 * 24);
}
//获取请求方法
$httpMethod = $_SERVER['REQUEST_METHOD'];
if ($httpMethod == 'OPTIONS') {
    die('options request');
}

$uri = $_SERVER['REQUEST_URI'];
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
//载入路由
$routes_info = require '../app/config/routes.php';
//匹配路由
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
loader::run($routeInfo);

//一个sdk的壳子，需要记录sdk入参可以放到这里
class wyCupboardSdk
{
    public static $_self = null;
    public static $sdk = null;

    /**
     * @var \Logger\Logger
     */
    public static $loggerId = '';

    public static function getInstance($sdk)
    {
        if (self::$_self === null) {
            self::$sdk = $sdk;
            self::$_self = new self();
        }
        return self::$_self;
    }

    /**
     * 是否记录service日志
     *
     * @param $fun
     *
     * @return bool
     */
    protected static function notLogService($fun)
    {
        return false;
        $unLogger = true;
        $services = (isset(\wyCupboard::$config['logExclude']['service']) && !empty(\wyCupboard::$config['logExclude']['service'])) ? \wyCupboard::$config['logExclude']['service'] : [];
        if (in_array($fun, $services)) {
            $unLogger = false;
        }

        return $unLogger;
    }

    public function call($fun, $params)
    {
        if (!empty(self::$loggerId)) {
            $params['logId'] = self::$loggerId;
        }
        $fun = strtolower($fun);
        $return = self::$sdk->call($fun, $params);
        $arrLog = [
            'serviceName' => $fun,
            'params' => $params,
        ];
        if (self::notLogService($fun)) {
            $arrLog['return'] = $return;
        }
        \wyCupboard::$logger->addRequestOtherNode($arrLog);

        return $return;
    }
}

//此类用于保存全局配置
class wyCupboard
{
    static $config;
    static $request;
    static $channelId;
    static $logger;
    static $sdk;
}

//单例容器
class instanceVendor
{
    static $_arrInstance = [];

    static function getInstance($className)
    {
        if (!isset(self::$_arrInstance[$className])) {
            self::$_arrInstance[$className] = new $className;
        }

        return self::$_arrInstance[$className];
    }
}

class loader
{
    //定义配置
    protected static function setConf($uriInfo)
    {
        if (isset($uriInfo[2]) && $uriInfo[2] == 'modules') {
            \wyCupboard::$request->module = $uriInfo[3];
            list($oldController, $oldAction) = explode('@', $uriInfo[5]);
            $newController = str_replace("Controller", '', $oldController);
            $newAction = str_replace("Action", '', $oldAction);
            \wyCupboard::$request->controller = $newController;
            \wyCupboard::$request->action = $newAction;
            define("MODULE_DIR", APP_DIR . "modules" . DS . $uriInfo[3] . DS);
            \wyCupboard::$config = require(MODULE_DIR . 'config' . DS . 'config.php');
            define("CLASS_PREFIX", "\\app\\modules\\{$uriInfo[3]}");
        } else {
            \wyCupboard::$request->module = null;
            list($oldController, $oldAction) = explode('@', $uriInfo[3]);
            $newController = str_replace("Controller", '', $oldController);
            $newAction = str_replace("Action", '', $oldAction);
            \wyCupboard::$request->controller = $newController;
            \wyCupboard::$request->action = $newAction;
            \wyCupboard::$config = require(APP_DIR . 'config' . DS . 'config.php');
            define("CLASS_PREFIX", "\\app");
        }
    }

    //保存请求参数
    protected static function setRequestParams()
    {
        $vars = null;
        // 自动获取请求变量
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $vars = array_merge($_GET, $_POST);
                break;
            case 'PUT':
                static $_PUT = null;
                if (is_null($_PUT)) {
                    parse_str(file_get_contents('php://input'), $_PUT);
                }
                $vars = array_merge($_GET, $_PUT);
                break;
            case 'DELETE':
                static $_DELETE = null;
                if (is_null($_DELETE)) {
                    parse_str(file_get_contents('php://input'), $_DELETE);
                    $vars = array_merge($_DELETE, $_GET);
                }
                break;
            default:
                $vars = $_GET;
        }
        if(!strpos($_SERVER['REQUEST_URI'],'proxy')){
            \wyCupboard::$request->params = $vars;
            if (empty($vars['channelId'])) {
                //channelId必传，否则报错
                header('Content-type: application/json');
                echo json_encode([
                    'ret' => '1111',
                    'sub' => '1111',
                    'msg' => 'The channelId is required !',
                ]);
                die;
            } else {
                \wyCupboard::$channelId = $vars['channelId'];
                defined("CHANNEL_ID") or define("CHANNEL_ID", \wyCupboard::$channelId);
            }
        }
    }

    //初始化logger
    protected static function initLogger()
    {
        //初始logger
        $controllerID = \wyCupboard::$request->controller;
        $actionID = \wyCupboard::$request->action;
        $info['logId'] = !empty($_REQUEST['logId']) ? $_REQUEST['logId'] : md5('APP' . microtime(true) . $controllerID . $actionID);
        //赋值logId，用来call sdk的时候传递
        \wyCupboardSdk::$loggerId = $info['logId'];
        $info['clientIP'] = app\helper\Net::getRemoteIp();
        $info['errorLog'] = "no error";
        $info['requestId'] = static::getRequestId();
        $info['openId'] = !empty($_COOKIE[COOKIE_OPENID_NAME]) ? $_COOKIE[COOKIE_OPENID_NAME] : '';
        $info[COOKIE_WX_OPEN_ID] = !empty($_COOKIE[COOKIE_WX_OPEN_ID]) ? $_COOKIE[COOKIE_WX_OPEN_ID] : '';
        $info[COOKIE_WX_UNION_ID]=!empty($_COOKIE[COOKIE_WX_UNION_ID]) ? $_COOKIE[COOKIE_WX_UNION_ID] : '';
        $info[COOKIE_MQQ_OPEN_ID] = !empty($_COOKIE[COOKIE_MQQ_OPEN_ID]) ? $_COOKIE[COOKIE_MQQ_OPEN_ID] : '';
        \wyCupboard::$logger = \Logger\Logger::getInstance();
        \wyCupboard::$logger->initLogWatch($info);
        $logPath = \wyCupboard::$config['logRootPath'] . date('Ymd') . "/" . $controllerID . "_" . $actionID . ".log";
        \wyCupboard::$logger->setLogPath($logPath);
    }

    //屏蔽错误
    protected static function screenError()
    {
        function wyShutdownFun()
        {
            $arrErr = error_get_last();
            if (!empty($arrErr)) {
                if ($arrErr['type'] != E_NOTICE && $arrErr['type'] != E_WARNING) {
                    ob_clean();
                    die(json_encode(['ret' => '01', 'sub' => '00001007', 'msg' => '服务器繁忙，请稍后尝试']));
                }
            }
        }

        register_shutdown_function("wyShutdownFun");
    }

    //初始化sdk
    protected static function initSdk()
    {
        if (isset(\wyCupboard::$config['sdkPath'])) {
            require(\wyCupboard::$config['sdkPath']);
            \wyCupboard::$sdk = \wyCupboardSdk::getInstance(\sdk::Instance());
        }
    }

    //初始化过滤器
    protected static function initFilter()
    {
        if (!empty(\wyCupboard::$config['filters'])) {
            foreach (\wyCupboard::$config['filters'] as $fliterName) {
                $className = CLASS_PREFIX . "\\filter\\" . $fliterName;
                $fliter = new $className;
                $fliter->init();
                $fliterRe = $fliter->filter();
                if ($fliterRe['ret'] != 0 || $fliterRe['sub'] != 0) {
                    \app\helper\OutPut::jsonOut($fliterRe);
                    die;
                }
            }
        }
    }

    //运行最终的类
    protected static function runClass($routeInfo)
    {
        $handler = $routeInfo[1];//handle
        $parameters = $routeInfo[2];//param
        list($class, $method) = explode('@', $handler);
        if (!class_exists($class)) {
            \app\helper\OutPut::jsonOut(['ret' => '01', 'sub' => '00001006', 'msg' => '方法不存在']);
        }
        $classObj = new $class();
        $parameters = array_values($parameters);
        //记录request参数
        $_REQUEST = array_merge($_REQUEST, ['request_uri' => $_SERVER['REQUEST_URI']]);
        $streamRequestData = file_get_contents('php://input');
        if ($streamRequestData) {
            $_REQUEST = array_merge($_REQUEST, ['inter_stream_data' => $streamRequestData]);
            $streamRequestData = null;
        }
        \wyCupboard::$logger->addNode('requestParams', $_REQUEST);
        //
        $nargs = sizeof($parameters);
        if ($nargs == 0) {
            $classObj->$method();
        } elseif ($nargs == 1) {
            $classObj->$method($parameters[0]);
        } elseif ($nargs == 2) {
            $classObj->$method($parameters[0], $parameters[1]);
        } elseif ($nargs == 3) {
            $classObj->$method($parameters[0], $parameters[1], $parameters[2]);
        } elseif ($nargs == 4) {
            $classObj->$method($parameters[0], $parameters[1], $parameters[2], $parameters[3]);
        } elseif ($nargs == 5) {
            $classObj->$method($parameters[0], $parameters[1], $parameters[2], $parameters[3], $parameters[4]);
        } else {
            call_user_func_array([$classObj, $method], array_values($parameters));
        }
    }

    //异常类的处理
    protected static function ExceptionHandler($e)
    {
        switch (APP_ENV) {
            case 'local':
            case 'dev':
            case 'pre':
                echo $e;
                break;
            case 'master':
                ob_clean();
                die(json_encode(['ret' => '01', 'sub' => '00001007', 'msg' => '方法不存在']));
                break;
        }
    }

    /**
     * 调用此方法可以直接用返回值，也可以调用完成之后用$_REQUEST
     *
     * @return string
     */
    public static function getRequestId()
    {
        $strRequestId = '';
        $arrHeaders = \app\helper\Utils::getAllHeaders();
        if (!empty($arrHeaders['X-REQUEST-ID'])) {
            $_REQUEST['X-Request-Id'] = $arrHeaders['X-REQUEST-ID'];
            $strRequestId = $arrHeaders['X-REQUEST-ID'];
        }

        return $strRequestId;
    }

    //初始化过滤器

    public static function run($routeInfo)
    {
        if (empty($routeInfo)) {
            \app\helper\OutPut::jsonOut(['ret' => '01', 'sub' => '00001003', 'msg' => '路由不匹配']);
        }
        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                \app\helper\OutPut::jsonOut(['ret' => '01', 'sub' => '00001004', 'msg' => '路由不匹配']);
                break;
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                \app\helper\OutPut::jsonOut(['ret' => '01', 'sub' => '00001005', 'msg' => '路由不匹配']);
                break;
            case FastRoute\Dispatcher::FOUND:
                try {
                    //发现路由后分割控制器/方法并调用
                    $uriInfo = explode('\\', $routeInfo[1]);
                    \wyCupboard::$request = new stdClass();
                    //获取参数
                    self::setRequestParams();
                    //定义controller,action并且加载配置
                    self::setConf($uriInfo);
                    //初始化logger
                    self::initLogger();
                    //错误屏蔽处理
                    self::screenError();
                    //初始化sdk
                    self::initSdk();
                    //过滤器
                    self::initFilter();
                    //最终执行controller类中的业务逻辑
                    self::runClass($routeInfo);
                } catch (\Exception $e) {
                    self::ExceptionHandler($e);
                }
        }
    }
}
