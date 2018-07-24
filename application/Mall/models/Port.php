<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class PortModel extends PublicModel {

    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'port'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取港口
     * @param string $lang
     * @param string $country
     * @return array|mixed
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function getPort($lang = '', $country = '') {
        $condition = array(
            'lang' => $lang,
            'deleted_flag' => 'N'
        );
        if (!empty($country)) {
            $condition['country_bn'] = $country;
        }
        if (redisHashExist('Port', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('Port', md5(json_encode($condition))), true);
        }
        try {
            $field = 'lang,country_bn,bn,name,port_type,trans_mode,remarks,address,longitude,latitude';
            $result = $this->field($field)->where($condition)->order('bn')->select();
            if ($result) {
                redisHashSet('Port', md5(json_encode($condition)), json_encode($result));
                return $result;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 根据简称与语言获取国家名称
     * @param array $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function getPortByBns($bns = [], $lang = '') {
        if (empty($bns) || empty($lang))
            return '';

        if (redisHashExist('Port', implode('_', $bns) . '_' . $lang)) {
            return json_decode(redisHashGet('Port', implode('_', $bns) . '_' . $lang), true);
        }
        try {
            $condition = array(
                'bn' => ['in', $bns],
                'lang' => $lang,
                    // 'status'=>self::STATUS_VALID
            );
            $field = 'bn,name';
            $data = $this->field($field)->where($condition)->select();
            $result = [];
            if ($data) {
                foreach ($data as $item) {
                    $result[$item['bn']] = $item['name'];
                }
            }
            if ($result) {
                redisHashSet('Port', implode('_', $bns) . '_' . $lang, json_encode($result));
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

}
