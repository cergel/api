<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/8/2
 * Time: 15:52
 */

namespace app\services;

use app\base\BaseService;

class SeatService extends BaseService
{
    private $refundTypeMsgMap = [
        '0' => '', //废弃
        '1' => '购票后15分钟至开场前2小时可退票', //可退票
        '2' => '请仔细核对购票信息，支付后不支持退票', //影院不可退
        '3' => '购票后15分钟至开场前2小时可退票', //开场前2小时
        '4' => '本月享有的1次退票资格已用完，不支持退票', //达到每月上线
        '5' => '该场次为万达特殊场次，不支持退票', //万达特定场次不可退
        '6' => '该场次离开场时间已不足2小时，不支持退票',
        '7' => '影厅特殊座位，不支持退票',
        '8' => '电影节影片不可退票',
    ];

    /**
     * 获取可售座位图
     * @param array $arrParams
     */
    public function getAvailableSeat($arrParams = [])
    {
        $return = self::getStOut();
        $res = $this->sdk->call('ticket/qry-available-seats', $arrParams);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        } else {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = $res['msg'];
        }
        return $return;
    }

    /**
     * 锁座
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function lockSeat($params = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (!empty($params['channelId']) && !empty($params['cinemaNo']) && !empty($params['schedulePricingId'])) {
            $res = $this->sdk->call('ticket/lock-seat', $params);
            //处理错误文案
            if ($res['ret'] != 0) {
                $msg = '锁座失败，请稍候再试';
                //1、处理书健文案之外的内容
                if (($res['ret'] == '-3')) {
                    //sub码以70003开头的，都是书健的错误文案
                    if (strpos($res['sub'], '70003') === false) {
                        $res['msg'] = $msg;
                    }
                } //2、处理接口响应失败的情况
                elseif ($res['ret'] != '-3') {
                    $res['msg'] = $msg;
                }
                $return['ret'] = $res['ret'];
                $return['sub'] = $res['sub'];
                $return['msg'] = $res['msg'];
            } else {
                //格式化退票信息
                if (isset($res['seatinfo']['refundType'])) {
                    $mapIndex = $res['seatinfo']['refundType'];
                    $res['seatinfo']['refundMsg'] = isset($this->refundTypeMsgMap[$mapIndex]) ? $this->refundTypeMsgMap[$mapIndex] : '';
                }
                //处理空数组转换为空对象
                $return['data'] = $res['seatinfo'];
            }
        }

        return $return;
    }

    /**
     * 锁座
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function lockSeatV2($params = [])
    {
        $return = $this->sdk->call('ticket/lock-seat-v1', $params);

        return $return;
    }
}