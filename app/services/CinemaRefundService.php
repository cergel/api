<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 2017/5/9
 * Time: 下午3:19
 */

namespace app\services;

use app\base\BaseService;


class CinemaRefundService extends BaseService
{
    private $refundCinemas = [];
    private $RefundCinemasType = [1, 4, 14, 15];

    /**
     * 获取要过滤的退票状态
     */
    public function getRefundCinemasType()
    {
        return $this->RefundCinemasType;
    }

    private function getMsg()
    {
        return in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids']) ? '格瓦拉' : '娱票儿';
    }

    private function getRefundCinemas()
    {
        if (empty($this->refundCinemas)) {
            $params['channelId'] = CHANNEL_ID;
            $this->refundCinemas = $this->sdk->call("cinema/get-app-refund-cinema", $params);
        }
        return $this->refundCinemas;
    }

    //格式化ES版本影院搜索
    public function refundCinemaEs(&$response)
    {
        if ($response['ret'] == 0 && $response['sub'] == 0 && !empty($response['data']['list'])) {
            foreach ($response['data']['list'] as &$cinemaItem) {
                if (in_array($cinemaItem['refund'], $this->getRefundCinemasType())) {
                    if (!in_array($cinemaItem['CinemaNo'], $this->getRefundCinemas())) {
                        $cinemaItem['refund'] = 0;
                    }
                }
            }
        }
    }

    //格式化ES版本的影院列表的退款标识
    public function refundCinemaListEs(&$response)
    {
        if ($response['ret'] == 0 && $response['sub'] == 0 && !empty($response['data']['list'])) {
            foreach ($response['data']['list'] as &$cinemaItem) {
                if (in_array($cinemaItem['refund'], $this->getRefundCinemasType())) {
                    if (!in_array($cinemaItem['cinema_no'], $this->getRefundCinemas())) {
                        $cinemaItem['refund'] = 0;
                    }
                }
            }
        }
    }


    //格式化锁坐文案
    public function refundLockSeat($cinemaId, &$response)
    {
        if ($response['ret'] == 0 && $response['sub'] == 0 && !empty($response['seatinfo'])) {
            if (in_array($response['seatinfo']['refundType'], $this->getRefundCinemasType())) {
                if (!in_array($cinemaId, $this->getRefundCinemas())) {
                    $response['seatinfo']['refundType'] = 2;
                    $response['seatinfo']['refundMsg'] = "{$this->getMsg()}客户端暂不支持本影院退票";
                }
            } else {
                //app请求没有请求订单详情所以请求详情并获取可退款信息 @author CHAIYUE
                $params = [
                    'channelId' => CHANNEL_ID,
                    'openId' => $this->service('Login')->getOpenIdFromCookie(),
                    'orderId' => $response['seatinfo']['sTempOrderID'],
                    'payStatus' => 0,
                    'yupiaoRefundShow' => 'true'
                ];
                $orderDetail = $this->sdk->call('order/query-order-info-new', $params);
                if ($orderDetail['ret'] == 0 && $orderDetail['sub'] == 0) {
                    $response['seatinfo']['refundMsg'] = isset($orderDetail['data']['refundMsg']) ? $orderDetail['data']['refundMsg'] : $response['seatinfo']['refundMsg'];
                }
            }
        }
    }

    //格式化ES版本的影院列表的退款标识
    public function refundCinemaSearch(&$response)
    {
        if ($response['ret'] == 0 && $response['sub'] == 0 && !empty($response['data']['list'])) {
            foreach ($response['data']['list'] as &$cinemaItem) {
                if (in_array($cinemaItem['refund'], $this->getRefundCinemasType())) {
                    if (!in_array($cinemaItem['CinemaNo'], $this->getRefundCinemas())) {
                        $cinemaItem['refund'] = 0;
                    }
                }
            }
        }
    }
}