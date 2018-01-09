<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */
/**
 * APP模块类
 */

namespace app\services;

use app\base\BaseService;

class AppResourceService extends BaseService
{

    /**
     * 获取app模块开关
     * @param $channelId
     * @return mixed
     */
    public function getAppModuleSwitch()
    {
        return $this->model('AppResource')->getAppModuleSwitch();
    }

    /**
     * 获取热更新补丁
     * @param $channelId
     * @param $appver
     * @param $openId
     * @return mixed
     */
    public function getPatch($channelId, $appver, $openId)
    {
        return $this->model('AppResource')->getPatch($channelId, $appver, $openId);
    }

    /**
     * 返回版本列表
     * @param array $arrInput
     * @return array
     */
    public function getRelease($arrInput)
    {
        $return = $this->getStOut();
        $Obj = new \stdClass();
        //获取被动缓存
        $verData = $this->model('AppResource')->getVersion($arrInput['channelId']);
        if (!$verData) {
            //版本不存在
            $this->jsonError(-11251001);
        }

        if (empty($arrInput['versionCode'])) {
            $appver = $arrInput['appver'];
            if (empty($appver)) {
                $this->jsonError(ERRORCODE_APP_VERSION_NOT_EXIST_ERROR);
            }
        } else {
            $appver = $arrInput['versionCode'];
        }

        $ret = $this->_checkVersion($verData, $appver, $arrInput['appkey']);
        $return['data'] = $ret;
        return $return;
    }

    private function _checkVersion($verData, $versionStr, $channelId)
    {
        $updateTimestamp = 0;
        foreach ($verData as $key => $value) {
            $versionData = json_decode($value, 1);
            $ret = $this->_compare($versionData, $versionStr);
            if ($ret) {
                if ($versionData['updated'] > $updateTimestamp) {
                    $updateTimestamp = $versionData['updated'];
                    $path = '';
                    if ($channelId == 9) {
                        if (APP_ENV == 'pre') {
                            $path = \wyCupboard::$config['nfs_host'].'uploads/app_version/' . $versionData['path'];
                        } else {
                            $path = \Yii::$app->params['version']['path'] . $versionData['path'];
                        }
                    } else {
                        $path = $versionData['path'];
                    }
                    $verStr = '';
                    $verCode = '';
                    $retVersion = models\Version::getChannelVersion($channelId);
                    if (empty($retVersion)) {
                        $verStr = '0.0.0';
                        $verCode = 0;
                    } else {
                        $verStr = $retVersion;
                        $verCode = $retVersion;
                    }
                    $compairData = [
                        'id' => $versionData['id'],
                        'title' => $versionData['title'],
                        'img' => $versionData['img'],
                        'version' => $verStr,
                        'versionCode' => $verCode,
                        'path' => $path,
                        'forceUpdate' => $versionData['forceUpdate'],
                        'md5' => empty($versionData['md5']) ? 0 : $versionData['md5'],
                        'description' => $versionData['description'],
                    ];
                }
            }
        }
        if (empty($compairData)) {
            $compairData = new \stdClass();
        } else {
            if (($channelId == 9) && $this->_checkAndroidVer($versionStr)) {
                $compairData['version'] = KEY_APP_VERSION_ANDROID_OLD_VERSION_UPDATE_VERSION;
                $compairData['versionCode'] = KEY_APP_VERSION_ANDROID_OLD_VERSION_UPDATE_VERSION;
            }
        }
        return $compairData;
    }


}