<?php
/**
 * 有偿退改签
 */
namespace app\services;

use app\base\BaseService;

class ChangesFeeService extends BaseService
{
    /**
     * 退改签费用规则
     */
    public function getFeeInfo($params)
    {
        $return = $this->sdk->call('changes-fee/get-changes-fee-by-cinema-no', $params);
        return $return;
    }

    /**
     * 退改签费用规则
     */
    public function getCurrentFee($params)
    {
        $return = $this->sdk->call('changes-fee/get-current-changes-fee', $params);
        return $return;
    }
}