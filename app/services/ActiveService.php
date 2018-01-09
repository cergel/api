<?php
/**
 * 活动类接口
 */
namespace app\services;

use app\base\BaseService;

class ActiveService extends BaseService
{
    /**
     * 银行卡优惠
     */
    public function BankPrivilege($params)
    {
        $return = $this->sdk->call('bank-privilege/qryBankPrivilegeV2', $params);
        return $return;
    }

    /**
     * 首页拉新促销弹框活动
     */
    public function NewcomerBonus($params)
    {
        $return = $this->sdk->call('bonus/newcomer-bonus', $params);
        return $return;
    }
}