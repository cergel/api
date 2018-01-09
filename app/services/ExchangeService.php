<?php

namespace app\services;

use app\base\BaseService;

class ExchangeService extends BaseService
{
    
    /**
     * 获取兑换券详情
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function info($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if ( !empty( $arrParams['grouponId'] )) {
            $return = $this->sdk->call('exchange/query', $arrParams);
        }
        
        return $return;
    }
    
    /**
     * 兑换券支付
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function exchangePay($arrInput = [])
    {
        $return = ['ret' => '0', 'sub' => '0', 'msg' => 'success', 'data' => new \stdClass()];
        //获取支付串
        $response = $this->sdk->call('pay/pay-exchange-weixin', $arrInput);
        if (isset( $response['ret'] ) && ( $response['ret'] == 0 )) {
            $return['data'] = $response['url'];
        }
        else {
            $return['ret'] = $response['ret'];
            $return['sub'] = $response['sub'];
            $return['msg'] = $response['msg'];
        }
        
        return $return;
    }
    
    /**
     * 获取兑换券订单列表
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function orderList($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if ( !empty( $arrParams['openId'] )) {
            $return = $this->sdk->call('exchange/order-list', $arrParams);
        }
        
        return $return;
    }
    
    /**
     * 获取兑换券订单详情
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function orderInfo($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if ( !empty( $arrParams['openId'] ) && !empty( $arrParams['orderId'] )) {
            $return = $this->sdk->call('exchange/order-info', $arrParams);
        }
        
        return $return;
    }
    
}