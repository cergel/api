<?php

namespace app\services;

use app\base\BaseService;

class SnackService extends BaseService
{
    /**
     * 获取小吃列表
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function getSnackList($arrParams = [])
    {
        $return = self::getStOut();
        if (!empty($arrParams['cinemaNo'])) {
            $return = $this->sdk->call('snack/get-snack-discount-list', $arrParams);
        }

        return $return;
    }

    public function getSnackListV2($arrInput = [], $rightArrInput = [])
    {
        $return = $this->sdk->call('snack/get-snack-discount-list-v2', $arrInput);
        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $rightArrInput['mpId'] = $arrInput['mpId'];
            $rightArrInput['cinemaId'] = $arrInput['cinemaNo'];
            $httpParams = [
                'arrData' => $rightArrInput,
                'sMethod' => 'POST',
            ];
            $gwlRet = $this->http(GEWARA_RIGHT_LIST, $httpParams);
            $return['data']['gewara_rights_list'] = $gwlRet['data'];
        }
        return $return;
    }

    /**
     * 小吃支付
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function snackPay($arrInput = [])
    {
        $return = ['ret' => '0', 'sub' => '0', 'msg' => 'success', 'data' => new \stdClass()];
        $response = $this->sdk->call('pay/snack', $arrInput);
        if (isset($response['ret']) && ($response['ret'] == 0)) {
            $return = $response;
        } else {
            $return['ret'] = $response['ret'];
            $return['sub'] = $response['sub'];
            $return['msg'] = $response['msg'];
        }

        return $return;
    }
}