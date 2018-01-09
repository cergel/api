<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
/**
 * app 版本
 */

namespace app\controllers;

use app\base\BaseController;

class AppVersionController extends BaseController
{
    /**
     * 取得Android最新版本
     */
    public function getVersionRelease()
    {
        $arrInput = $this->input;
        if ($arrInput === false) {
            Yii::warning('params error');
            $this->jsonError($this->getDataValidatedErrors());
        }
        $data = $this->serviceVersion->getRelease($arrInput);
        $this->jsonOut($data);
    }
}