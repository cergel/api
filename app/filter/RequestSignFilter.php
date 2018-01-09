<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/12
 * Time: 下午3:05
 */

namespace app\filter;

use app\base\BaseFilter;

class RequestSignFilter extends BaseFilter
{
    public function init()
    {
    }

    public function filter()
    {
        $controller = \wyCupboard::$request->controller;
        $action = \wyCupboard::$request->action;
        $paramsConfig = \wyCupboard::$config['requestSignConfig'];
        if ($paramsConfig['switch'] === "off") {
            return $this->getReturn();
        }
        if (isset(\wyCupboard::$request->params['no_sig_check']) && (\wyCupboard::$request->params['no_sig_check'] == '7a9015e9521a3b415d99c351607573cc')) {
            return $this->getReturn();
        }

        if (!(isset($paramsConfig['except'][$controller]) && in_array($action, $paramsConfig['except'][$controller]))) {
            //获取客户端参数如果需要验证签名则参数中必须包含channelId、t、sign、appver
            $params = \wyCupboard::$request->params;
            if (empty($params['channelId']) || empty($params['t']) || empty($params['appver']) || empty($params['sign'])) {
                return ['ret' => -1, 'sub' => -1, 'msg' => '本接口需要签名验证请检查是否缺少相关字段channelId,t,sign,appver'];
            }

            //获取当前渠道当前客户端版本的密钥，如果未设置对应渠道则用默认的密钥
            if (isset($paramsConfig['secret'][$params['channelId']])) {
                $app_vers = array_keys($paramsConfig['secret'][$params['channelId']]);
                $max_ver = max($app_vers);
                //一般情况下传入的版本号应取配置中最大版本号的值
                if ($params['appver'] >= $max_ver) {
                    $secret = $paramsConfig['secret'][$params['channelId']][$max_ver];
                    $secretId = "{$params['channelId']}_{$params['appver']}";
                } else {
                    //中间版本情况时排序后取匹配上的第一个。都取不到取最小的
                    rsort($app_vers);
                    foreach ($app_vers as $k) {
                        if ($k <= $params['appver']) {
                            $secret = $paramsConfig['secret'][$params['channelId']][$k];
                            $secretId = "{$params['channelId']}_{$params['appver']}";
                            break;
                        }
                    }
                    if ($params['appver'] <= end($app_vers)) {
                        $secret = $paramsConfig['secret'][$params['channelId']][end($app_vers)];
                        $secretId = "{$params['channelId']}_{$params['appver']}";
                    }
                }
            } else {
                $secret = $paramsConfig['secret']['default'];
                $secretId = "default";
            }


            //获取所有的参数排除字段准备签名对比
            $client_sign = strtoupper($params['sign']);
            unset($params['sign']);
            ksort($params);
            $strKey = urldecode(http_build_query($params));
            $strMd5 = strtoupper(MD5($secret . $strKey));
            if ($client_sign !== $strMd5) {
                header("X-Signature :" . $secretId);
                header("Signature :" . $strMd5);
                return ['ret' => -1, 'sub' => -1, 'msg' => '签名认证失败'];
            }
            return $this->getReturn();
        }

    }
}