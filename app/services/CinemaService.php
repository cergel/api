<?php

namespace app\services;


use app\base\BaseService;
use app\helper\Utils;

class CinemaService extends BaseService
{

    private $openId = "";
    private $channelId = "8";
    private $cityId = 10;
    private $token = "";
    private $cinemaId = "";
    private $iBuySource = "3";
    private $userCardList = [];
    private $cinemaCardList = [];
    private $iStatus = 0;
    private $available = [];
    private $unavailable = [];
    private $expiredCardNo = "";
    private $limitCardNo = "";
    private $cardInfo = '';
    const CARD_STATUS_NOT_LOGIN = 0;//未登录
    const CARD_STATUS_NOT_OPEN = 1;//未开卡
    const CARD_STATUS_EXPIRED = 2;//卡过期
    const CARD_STATUS_LIMITED = 3;//卡片超限制
    const CARD_STATUS_SUCCESS = 4;//卡片状态正常

    private $tipsTextArr = [
        self::CARD_STATUS_NOT_LOGIN => [
            'icon' => "icon.png",
            'tips' => "开启后购票享超值优惠价",
            'label' => "¥#price#开卡",
            'link' => "",
            'discount' => 0,
            'status' => self::CARD_STATUS_NOT_LOGIN,
            'cardInfo' => null,
        ],
        self::CARD_STATUS_NOT_OPEN => [
            'icon' => "icon.png",
            'tips' => "开启后购票享超值优惠价",
            'label' => "¥#price#开卡",
            'link' => "",
            'discount' => 0,
            'status' => self::CARD_STATUS_NOT_OPEN,
            'cardInfo' => null
        ],
        self::CARD_STATUS_EXPIRED => [
            'icon' => "icon.png",
            'tips' => "已过期，续费享受折扣卡优惠",
            'label' => "¥#price#/月续费",
            'link' => "",
            'discount' => 0,
            'status' => self::CARD_STATUS_EXPIRED,
            'cardInfo' => null
        ],
        self::CARD_STATUS_LIMITED => [
            'icon' => "icon.png",
            'tips' => "今日购票已达到折扣卡优惠上限",
            'label' => "",
            'link' => "",
            'discount' => 0,
            'status' => self::CARD_STATUS_LIMITED,
            'cardInfo' => null
        ],
        self::CARD_STATUS_SUCCESS => [
            'icon' => "icon.png",
            'tips' => "已开通，可享受折扣卡优惠价",
            'label' => "",
            'link' => "",
            'discount' => 0,
            'status' => self::CARD_STATUS_SUCCESS,
            'cardInfo' => null
        ],
    ];

    /**
     * 获取影院列表
     * 这个不再使用，直接使用大数据的接口
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaList($arrParams = [])
    {
        $return = self::getStOut();
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $params['cityId'] = !empty($arrParams['cityId']) ? $arrParams['cityId'] : '';
        if (!empty($params['channelId']) && !empty($params['cityId'])) {
            $res = $this->sdk->call("cinema/read-cinemas-city", $params);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 从大数据获取影院列表
     * 新版,此版本用于替换 getCinemaList
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaListFromEs($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['extra'] = new \stdClass();
        $res = $this->sdk->call("search/search-cinema-list-from-es", $arrParams);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data']['list'] = $res['data'];
            if (!empty($res['extra'])) {
                $return['data']['extra'] = $res['extra'];
            }
        }
        return $return;
    }

    /**
     * 从大数据获取影院列表 V2版本
     * V2相比V1, 增加服务筛选,比如 搜索可退票的影院、有小吃的影院等
     * 新版,此版本用于替换 getCinemaList
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaListFromEsV2($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['extra'] = new \stdClass();
        $res = $this->sdk->call("search/search-cinema-list-from-es-v2", $arrParams);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data']['list'] = $res['data'];
            if (!empty($res['extra'])) {
                $return['data']['extra'] = $res['extra'];
            }
            //处理APP非有偿退票信息
            if (in_array(CHANNEL_ID, \wyCupboard::$config['app_channel_ids'])) {
                $this->service("CinemaRefund")->refundCinemaListEs($return);
            }
        }
        return $return;
    }

    /**
     * 调用sdk，获取影院筛选信息，如：某个城市下所有影院下的品牌、上映日期等待
     *
     * @param array $arrInput
     *
     * @return string
     */
    public function readSearchCinemaFilter($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        if (!empty($arrParams['cityId'])) {
            $res = $this->sdk->call('search/search-cinema-filters', $arrParams);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                $return['data']['list'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 调用sdk，获取影院筛选信息，如：某个城市下所有影院下的品牌、上映日期等待——V2版本(返回值增加服务数据)
     *
     * @param array $arrInput
     *
     * @return string
     */
    public function readSearchCinemaFilterV2($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        if (!empty($arrParams['cityId'])) {
            $res = $this->sdk->call('search/search-cinema-filters-v2', $arrParams);
            if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
                    $this->_gewaraAppFormat($res['data']);
                }
                $return['data']['list'] = $res['data'];
            }
        }
        return $return;
    }

    /**
     * 从大数据获取在映某个影片的影院列表
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaListByMovieFromEs($params = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['extra'] = new \stdClass();
        $res = $this->sdk->call("search/search-movie-cinema-list-from-es", $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data']['list'] = $res['data'];
            if (!empty($res['extra'])) {
                $return['data']['extra'] = $res['extra'];
            }
        }


        return $return;
    }

    /**
     * 从大数据获取在映某个影片的影院列表 V2版本
     * V2相比V1, 增加服务筛选,比如 搜索可退票的影院、有小吃的影院等
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaListByMovieFromEsV2($arrParams = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['extra'] = new \stdClass();
        $res = $this->sdk->call("search/search-movie-cinema-list-from-es-v2", $arrParams);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data']['list'] = $res['data'];
            if (!empty($res['extra'])) {
                $return['data']['extra'] = $res['extra'];
            }
        }

        return $return;
    }


    /**
     * 获取影院详情
     *
     * @param array $arrParams
     *
     * @return array
     */
    public function getCinemaInfo($params = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (!empty($params['cinemaId'])) {
            $return = $this->sdk->call("cinema/read-cinema-info-v3", $params);
        }
        return $return;
    }

    /**
     * 获取影院座位图
     *（新版切到融合版座位图，该接口可废弃）
     * @param array $arrParams
     * @return array
     */
    public function getCinemaSeats($arrParams = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $params = [];
        $params['cinemaId'] = !empty($arrParams['cinemaId']) ? $arrParams['cinemaId'] : '';
        $params['roomId'] = !empty($arrParams['roomId']) ? $arrParams['roomId'] : '';
        $params['channelId'] = !empty($arrParams['channelId']) ? $arrParams['channelId'] : CHANNEL_ID;
        $res = $this->sdk->call("cinema/read-cinema-room", $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
            if (!empty($arrParams['movieId'])) {
                $params2 = array(
                    'movieId' => $arrParams['movieId'],
                    'channelId' => $arrParams['channelId'],
                );
                $res = $this->sdk->call("movie/get-movie-custom-seat", $params2);
                if (isset($res['data']['customization_seats'])) {
                    $return['data']['customization_seats'] = $res['data']['customization_seats'];
                }
            }
        }
        return $return;
    }

    /**
     * 获取支持某个会员卡的影院列表
     *
     * @param array $params
     *
     * @return array
     */
    public function getCardCinemaList($params = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();

        $res = $this->sdk->call("search/search-card-cinema-list", $params);
        if (!empty($res) && isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
            $return['data'] = $res['data'];
        }
        return $return;
    }

    /**
     * @param array $params
     * @return array
     * 说明：
     * 原本APP为先判断影院是否存在折扣卡
     * 如果不存在直接返回前端没有
     * 但是后来有折扣卡下架后仍然可以使用
     * 所以需要对折扣卡状态进行修改
     */
    public function CinemaVipCard($params = [])
    {
        $this->cardInfo = new \stdClass();
        $this->openId = $params['openId'];
        $this->channelId = $params['channelId'];
        $this->cinemaId = $params['cinemaId'];
        $this->cityId = $params['cityId'];
        $this->iBuySource = !empty($params['buySource']) ? $params['buySource'] : 3;
        $this->token = isset($_SERVER['HTTP_TOKEN']) ? $_SERVER['HTTP_TOKEN'] : '';
        $return = [];
        $return['ret'] = 0;
        $return['sub'] = 0;
        $return['data'] = [];

        //判断该影院是否有会员卡功能，如果没有则返回空对象
        $cinemaCard = $this->getCinemaVip();
        if (!empty($cinemaCard)) {
            //设置会员卡状态，设置会员卡文案，格式化会员卡文案，设置会员卡链接一般模式
            $this->getVipCardStatus()->formatText()->setAppUrl();
            $return['data'] = $this->getCardText($this->iStatus);
        } else {
            //如果影院不存在折扣卡用户有这个影院的折扣卡且未过期则显示折扣卡入口
            $this->getVipCardStatus();
            if (in_array($this->iStatus, [self::CARD_STATUS_LIMITED, self::CARD_STATUS_SUCCESS])) {
                $this->formatText()->setAppUrl();
                $return['data'] = $this->getCardText($this->iStatus);
            } else {
                $return['data'] = new \stdClass();
            }

        }
        return $return;
    }

    /**
     * 添加格瓦拉影院id
     * @param $data
     */
    public function _formatAddGewaraCinemaId(&$data)
    {
        $cinemaId = $data['id'];
        $channelId = CHANNEL_ID;
        $gewaraCinemaId = $this->sdk->call("cinema/get-gewala-cinema-id", compact('cinemaId', 'channelId'));
        $data['gewara_id'] = $gewaraCinemaId;
    }

    /**
     * 添加格瓦拉影院是否可定制票
     * @param $data
     */
    public function _formatAddGewaraCinemaPaper(&$data)
    {
        $msg = '可定制票纸';
        $cinemaId = $data['id'];
        $channelId = CHANNEL_ID;
        $data['t_paper'] = $this->sdk->call("cinema/is-gewala-paper", compact('cinemaId', 'channelId'));
        $data['t_paper_msg'] = $msg;
    }

    //验证卡片有效期
    private function verifyCardDate()
    {
        $valid = false;
        foreach ($this->userCardList as $card) {
            if (time() < strtotime($card['expireTime'])) {
                $valid = true;
            } else {
                $this->expiredCardNo = $card['cardNo'];
                $this->unavailable = $card;
            }
        }
        return $valid;
    }

    //验证卡片购票上限验证成功后返回卡片信息
    private function verifyCardLimit()
    {
        $return = [];
        $return['card'] = [];
        $return['valid'] = false;
        foreach ($this->userCardList as $card) {
            if (time() < strtotime($card['expireTime'])) {
                if ($card['intervalLeft'] > 0 && $card['totalLeft'] > 0) {
                    $return = true;
                    $this->available = $card;
                    //设置可用卡片信息
                    $cardInfo = [];
                    $cardInfo['cardNo'] = $card['cardNo'];
                    $cardInfo['intervalLeft'] = $card['intervalLeft'];
                    $this->available = $cardInfo;
                    $this->tipsTextArr[self::CARD_STATUS_SUCCESS]['cardInfo'] = $cardInfo;
                    return $return;
                } else {
                    $return = false;
                    $cardInfo = [];
                    $cardInfo['cardNo'] = $card['cardNo'];
                    $cardInfo['intervalLeft'] = $card['intervalLeft'];
                    $this->unavailable = $cardInfo;
                    $this->tipsTextArr[self::CARD_STATUS_LIMITED]['cardInfo'] = $cardInfo;
                    return $return;
                }
            }
        }
        return $return;
    }

    //格瓦拉filter格式化
    private function _gewaraAppFormat(&$data)
    {
        if (isset($data['servs'])) {
            foreach ($data['servs'] as &$val) {
                if ($val['value'] == 'snack') {
                    $val['name'] = '卖品';
                }
            }
        }
    }

    /**
     * 获取影院VIP折扣卡信息
     * @return array
     */
    private function getCinemaVip()
    {
        if (empty($this->cinemaCardList)) {
            //判断该影院是否有会员卡功能，如果没有则返回空对象
            $paramsCheckCinema = [];
            //因为商品中心处理不了一些地级市的ID所以这里修改为暂时不传cityId
            //$paramsCheckCinema['cityId'] = $this->cityId;
            $paramsCheckCinema['cinemaId'] = $this->cinemaId;
            $paramsCheckCinema['channelId'] = $this->channelId;
            $response = $this->sdk->call("cinema-vip/get-card-list", $paramsCheckCinema);
            $this->cinemaCardList = !empty($response['data'][0]) ? $response['data'][0] : [];
        }
        return $this->cinemaCardList;
    }

    /**
     * 获取折扣卡的状态
     */
    private function getVipCardStatus()
    {
        //判断用户是否登录
        if (empty($this->openId)) {
            $this->iStatus = self::CARD_STATUS_NOT_LOGIN;
            return $this;
        }
        //判断用户是否有本影院的会员卡
        $userCards = $this->getUserCardList();
        if (empty($userCards)) {
            $this->iStatus = self::CARD_STATUS_NOT_OPEN;
            return $this;
        }
        //判断可用卡片是否过期
        if (!$this->verifyCardDate()) {
            $this->iStatus = self::CARD_STATUS_EXPIRED;
            return $this;
        }
        //判断可用卡片是否到量
        if (!$this->verifyCardLimit()) {
            $this->iStatus = self::CARD_STATUS_LIMITED;
            return $this;
        }
        //卡片正常可用
        $this->iStatus = self::CARD_STATUS_SUCCESS;
        return $this;
    }

    /**
     * 格式化文本文案
     */
    private function formatText()
    {
        $cinemaCard = $this->getCinemaVip();
        //判断当前的状态是否需要处理优惠文案
        if (in_array($this->iStatus, [self::CARD_STATUS_NOT_LOGIN, self::CARD_STATUS_NOT_OPEN])) {
            if (is_array($cinemaCard['discount']) && !empty($cinemaCard['discount']) && !empty($cinemaCard['discount']['discountText'])) {
                $this->tipsTextArr[$this->iStatus]['tips'] = $cinemaCard['discount']['discountName'];
                $this->tipsTextArr[$this->iStatus]['label'] = $cinemaCard['discount']['discountText'];
                $this->tipsTextArr[$this->iStatus]['discount'] = 1;
            } elseif (is_array($cinemaCard['discount']) && !empty($cinemaCard['discount']) && empty($cinemaCard['discount']['discountText'])) {
                $price = $cinemaCard['discount']['discountPrice'] / 100;
                $this->tipsTextArr[$this->iStatus]['label'] = str_replace("#price#", $price,
                    $this->tipsTextArr[$this->iStatus]['label']);
                $this->tipsTextArr[$this->iStatus]['discount'] = 1;
                $this->tipsTextArr[$this->iStatus]['tips'] = $cinemaCard['discount']['discountName'];
            } else {
                $price = $cinemaCard['sellPrice'] / 100;
                $this->tipsTextArr[$this->iStatus]['label'] = str_replace("#price#", $price,
                    $this->tipsTextArr[$this->iStatus]['label']);
            };
            //处理过期文案的价格
        } elseif (in_array($this->iStatus, [self::CARD_STATUS_EXPIRED])) {
            $price = round($cinemaCard['sellPrice'] / 100 / $cinemaCard['validMonth'], 1);
            $this->tipsTextArr[$this->iStatus]['label'] = str_replace("#price#", $price,
                $this->tipsTextArr[$this->iStatus]['label']);
            $this->tipsTextArr[$this->iStatus]['cardInfo'] = $this->cardInfo;
        }
        return $this;
    }

    /**
     * 设置APP的跳转链接
     */
    private function setAppUrl()
    {
        switch ($this->iStatus) {
            case self::CARD_STATUS_NOT_LOGIN :
                $url = "https://vip.wepiao.com/appcinemavip?buysource={$this->iBuySource}&page=detail&cityid={$this->cityId}&cinemaid={$this->cinemaId}&channelId={$this->channelId}&token={$this->token}";
                $this->tipsTextArr[$this->iStatus]['link'] = $this->getJumpUrl($url);
                break;
            case self::CARD_STATUS_NOT_OPEN :
                $url = "https://vip.wepiao.com/appcinemavip?buysource={$this->iBuySource}&page=detail&cityid={$this->cityId}&cinemaid={$this->cinemaId}&channelId={$this->channelId}&token={$this->token}";
                $this->tipsTextArr[$this->iStatus]['link'] = $this->getJumpUrl($url);
                break;
            case self::CARD_STATUS_EXPIRED :
                $url = "https://vip.wepiao.com/appcinemavip?buysource={$this->iBuySource}&page=detail&cardno={$this->expiredCardNo}&cityid={$this->cityId}&token={$this->token}&channelId={$this->channelId}";
                $this->tipsTextArr[$this->iStatus]['link'] = $this->getJumpUrl($url);
                break;
            case self::CARD_STATUS_LIMITED :
                $url = "https://vip.wepiao.com/appcinemavip?buysource={$this->iBuySource}&page=detail&cardno={$this->unavailable['cardNo']}&cityid={$this->cityId}&token={$this->token}&channelId={$this->channelId}";
                $this->tipsTextArr[$this->iStatus]['link'] = $this->getJumpUrl($url);
                break;
            case self::CARD_STATUS_SUCCESS :
                $url = "https://vip.wepiao.com/appcinemavip?buysource={$this->iBuySource}&page=detail&cardno={$this->available['cardNo']}&cityid={$this->cityId}&token={$this->token}&channelId={$this->channelId}";
                $this->tipsTextArr[$this->iStatus]['link'] = $this->getJumpUrl($url);
                break;
        }
        return $this;
    }

    /**
     * 获取卡片文案
     * @return mixed
     */
    private function getCardText()
    {
        return $this->tipsTextArr[$this->iStatus];
    }

    /**
     * 折扣卡跳转链接
     * 因为一些参数不方便一直跟着url传递所以写入到cookie内进行传递
     * @param string $url
     * @return string
     */
    public function getJumpUrl($url = "")
    {
        //判断是否是 格瓦拉渠道
        $uri = 'wxmovie';
        if (in_array(CHANNEL_ID, \wyCupboard::$config['gewara_channel_ids'])) {
            $uri = 'gewara';
        }
        $urlSetCookie = Utils::getHost() . "/wap/jump.html?channelId=" . CHANNEL_ID . "&url=";
        return "{$uri}://usertoken?url=" . urlencode($urlSetCookie . base64_encode($url));
    }

    /**
     * 获取用户
     * @return array
     */
    private function getUserCardList()
    {
        if (empty($this->userCardList)) {
            $paramsUserCard = [];
            $paramsUserCard['openId'] = $this->openId;
            $paramsUserCard['channelId'] = $this->channelId;
            $paramsUserCard['cinemaId'] = $this->cinemaId;
            $response = $this->sdk->call("cinema-vip/get-user-card-list", $paramsUserCard);
            if ($response['ret'] == 0 && $response['sub'] == 0) {
                $this->userCardList = $response['data']['list'];
            }
        }
        return $this->userCardList;
    }
}