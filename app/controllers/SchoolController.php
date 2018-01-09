<?php

namespace app\controllers;

use app\base\BaseController;

class SchoolController extends BaseController
{

    /**
     * 校园认证，仅手Q使用
     */
    public function oauth()
    {
        $return = self::getStOut();
        $params = [];
        $params['channelId'] = CHANNEL_ID;
        $strUserInfo = $this->getRequestParams('userinfo_tencent', '');
        if(empty($_REQUEST['userinfo_tencent'])){
            $this->jsonError(-1,'params error!');
        }
        $strUserInfo = str_replace(' ','+',$strUserInfo);
        $des3 = new \app\helper\Des3();
        $result = $des3->decrypt($strUserInfo);
        $arrResult = json_decode($result, 1);
        if(empty($arrResult)){
            //腾讯解密失败
            $this->jsonError(-1, 'decrypy error!');
        }
        $oauthStatus = $arrResult['is_auth'];
        $openid = $arrResult['openid'];
        if ($oauthStatus) {
            //此方法最后只需返回是否领取红包成功，只有商业化接口返回成功的时候 $bonusStatus=1,其他任何异常都返回0
            $bonusStatus = $this->service("Bonus")->getBonusStatus($openid);
        } else {
            $bonusStatus = 0;//如果没有认证 ，说明不能领红包
        }
        $return['data']=['authStatus' => $oauthStatus, 'bonusStatus' => $bonusStatus];
        $this->jsonOut($return);
    }
}