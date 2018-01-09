<?php

namespace app\controllers;

use app\base\BaseController;

class ProxyController extends BaseController
{
    /**
     * 代理
     */
    public function bonusProxy()
    {
        if (empty($_REQUEST['url'])) {
            exit;
        }
        
        $url = $_REQUEST['url'];
        $arrParse = parse_url($url);
        if (strpos($arrParse['host'], ".wepiao.com") === false) {
            exit;
        }
        
        $arrParam = [];
        foreach ($_REQUEST as $key => $value) {
            if ($key != 'url') {
                $purl = $key . '=' . $value;
                $arrParam[] = $purl;
            }
        }
        //只有$arrParam不为空 说明微信带回来code，其他情况直接跳转回原url
        if ( !empty($arrParam)) {
            $paramUrl = implode('&', $arrParam);
            
            if (strpos($url, '?') === false) {
                $url .= '?' . $paramUrl;
            } else {
                $url .= '&' . $paramUrl;
            }
        }
        
        header('Location:' . $url);
    }
    
    
}