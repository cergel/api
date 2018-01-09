<?php

namespace app\services;


use app\base\BaseService;

class ScheService extends BaseService
{

    /**
     * 获取排期信息
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getScheInfo($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['cinemaId'] = !empty($arrParams['cinemaId']) ? $arrParams['cinemaId'] : '';
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        $params['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        if (!empty($params['channelId']) && !empty($params['cinemaId'])) {
            $res = $this->sdk->call('sche/read-cinema-sche-and-format', $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                if (in_array($params['channelId'], array(63, 66, 67, 68))) //小程序排期独有 单独放在小程序项目里 暂不放入service项目
                {
                    $return['data'] = $this->_wxAppFormat($res['data'], $params['movieId'], $arrParams['needMore'],
                        $arrParams['all']);
                } else {
                    $return['data'] = $res['data'];
                }
            }
        }

        return $return;
    }

    private function _wxAppFormat($arrData, $movieId, $wxAppNeedMore, $wxAppAll)
    {
        $return = [];
        if ($wxAppAll == 2) {
            //如果影片ID为null,取列表中的第一个影片
            if (empty($movieId)) {
                foreach ($arrData as $k => $v) {
                    if ($k != 0) {
                        $v['sche'] = new \stdClass();
                    }
                    $return[] = $v;
                    //如果不需要其他影片信息，直接就退出了
                    if ($wxAppNeedMore != 1) {
                        break;
                    }
                }
            } else {//如果影片id不为空，取影片排期中和传入影片id相同的项
                foreach ($arrData as $k => $v) {
                    //如果不需要其他影片信息,继续遍历到需要的影片id
                    if ($wxAppNeedMore != 1) {
                        if ($v['id'] != $movieId) {
                            continue;
                        } else {
                            $return[] = $v;
                            break;
                        }
                    } else {
                        if ($v['id'] != $movieId) {
                            $v['sche'] = new \stdClass();
                        }
                        $return[] = $v;
                    }
                }
            }
        } else {
            $return = $arrData;
        }
        return $return;
    }

    /**
     * 某个影院所有电影排期V2接口
     */
    public function getScheInfoV2($arrParams = [])
    {
        return $this->sdk->call('sche/qryScheV2', $arrParams);
    }

    //app格式化影院排期
    public function _formatAppScheV2(&$arrData)
    {
        foreach ($arrData as &$arrValue) {
            $i = 0;
            $j = 0;
            $sche = [];
            $discount_label = [];
            ksort($arrValue['sche']);
            foreach ($arrValue['sche'] as $date => $item) {
                $sche[$i]['date'] = $date;
                $sche[$i]['info'] = $item;
                $i++;
            }
            foreach ($arrValue['disLabel'] as $date => $item) {
                //过滤之前的排期只保留今天之后的排期

                $discount_label[$j]['date'] = $date;
                $discount_label[$j]['info'] = $item;
                $j++;
            }
            $arrValue['disLabel'] = $discount_label;
            $arrValue['sche'] = $sche;
        }
    }
    
    /**
     * 格式化排期中的各种label
     *
     * @param $arrData
     */
    public function _formatScheLabel(&$arrData)
    {
        foreach ($arrData as &$arrValue) {
            //空数组转空对象
            if (isset($arrValue['pointMappingLabel']) && empty($arrValue['pointMappingLabel']) && is_array($arrValue['pointMappingLabel'])) {
                $arrValue['pointMappingLabel'] = new \stdClass();
            }
        }
    }

    /**
     * 获取某排期扩展属性（是否配有3D 是否需要自带3D眼镜）
     */
    public function getScheduleExt($arrParams = [])
    {
        return $this->sdk->call('sche/get-schedule-ext', $arrParams);
    }
}