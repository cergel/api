<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/8/3
 * Time: 13:53
 */

namespace app\services;

use app\base\BaseService;
use PKPass\PKPass;

class OrderService extends BaseService
{

    /**
     * 全支付接口
     * @param array $arrSendParams
     * @return mixed
     */
    public function pay($arrParams = [])
    {
        $return = self::getStOut();
        $params = [];
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['orderId'] = !empty($arrParams['orderId']) ? $arrParams['orderId'] : '';
        $params['phone'] = !empty($arrParams['phone']) ? $arrParams['phone'] : '';
        $params['channelId'] = $arrParams['channelId'];
        $params['tradeType'] = $arrParams['tradeType'];
        $params['payType'] = $arrParams['payType'];
        //卖品
        $params['snackId'] = !empty($arrParams['snackId']) ? $arrParams['snackId'] : '';
        $params['snackNum'] = !empty($arrParams['snackNum']) ? $arrParams['snackNum'] : '';
        if (empty($params['snackNum'])) {
            $params['snackId'] = '';
        }
        $params['smsToken'] = !empty($arrParams['smsToken']) ? $arrParams['smsToken'] : '';
        $params['smsCode'] = !empty($arrParams['smsCode']) ? $arrParams['smsCode'] : '';
        $params['disInfo'] = !empty($arrParams['disInfo']) ? $arrParams['disInfo'] : '';
        $params['goodsInfoList'] = !empty($arrParams['goodsInfoList']) ? $arrParams['goodsInfoList'] : '';
        if (isset($arrParams['returnUrl'])) {
            $params['returnUrl'] = $arrParams['returnUrl'];
        }
        $res = $this->sdk->call('pay/pay', $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        } else {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = $res['msg'];
            $return['data'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        }

        return $return;
    }

    /**
     * 全支付接口V2版本,支持改签。后续所有支付,都必须迁移到此接口上来
     *
     * @param array $arrSendParams
     *
     * @return mixed
     */
    public function payV2($arrParams = [])
    {
        $return = self::getStOut();
        $params = [];
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['orderId'] = !empty($arrParams['orderId']) ? $arrParams['orderId'] : '';
        $params['phone'] = !empty($arrParams['phone']) ? $arrParams['phone'] : '';
        $params['channelId'] = $arrParams['channelId'];
        $params['tradeType'] = $arrParams['tradeType'];
        $params['payType'] = $arrParams['payType'];
        if (isset($arrParams['returnUrl'])) {
            $params['returnUrl'] = $arrParams['returnUrl'];
        }
        //卖品
        $params['snackId'] = !empty($arrParams['snackId']) ? $arrParams['snackId'] : '';
        $params['snackNum'] = !empty($arrParams['snackNum']) ? $arrParams['snackNum'] : '';
        if (empty($params['snackNum'])) {
            $params['snackId'] = '';
        }
        $params['smsToken'] = !empty($arrParams['smsToken']) ? $arrParams['smsToken'] : '';
        $params['smsCode'] = !empty($arrParams['smsCode']) ? $arrParams['smsCode'] : '';
        $params['disInfo'] = !empty($arrParams['disInfo']) ? $arrParams['disInfo'] : '';
        $params['goodsInfoList'] = !empty($arrParams['goodsInfoList']) ? $arrParams['goodsInfoList'] : '';
        $res = $this->sdk->call('pay/pay-v2', $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        } else {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = $res['msg'];
            $return['data'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        }

        return $return;
    }

    public function payV3($arrParams = [])
    {
        $return = self::getStOut();
        $params = [];
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['orderId'] = !empty($arrParams['orderId']) ? $arrParams['orderId'] : '';
        $params['phone'] = !empty($arrParams['phone']) ? $arrParams['phone'] : '';
        $params['channelId'] = $arrParams['channelId'];
        $params['tradeType'] = $arrParams['tradeType'];
        $params['payType'] = $arrParams['payType'];
        $params['wxOpenId'] = (isset($arrParams['wxOpenId']) && !empty($arrParams['wxOpenId'])) ? $arrParams['wxOpenId'] : '';
        if (isset($arrParams['returnUrl'])) {
            $params['returnUrl'] = $arrParams['returnUrl'];
        }
        if (isset($arrParams['interfaceVersion'])) {
            $params['interfaceVersion'] = $arrParams['interfaceVersion'];
        }
        //卖品
        $params['snackId'] = !empty($arrParams['snackId']) ? $arrParams['snackId'] : '';
        $params['snackNum'] = !empty($arrParams['snackNum']) ? $arrParams['snackNum'] : '';
        if (empty($params['snackNum'])) {
            $params['snackId'] = '';
        }
        $params['smsToken'] = !empty($arrParams['smsToken']) ? $arrParams['smsToken'] : '';
        $params['smsCode'] = !empty($arrParams['smsCode']) ? $arrParams['smsCode'] : '';
        if (isset($arrParams['disInfo'])) {
            $params['disInfo'] = $arrParams['disInfo'];
        }
        $params['goodsInfoList'] = !empty($arrParams['goodsInfoList']) ? $arrParams['goodsInfoList'] : '';
        //格瓦拉
        $params['reqSource'] = !empty($arrParams['reqSource']) ? $arrParams['reqSource'] : '';
        $params['merchantCode'] = !empty($arrParams['merchantCode']) ? $arrParams['merchantCode'] : '';
        $params['bankCode'] = !empty($arrParams['bankCode']) ? $arrParams['bankCode'] : '';
        $params['gatewayCode'] = !empty($arrParams['gatewayCode']) ? $arrParams['gatewayCode'] : '';
        //权益相关
        $params['rightsId'] = !empty($arrParams['rightsId']) ? $arrParams['rightsId'] : '';;
        $params['goodsId'] = !empty($arrParams['goodsId']) ? $arrParams['goodsId'] : '';;
        $params['goodsNum'] = !empty($arrParams['goodsNum']) ? $arrParams['goodsNum'] : '';;
        $res = $this->sdk->call('pay/pay-v3', $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        } else {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = $res['msg'];
            $return['data'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        }

        return $return;
    }

    /**
     * 根据订单号查询订单新版
     * @param array $arrInput
     * @return mixed
     */
    public function queryOrderInfo($arrParams = [])
    {
        $return = self::getStOut();
        //拼装通用参数
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $params['salePlatformType'] = $arrParams['salePlatformType'];
        $params['appId'] = !empty($arrParams['appId']) ? $arrParams['appId'] : WX_MOVIE_APP_ID;
        $params['userId'] = !empty($arrParams['userId']) ? $arrParams['userId'] : '';
        $params['orderId'] = !empty($arrParams['orderId']) ? $arrParams['orderId'] : '';
        $params['yupiaoRefundShow'] = !empty($arrParams['yupiaoRefundShow']) ? $arrParams['yupiaoRefundShow'] : 'true';
        //只在支付成功页会传入
        if (isset($arrParams['payStatus']) && $arrParams['payStatus'] == '0') {
            $params['payStatus'] = $arrParams['payStatus'];
            //阵营购买计数，例如《魔兽》联盟部落
            $campData['movieId'] = !empty($arrParams['movieId']) ? $arrParams['movieId'] : '';
            $campData['campName'] = !empty($arrParams['campName']) ? $arrParams['campName'] : '';
            $campData['seatNum'] = !empty($arrParams['seatNum']) ? $arrParams['seatNum'] : '';
            $this->service("Movie")->incrMovieCamp($campData);
        }
        $res = $this->sdk->call('order/query-order-info-new', $params);
        $ptInfo = $this->model('Spellgroup')->queryOrderPintuan($params['orderId']);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
            if (!empty($ptInfo)) {
                $return['data']['pintuanInfo'] = json_decode($ptInfo, true);
            }
        }

        return $return;
    }

    /**
     * 根据订单号查询订单新版(支持改签)
     *
     * @param array $arrInput
     *
     * @return mixed
     */
    public function queryOrderInfoV2($arrParams = [])
    {
        $return = self::getStOut();
        //拼装通用参数
        $params['channelId'] = $arrParams['channelId'];
        $params['openId'] = $arrParams['openId'];
        $params['orderId'] = !empty($arrParams['orderId']) ? $arrParams['orderId'] : '';
        $params['payStatus'] = !empty($arrParams['payStatus']) ? $arrParams['payStatus'] : 0;
        $params['yupiaoRefundShow'] = !empty($arrParams['yupiaoRefundShow']) ? $arrParams['yupiaoRefundShow'] : 'true';
        $params['realOpenId'] = !empty($arrParams['realOpenId']) ? $arrParams['realOpenId'] : '';
        $params['realChannelId'] = !empty($arrParams['realChannelId']) ? $arrParams['realChannelId'] : '';
        $return = $this->sdk->call('order/query-orderinfo-v1', $params);
        if ($params['channelId'] == '28') {
            $ptInfo = $this->model('Spellgroup')->queryOrderPintuan($params['orderId']);
            if (!empty($return) && isset($return['ret']) && ($return['ret'] == 0) && !empty($return['data'])) {
                if (!empty($ptInfo)) {
                    $return['data']['pintuanInfo'] = json_decode($ptInfo, true);
                }
            }
        }

        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            //添加影片详情
            $movieParams['channelId'] = CHANNEL_ID;
            $movieParams['movieId'] = $return['data']['movieInfo']['id'];
            $movieParams['cityId'] = 10;
            $res = $this->sdk->call('movie/read-movie-info', $movieParams);
            $return['data']['movieInfo']['logo'] = !empty($res['data']['poster_url']) ? $res['data']['poster_url'] : '';
            //添加自定义票纸
            $cinemaId = $return['data']['cinemaId'];
            $channelId = CHANNEL_ID;
            $orderId = $params['orderId'];
            $return['data']['ticketPaper'] = $this->sdk->call("cinema/is-gewala-paper",
                compact('cinemaId', 'channelId'));
            $return['data']['ticketPaperMsg'] = '可定制票纸';
            $return['data']['ticketPaperContent'] = '';
            if ($return['data']['ticketPaper']) {
                $return['data']['ticketPaperContent'] = $this->sdk->call("order/paper-query-order-print-msg",
                    compact('orderId', 'channelId'));
            }
        }
        return $return;
    }

    /**
     * 获取订单列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getOrderList($arrParams = [])
    {
        $return = self::getStOut();
        //拼装通用参数
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : '';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['page'] = !empty($arrParams['page']) ? $arrParams['page'] : 1;
        $params['num'] = !empty($arrParams['num']) ? $arrParams['num'] : 10;
        $params['salePlatformType'] = !empty($arrParams['salePlatformType']) ? $arrParams['salePlatformType'] : SALE_PLATFORM_TYPE;
        $params['appId'] = !empty($arrParams['appId']) ? $arrParams['appId'] : WX_MOVIE_APP_ID;
        $params['userId'] = !empty($arrParams['userId']) ? $arrParams['userId'] : '';
        $params['types'] = !empty($arrParams['types']) ? $arrParams['types'] : '';
        $res = $this->sdk->call('order/query-paid-order-new', $params);
        $Paid = [
            'orderList' => [],
            'total_row' => 0,
            'page' => 1,
            'num' => 0,
        ];
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['orderList'])) {
            $Paid['orderList'] = $res['orderList'] ? $res['orderList'] : [];
            $Paid['total_row'] = $res['total_row'];
            $Paid['page'] = $res['page'];
            $Paid['num'] = $res['num'];
        }
        $return['data'] = $Paid;

        return $return;
    }

    /**
     * 获取订单列表(支持改签)
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getOrderListV2($arrParams = [])
    {
        $return = self::getStOut();
        //拼装通用参数
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : '3';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['page'] = !empty($arrParams['page']) ? $arrParams['page'] : 1;
        $params['num'] = !empty($arrParams['num']) ? $arrParams['num'] : 10;
        $params['expireFlag'] = !empty($arrParams['expireFlag']) ? $arrParams['expireFlag'] : '';
        $params['types'] = !empty($arrParams['types']) ? $arrParams['types'] : '';
        if (!empty($params['openId'])) {
            $return = $this->sdk->call('order/query-order-list-v1', $params);
        }
        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $cinemaIdTicketPaper = [];
            if (is_array($return['data']) && !empty($return['data'])) {
                foreach ($return['data'] as &$value) {
                    if (!empty($value['movieInfo'])) {
                        $movieParams['channelId'] = CHANNEL_ID;
                        $movieParams['movieId'] = $value['movieInfo']['id'];
                        $movieParams['cityId'] = 10;
                        $res = $this->sdk->call('movie/read-movie-info', $movieParams);
                        $value['movieInfo']['logo'] = !empty($res['data']['poster_url']) ? $res['data']['poster_url'] : '';
                        $value['movieInfo']['gewara_id'] = !empty($res['data']['poster_url']) ? $res['data']['gewara_id'] : '';
                    }
                    //添加自定义票纸
                    $msg = '可定制票纸';
                    $cinemaId = $value['cinemaId'];
                    $channelId = CHANNEL_ID;
                    if (!isset($cinemaIdTicketPaper[$cinemaId])) {
                        $ticketPaper = $this->sdk->call("cinema/is-gewala-paper", compact('cinemaId', 'channelId'));
                        $cinemaIdTicketPaper[$cinemaId] = $ticketPaper;
                    }
                    $value['ticketPaper'] = $cinemaIdTicketPaper[$cinemaId];
                    $value['ticketPaperMsg'] = $msg;
                }
            }
        }

        return $return;
    }

    /**
     * 获取未支付订单(支持改签)
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function queryUnpaidOrderV2($arrParams = [])
    {
        $return = self::getStOut();
        //拼装通用参数
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : '3';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['forceGet'] = 1;
        if (!empty($params['openId'])) {
            $res = $this->sdk->call('order/query-un-pay-order-multi-v1', $params);
            //只取一条订单
            if (!empty($res['lockinfo'][0])) {
                $return['data'] = $res['lockinfo'][0];
            }
        }
        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $params['cityId'] = $arrParams['cityId'];
            $params['cinemaId'] = isset($return['data']['iCinemaID']) ? $return['data']['iCinemaID'] : 0;
            $cardInfo = $this->service("Cinema")->CinemaVipCard($params);
            //未支付订单接口返回的carNo不为空 且cardInfo不为空，且影院会员卡接口cardInfo不为空，且status状态为4
            if (is_array($cardInfo['data']) && !empty($return['data']['cardNo']) && $cardInfo['data']['status'] == "4") {
                $cardInfo['data']['cardInfo']['useCard'] = 1;
            }
            $return['data']['card'] = isset($cardInfo['data']) ? $cardInfo['data'] : new \stdClass();
            if(isset($return['data']['iUnitPrice'])){
                $return['data']['discountMessage'] = $this->service("Resource")->alertDiscountMessage($return['data']['iUnitPrice']);
            }
        }

        return $return;
    }

    /**
     * 获取订单列表(支持改签)
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getOrderMobile($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['mobileNo'] = '';
        //拼装通用参数
        $params = [];
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : '3';
        $params['openId'] = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
        $params['userId'] = !empty($arrParams['userId']) ? $arrParams['userId'] : '';
        $params['salePlatformType'] = !empty($arrParams['salePlatformType']) ? $arrParams['salePlatformType'] : SALE_PLATFORM_TYPE;
        $params['appId'] = !empty($arrParams['appId']) ? $arrParams['appId'] : WX_MOVIE_APP_ID;
        if (!empty($params['openId'])) {
            $return = $this->sdk->call('order/query-order-mobile', $params);
        }

        return $return;
    }


    //APP格式化已支付订单的列表
    public function _formatOrderList($channelId, &$orderList)
    {

        $convert = [
        ];
        $unset = [
            'city_id',
            'city_name',
            'hall_id',
            'hall_name',
            'language',
            'show_type',
            'totalPrice',
            'hotline_tele',
            'expired_time',
            'time',
            'exchange_addr',
            'exchange_method',
            'cinemaAddr',
            'cinema_telephone',
            'cinema_address',
            'show_date_has_weekday',
            'total_fee',
        ];

        //获取评论状态
        $arrCommentedMovieId = $this->service("Movie")->getUserCommentList($channelId);
        foreach ($orderList as &$value) {
            //只有卖品+电影，电影的才转
            if (in_array($value['order_type'], [ORDER_TYPE_TICKET, ORDER_TYPE_TICKET_SNACK])) {
                //获取影片海报
                $movieInfo = $this->sdk->call("movie/read-movie-info",
                    ['channelId' => $channelId, 'movieId' => $value['movie_id'], 'cityId' => 10]);
                if ($movieInfo['ret'] == 0 && $movieInfo['sub'] == 0) {
                    $value['poster_url'] = $movieInfo['data']['poster_url'];
                    $arrLong = explode("分", $movieInfo['data']['longs']);
                    $value['longs'] = $arrLong[0];
                } else {
                    $value['poster_url'] = "";
                    $value['longs'] = "0";
                }
                //查看用户所购买的场次是否已完成（也就是是否过了放映时间）
                $scheState = 1; //1表示已完成，0表示未完成
                if (isset($value['expired_time']) || !empty($value['expired_time'])) {
                    $intExpiredTime = strtotime($value['expired_time']);
                    //如果当前时间小于过期时间，则表示已过期
                    if (time() < $intExpiredTime) {
                        $scheState = 0;
                    }
                }
                $value['sche_state'] = $scheState;
                //判断用户是否评论过订单中的这部影片，0表示未评论，1表示评论过
                $intCommentState = 0;
                if (in_array($value['movie_id'], $arrCommentedMovieId)) {
                    $intCommentState = 1;
                }
                $value['comment_state'] = $intCommentState;
                $value['show_date'] = strtotime($value['show_date']);

                //格式化取票码判断是否为双码并把标签和文字分离
                foreach ($value['seatInfo'] as &$item) {
                    if (empty($item['cdKey'])) {
                        $item['cdKey'] = [];
                        continue;
                    }
                    $strArr = explode("，", $item['cdKey']);
                    $item['cdKey'] = [];
                    //拆分标签和文本
                    $i = 0;
                    foreach ($strArr as $key => $text) {
                        $arrcode = explode(":", $text);
                        if ($i) {
                            $item['secondLabel'] = $arrcode[0];
                            $item['secondLabelText'] = $arrcode[1];
                        } else {
                            $item['firstLabel'] = $arrcode[0];
                            $item['firstLabelText'] = $arrcode[1];
                        }
                        $i++;
                    }
                    unset($item['cdKey']);
                }
            }
            //转换字段
            $this->convertInputAndUnset($value, $convert, $unset);
        }
    }

    public function appleWallet($Order, $env = "product")
    {
        $path = __DIR__ . "/../../vendor/pkpass";
        //新建PASSPORT对象
        $pass = new PKPass();
        //判断环境测试环境用微影的证书否则用腾讯的证书
        if ($env == "test") {
            $pass->setCertificate($path . '/Certificates_weiying.p12');
            $pass->setCertificatePassword('');
            $pass->setWWDRcertPath($path . '/WWDR.pem');
            //基本信息
            $standardKeys = array(
                'description' => '格瓦拉生活',
                'formatVersion' => 1,
                'organizationName' => '格瓦拉',
                'passTypeIdentifier' => 'pass.gewara.testpassbook',
                'serialNumber' => $Order['sOrderId'],
                'teamIdentifier' => 'UWJVY6ERGE'
            );
        } else {
            if (in_array(CHANNEL_ID, array(80, 84))) {
                $pass->setCertificate($path . '/GewaraMoviePass.p12');
                $pass->setCertificatePassword('gewara');
            } else {
                $pass->setCertificate($path . '/Certificates.p12');
                $pass->setCertificatePassword('');
            }

            $pass->setWWDRcertPath($path . '/WWDR.pem');
            //基本信息
            $standardKeys = array(
                'description' => '格瓦拉生活',
                'formatVersion' => 1,
                'organizationName' => '格瓦拉',
                'passTypeIdentifier' => 'pass.gewara.testpassbook',
                'serialNumber' => $Order['sOrderId'],
                'teamIdentifier' => 'UWJVY6ERGE'
            );
        }

        $associatedAppKeys = [
            'associatedStoreIdentifiers' => [388083979]
        ];

        //到影院前提前半小时提示
        $TimeStamp = strtotime($Order['sShowDate']) - 1800;
        $DateTime = date('Y-m-d\TH:i:sP', $TimeStamp);
        $relevanceKeys = [
            'relevantDate' => $DateTime,
            // 'locations' => [
            //     [
            //         'latitude' => $Order['location']['latitude'],
            //        "longitude" => $Order['location']['longitude'],
            //         "relevantText" => "您的电影即将开始"
            //   ]
            // ]
        ];

        $styleKeys = [];
        $styleKeys['eventTicket'] = [];
        $styleKeys['eventTicket']['primaryFields'] = array(
            array(
                'key' => 'movie',
                'label' => '影片',
                'value' => $Order['sMovie']
            )
        );

        //合并取票码信息
        $allseat = "";
        $allcode = "";
        foreach ($Order['seatInfo'] as $seatInfo) {
            $allseat .= $seatInfo['seatLable'] . " ";
            $allcode .= $seatInfo['cdKey'] . ",";
        }

        //单码第二行取票信息
        $styleKeys['eventTicket']['secondaryFields'] = array(
            array(
                'key' => 'code',
                'label' => '取票信息',
                'value' => $allcode
            ),
        );
        //单码背后取票信息
        $styleKeys['eventTicket']['backFields'] = array(
            array(
                'key' => 'CinemaBackFields',
                'label' => '影院信息',
                'value' => "地址:{$Order['sCinemaAddr']}\n电话:{$Order['sCinemaPhone']}"
            ),
            array(
                'key' => 'TicketBackFields',
                'label' => '取票信息',
                'value' => "{$Order['sCinema']}\n" . $Order['sShowDate'] . "\n{$Order['sHall']} {$allseat}\n{$allcode}"
            ),
            /*
            array(
                'key' => 'TicketWayBackFields',
                'label' => '取票方式',
                'value' => "{$Order['cinema_detail']['machineInfo']['location']}\n {$Order['cinema_detail']['machineInfo']['desc']}"
            ),
            */
            array(
                'key' => 'contact',
                'label' => '客服电话',
                'value' => "1010-1068"
            )
        );
        //第三行辅助信息
        $styleKeys['eventTicket']['auxiliaryFields'] = array(

            array(
                "key" => "cinema",
                "label" => "影院",
                "value" => $Order['sCinema']
            ),
            array(
                "key" => "seat",
                "label" => "座位",
                "value" => $allseat
            ),
            array(
                "key" => "Sche",
                "label" => "场次",
                "value" => $Order['sShowDate'],
            ),

        );

        //条码信息(没有QRCODE的场次不输出二维码)
        $visualAppearanceKeys = array(
            'backgroundColor' => 'rgb(255,82,0)',
            'foregroundColor' => 'rgb(255, 255, 255)',
            'labelColor' => 'rgb(109, 252, 177)',
        );


        $webServiceKeys = array(
            //服务器Passkit RESTful配置困难本期先不做推送
            //"webServiceURL"=>"https://apppre.wepiao.com/2015110401/passkit",
            //"authenticationToken"=>md5($Order['sOrderId']),
        );
        $passData = array_merge(
            $standardKeys,
            $associatedAppKeys,
            $relevanceKeys,
            $styleKeys,
            $visualAppearanceKeys,
            $webServiceKeys
        );
        $pass->setJSON(json_encode($passData));
        $pass->addFile($path . '/icon.png');
        $pass->addFile($path . '/icon@2x.png');
        $pass->addFile($path . '/icon@3x.png');
        $pass->addFile($path . '/logo.png');
        $pass->addFile($path . '/logo@2x.png');
        $pass->addFile($path . '/logo@3x.png');
        $pass->create(true);

    }

}
