<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/8/2
 * Time: 15:52
 */

namespace app\services;

use app\base\BaseService;

class FavoriteService extends BaseService
{
    public function cinema($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['cinemaId'] = $arrParams['cinemaId'];
        $params['openId'] = $arrParams['openId'];
        $status = $arrParams['status'];
        if ($status == 0) {
            $params['action'] = 'favorite';
            $res = $this->sdk->call('favorite/favorite-cinema', $params);
            if ( !isset( $res['ret'] ) || ( $res['ret'] != 0 )) {
                $return = $this->getErrorOut(ERRORCODE_CINEMA_FAVORITE_FAILURE);
                $return['msg'] = !empty( $res['msg'] ) ? $res['msg'] : $return['msg'];
            }
        }
        elseif ($status == 1) {
            $params['action'] = 'un-favorite';
            $res = $this->sdk->call('favorite/favorite-cinema', $params);
            if ( !isset( $res['ret'] ) || ( $res['ret'] != 0 )) {
                $return = $this->getErrorOut(ERRORCODE_CINEMA_UN_FAVORITE_FAILURE);
                $return['msg'] = !empty( $res['msg'] ) ? $res['msg'] : $return['msg'];
            }
        }
        else {
            $return = $this->getErrorOut(ERRORCODE_ERROR_PARAM);
        }
        
        return $return;
    }
    
    /**
     * 获取收藏的影院
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function cinemaList($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $res = $this->sdk->call("favorite/get-favorite-cinema", $params);
        if ( !empty( $res ) && isset( $res['ret'] ) && ( $res['ret'] == 0 ) && !empty( $res['data'] )) {
            $return['data'] = $res['data'];
        }
        
        return $return;
    }
}