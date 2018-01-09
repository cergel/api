<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/6/3
 * Time: 15:50
 */
namespace app\base;
class BaseModel
{
    protected static $self = null;

    protected $redis = null;
    protected $redisType = null;

    protected $pdo = null;
    protected $pdoDbType = null;
    protected $pdoTableName = null;

    public static function getInstance()
    {
        if (self::$self === null) {
            self::$self = new static;
        }
        return self::$self;
    }

    public function init()
    {

    }

    //根据配置加载redisManager到redis属性
    public function getRedis($redisType = null, $channelId = 'common')
    {
        if (empty($redisType)) {
            $redisType = $this->redisType;
        }
        if (isset(\wyCupboard::$config['redis'][$redisType][$channelId])) {
            $redisConf = \wyCupboard::$config['redis'][$redisType][$channelId];
        } elseif (isset(\wyCupboard::$config['redis'][$redisType]['common'])) {
            $redisConf = \wyCupboard::$config['redis'][$redisType]['common'];
        } else {
            throw new \Exception('unknow constant redis type:' . $redisType);
        }
        return \redisManager\redisManager::getInstance($redisConf);
    }


    //根据配置加载orm到orm属性
    public function getPdo($dbType = null, $tableName = null)
    {
        if (empty($dbType)) {
            $dbType = $this->pdoDbType;
        }
        if (empty($tableName)) {
            $tableName = $this->pdoTableName;
        }
        $conf = \wyCupboard::$config['db'][$dbType];
        return \pdoManager\pdoManager::getInstance($conf, $tableName);
    }

    /**
     * 将key的模板转换成真正的key
     * @param $input k=>v形式，k是$keyTempplate中的key  v是真正的值
     * @param $keyTemplate key的模板，通常定义在constant中
     * @return mixed
     */
    protected function swtichRedisKey($input, $keyTemplate)
    {
        $keys = array_keys($input);
        $keys = $keys[0];
        $search = '{#' . $keys . '}';
        return str_replace($search, $input[$keys], $keyTemplate);
    }

    //string类型被动缓存函数
    /**
     * @param $inpuKey      redis中的key
     * @param $keyTemplate  redis的key的模板
     * @param $dbConfigKey  pdo()函数中用到的$dbConfigKey
     * @param $tableName    pdo()函数中用到的$tableName
     * @param $cacheTime    缓存时间
     * @param $where        数据库查询时的条件
     * @param $whereParams  查询条件的params
     * @return mixed
     */
    protected function queryStringCache($inpuKey, $keyTemplate, $dbConfigKey, $tableName, $cacheTime, $where, $whereParams)
    {
        $redisKey = $this->swtichRedisKey($inpuKey, $keyTemplate);
        $redisRe = $this->getRedis()->WYget($redisKey);
        if ($redisRe !== false) {
            $return = $redisRe;
        } else {
            $dbRe = $this->getPdo()->fetchOne($where, $whereParams);
            if ($dbRe) {
                $return = json_encode($dbRe);
                $this->getRedis()->WYset($redisKey, $return);
            } else {
                $return = '';
                $this->getRedis()->WYset($redisKey, '');
            }
        }
        $this->getRedis()->WYexpire($redisKey, $cacheTime);
        return $return;
    }


    //hash类型被动缓存函数
    /**
     * @param $inpuKey      redis中的key
     * @param  $hashKey     hashKey
     * @param $keyTemplate  redis的key的模板
     * @param $dbConfigKey  pdo()函数中用到的$dbConfigKey
     * @param $tableName    pdo()函数中用到的$tableName
     * @param $cacheTime    缓存时间
     * @param $where        数据库查询时的条件
     * @param $whereParams  查询条件的params
     * @return mixed
     */
    protected function queryHashCache($inpuKey, $hashKey, $keyTemplate, $dbConfigKey, $tableName, $cacheTime, $where, $whereParams, $field = '*')
    {
        $redisKey = $this->swtichRedisKey($inpuKey, $keyTemplate);
        $redisRe = $this->getRedis()->WYhGet($redisKey, $hashKey);
        //$this->getRedis()->WYdelete($redisKey);
        if ($redisRe !== false) {
            $return = $redisRe;
        } else {
            $dbRe = $this->getPdo()->fetchOne($where, $whereParams, $field);
            if ($dbRe) {
                if ($field != '*') {
                    $return = $dbRe[$field];
                } else {
                    $return = json_encode($dbRe);
                }
                $this->getRedis()->WYhSet($redisKey, $hashKey, $return);
            } else {
                $return = '';
                $this->getRedis()->WYhSet($redisKey, $hashKey, '');
            }
        }
        $this->getRedis()->WYexpire($redisKey, $cacheTime);
        return $return;
    }


    /**
     * @param $arrInputKey 大K数组
     * @param $hashKey 小K
     * @param $keyTemplate 大K模板
     * @param $value 值
     * @param int $expire 过期时间
     * @return mixed
     */
    protected function addHashCache($arrInputKey, $hashKey, $keyTemplate, $value, $expire = 86400)
    {
        $redisKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $return = $this->getRedis()->WYhSet($redisKey, $hashKey, $value);
        if ($return) {
            $this->getRedis()->WYexpire($redisKey, $expire);
        }
        return $return;
    }

    /**
     * @param $arrInputKey 大K数组
     * @param $hashKey 小K
     * @param $keyTemplate 大K模板
     * @param $value 值
     * @param int $expire 过期时间
     * @return mixed
     */
    protected function delHashCache($arrInputKey, $hashKey, $keyTemplate)
    {
        $redisKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $return = $this->getRedis()->WYhDel($redisKey, $hashKey);
        return $return;
    }

    //有序集合通用被动缓存查询方法
    /*
        $arrRedisInfo = [
            'redisKey'=>要查找的redisKey,
            'redisKeyTemplate'=>要查找的redisKey的template,
            'redisCountKey'=>计算总数的redisKey,
            'redisCountKeyTemplate'=>计算总数的redisKey的template,
            'scoreField'=>查询出的哪个字段用于保存score,
            'valueField'=>查询出的哪个字段用于保存value,
            'start'=>查询的起始位置,
            'end'=>查询的结束位置,
            'expire'=>缓存的过期时间,
        ];
        $arrSqlInfo=[
            'table'=>要查询的数据表名,
            'where'=>查询的条件,
            'params'=>预处理,
            'orderBy'=>排序字段,
            'step'=> 步长值,
        ];
        $orderDesc true:使用zrevrange false:使用zrange
        $withScores true:返回值为[value1=>score1,value2=>score2,....] false:返回值为[value1,value2,....]
     */
    protected function queryZsetCache($arrRedisInfo, $arrSqlInfo, $orderDesc = true, $withScores = false)
    {
        $return = false;
        if ($arrRedisInfo['start'] > $arrRedisInfo['end']) {
            return $return;
        }

        //确定排序函数
        $fun = $orderDesc ? 'WYzRevRange' : 'WYzRange';
        //集合key
        $zsetKey = $this->swtichRedisKey($arrRedisInfo['redisKey'], $arrRedisInfo['redisKeyTemplate']);
        //总数key
        $countKey = $this->swtichRedisKey($arrRedisInfo['redisCountKey'], $arrRedisInfo['redisCountKeyTemplate']);
        $countRedisExists = $this->getRedis()->WYexists($countKey);
        //计算总数
        if (empty($countRedisExists)) {
            $countSql = 'select count(*) from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'];
            $dbRe = $this->getPdo()->fetchOneBySql($countSql, $arrSqlInfo['params']);
            $count = $dbRe['count(*)'] ? $dbRe['count(*)'] : 0;
            $setRe = $this->getRedis()->WYset($countKey, $count);
        } else {
            $count = $this->getRedis()->WYget($countKey);
        }
        $this->getRedis()->WYexpire($countKey, $arrRedisInfo['expire']);
        //读key
        $redisExists = $this->getRedis()->WYexists($zsetKey);
        $scoreField = isset($arrRedisInfo['selectScoreField']) ? $arrRedisInfo['selectScoreField'] : $arrRedisInfo['scoreField'];
        $valueField = isset($arrRedisInfo['selectValueField']) ? $arrRedisInfo['selectValueField'] : $arrRedisInfo['valueField'];
        if ($redisExists) {//如果有key
            $redisRe = $this->getRedis()->$fun($zsetKey, 0, 0);//正序取第一个，判断是否是空
            if (isset($redisRe) && $redisRe[0] == '') {//如果是，返回false
                $return = false;
            } else {
                //取出缓存中的数目
                $keyNums = $this->getRedis()->WYzCard($zsetKey);
                if ($keyNums > 0) {
                    if ($arrRedisInfo['start'] <= $keyNums && $arrRedisInfo['end'] <= $keyNums) {
                        //已经缓存的区间之内，只需要查询redis,不用查询db
                        $return = $this->getRedis()->$fun($zsetKey, $arrRedisInfo['start'], $arrRedisInfo['end'], $withScores);
                    } elseif ($arrRedisInfo['start'] <= $keyNums && $arrRedisInfo['end'] > $keyNums) {
                        //1.根据步长值在db中生成数据
                        if ($keyNums < $count) {//如果缓存的key的数，小于db中的总数，那么按照步长查询一批数据塞进缓存
                            $limitStart = $keyNums - floor($arrSqlInfo['step'] / 10);//起始的查询值略小于key的总数，防止由于查询时并发插入遗漏一部分
                            $limitStart = $limitStart > 0 ? $limitStart : 0;
                            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit ' . $limitStart . ',' . $arrSqlInfo['step'];
                            $dbRe = $this->getPdo()->fetchArrayBySql($sql, $arrSqlInfo['params']);
                            if ($dbRe) {
                                foreach ($dbRe as $info) {
                                    $this->getRedis()->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                                }
                            }
                        }
                        //2.通过缓存拿到要返回的数据
                        $return = $this->getRedis()->$fun($zsetKey, $arrRedisInfo['start'], $arrRedisInfo['end'], $withScores);
                    } else {//如果超出了缓存范围，有可能要使用db查询
                        if ($keyNums < $count) {//如果缓存的key的数，小于db中的总数，那么按照步长查询一批数据塞进缓存
                            $limitStart = $keyNums - floor($arrSqlInfo['step'] / 10);//起始的查询值略小于key的总数，防止由于插入太多在查询的时候遗漏一部分
                            $limitStart = $limitStart > 0 ? $limitStart : 0;
                            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit ' . $limitStart . ',' . $arrSqlInfo['step'];
                            $dbRe = $this->getPdo()->fetchArrayBySql($sql, $arrSqlInfo['params']);
                            if ($dbRe) {
                                foreach ($dbRe as $info) {
                                    $this->getRedis()->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                                    if ($withScores) {
                                        $return[$info[$arrRedisInfo['valueField']]] = $info[$arrRedisInfo['scoreField']];
                                    } else {
                                        $return[] = $info[$arrRedisInfo['valueField']];
                                        $return = array_unique($return);
                                    }
                                }
                            } else {
                                $return = false;
                            }
                        } else {
                            $return = false;
                        }
                    }
                } else {
                    $return = false;
                }
            }
        } else {//如果没有key
            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit 0 ,' . $arrSqlInfo['step'];
            $dbRe = $this->getPdo()->fetchArrayBySql($sql, $arrSqlInfo['params']);
            if ($dbRe) {//如果有数据，存入缓存，并设置超时时间
                $i = 0;
                foreach ($dbRe as $info) {
                    $this->getRedis()->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                    //todo
                    if ($i >= $arrRedisInfo['start'] && $i <= $arrRedisInfo['end']) {
                        $return[] = $info[$arrRedisInfo['valueField']];
                    }
                    $i++;
                }
                $return = array_unique($return);
            } else {//没有数据缓存空值
                $this->getRedis()->WYzAdd($zsetKey, 0, '');
            }
        }
        $this->getRedis()->WYexpire($zsetKey, $arrRedisInfo['expire']);
        return $return;
    }

    //有序集合通用插入方法
    protected function addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire = 86400)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $redisRe = $this->getRedis()->WYzRange($key, 0, 0);
        if (!empty($redisRe) && $redisRe[0] == '') {
            $this->getRedis()->WYdelete($key);
        }
        $addRe = $this->getRedis()->WYzAdd($key, $score, $value);
        if ($expire > 0) {
            $this->getRedis()->WYexpire($key, $expire);
        }
        if ($addRe) {
            if ($countKey) {
                $this->getRedis()->WYincr($countKey);//计数的key +1
                if ($expire > 0) {
                    $this->getRedis()->WYexpire($countKey, $expire);
                }
            }
        }
        return $addRe;
    }

    //向存在的有序集合通用插入方法--仅当有序集合存在的时候插入
    protected function addExistZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire = 86400)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $exists = $this->getRedis()->WYexists($key);
        if ($exists) {
            $redisRe = $this->getRedis()->WYzRange($key, 0, 0);
            if (!empty($redisRe) && $redisRe[0] == '') {
                $this->getRedis()->WYdelete($key);
            }
            $addRe = $this->getRedis()->WYzAdd($key, $score, $value);
            if ($expire > 0) {
                $this->getRedis()->WYexpire($key, $expire);
            }
            if ($addRe) {
                if ($countKey) {
                    $this->getRedis()->WYincr($countKey);//计数的key +1
                    if ($expire > 0) {
                        $this->getRedis()->WYexpire($countKey, $expire);
                    }
                }
            }
            $return = $addRe;
        } else {
            $return = false;
        }
        return $return;
    }

    //有序集合通用删除方法
    protected function delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $remRe = $this->getRedis()->WYzRem($key, $value);
        if ($remRe) {
            if ($countKey) {
                $nums = $this->getRedis()->WYget($countKey);
                if ($nums > 0) {
                    $this->getRedis()->WYdecr($countKey);//计数的key -1
                }
            }
        }
        return $remRe;
    }

    //查询有序列表的count总数，通用被动缓存
    protected function queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire = 86400)
    {
        //总数key
        $countKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $countRedisExists = $this->getRedis()->WYexists($countKey);
        //计算总数
        if (empty($countRedisExists)) {
            $countSql = 'select count(*) from ' . $table . ' where ' . $where;
            $dbRe = $this->getPdo()->fetchOneBySql($countSql, $params);
            $count = $dbRe['count(*)'] ? $dbRe['count(*)'] : 0;
            $setRe = $this->getRedis()->WYset($countKey, $count);
        } else {
            $count = $this->getRedis()->WYget($countKey);
        }
        $this->getRedis()->WYexpire($countKey, $expire);
        return $count;
    }

    /**
     * 格式化输入的数组，去掉不在列表中的字段,并且验证必传字段
     */
    protected function formatInputArray($inputArrData, $arrFields, $mustFields = [])
    {
        $arrData = [];
        $mustNum = 0;
        foreach ($inputArrData as $k => $v) {
            if (in_array($k, $arrFields)) {
                $arrData[$k] = $v;
            }
            if (in_array($k, $mustFields)) {
                $mustNum++;
            }
        }
        if ($mustNum != count($mustFields)) {
            throw new \Exception(__FUNCTION__ . ' must input ' . json_encode($mustFields));
        }
        return $arrData;
    }
}