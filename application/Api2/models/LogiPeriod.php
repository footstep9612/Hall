<?php

/**
 * Description of LogiPeriodModel
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   贸易条款对应物流时效
 */
class LogiPeriodModel extends PublicModel {

    protected $dbName = 'erui2_config'; //数据库名称
    protected $tableName = 'logi_period';

    const STATUS_VALID = 'VALID';

    /**
     * 根据贸易术语，运输方式获取起运国
     * @param string $trade_terms
     * @param string $trans_mode
     * @param string $lang
     * @return array|mixed
     */
    public function getFCountry($trade_terms = '', $trans_mode = '', $lang = '') {
        if (empty($trade_terms) || empty($trans_mode))
            return array();

        $countryModel = new CountryModel();
        $t_country = $countryModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable . '.trade_terms' => $trade_terms,
            $thistable . '.trans_mode' => $trans_mode,
            $thistable . '.lang' => $lang,
            $thistable . '.status' => self::STATUS_VALID
        );

        if (redisHashExist('Country', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Country', md5(json_encode($where))), true);
        }

        $field = "$t_country.bn,$t_country.name";
        try {
            $result = $this->field($field)->group("$t_country.bn")
                            ->join($t_country . " On $thistable.from_country = $t_country.bn "
                                    . "AND $thistable.lang =$t_country.lang", 'LEFT')
                            ->where($where)->select();
            $data = array();
            if ($result) {
                $data = $result;
                redisHashSet('Country', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国获取港口城市
     * @param string $trade_terms
     * @param string $trans_mode
     * @param $from_country
     * @param string $lang
     */
    public function getFPort($trade_terms = '', $trans_mode = '', $from_country = '', $lang = '') {
        if (empty($trade_terms) || empty($trans_mode) || empty($from_country))
            return array();

        $portModel = new PortModel();
        $t_port = $portModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable . '.trade_terms' => $trade_terms,
            $thistable . '.trans_mode' => $trans_mode,
            $thistable . '.from_country' => $from_country,
            $thistable . '.lang' => $lang,
            $thistable . '.status' => self::STATUS_VALID
        );

        if (redisHashExist('Port', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Port', md5(json_encode($where))), true);
        }

        $field = "$t_port.bn,$t_port.name";
        try {
            $result = $this->field($field)->group("$t_port.bn")->join($t_port . " On $thistable.from_port = $t_port.bn AND $thistable.lang =$t_port.lang", 'LEFT')->where($where)->select();
            $data = array();
            if ($result) {
                $data = $result;
                redisHashSet('Port', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国，起运港获取目的国
     * @param string $trade_terms
     * @param string $trans_mode
     * @param string $from_country
     * @param string $from_port
     * @param string $lang
     * @return array|mixed
     */
    public function getToCountry($trade_terms = '', $trans_mode = '', $from_country = '', $from_port = '', $lang = '') {
        if (empty($trade_terms) || empty($trans_mode) || empty($from_country) || empty($from_port))
            return array();

        $countryModel = new CountryModel();
        $t_country = $countryModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable . '.trade_terms' => $trade_terms,
            $thistable . '.trans_mode' => $trans_mode,
            $thistable . '.from_country' => $from_country,
            $thistable . '.from_port' => $from_port,
            $thistable . '.lang' => $lang,
            $thistable . '.status' => self::STATUS_VALID
        );

        if (redisHashExist('Country', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Country', md5(json_encode($where))), true);
        }

        $field = "$t_country.bn,$t_country.name";
        try {
            $result = $this->field($field)->group("$t_country.bn")->join($t_country . " On $thistable.to_country = $t_country.bn AND $thistable.lang =$t_country.lang", 'LEFT')->where($where)->select();
            $data = array();
            if ($result) {
                $data = $result;
                redisHashSet('Country', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国,起运港口，目的国获取目的港口城市
     * @param string $trade_terms
     * @param string $trans_mode
     * @param $from_country
     * @param string $lang
     */
    public function getToPort($trade_terms = '', $trans_mode = '', $from_country = '', $from_port = '', $to_country = '', $lang = '') {
        if (empty($trade_terms) || empty($trans_mode) || empty($from_country) || empty($from_port) || empty($to_country))
            return array();

        $portModel = new PortModel();
        $t_port = $portModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable . '.trade_terms' => $trade_terms,
            $thistable . '.trans_mode' => $trans_mode,
            $thistable . '.from_country' => $from_country,
            $thistable . '.from_port' => $from_port,
            $thistable . '.to_country' => $to_country,
            $thistable . '.lang' => $lang,
            $thistable . '.status' => self::STATUS_VALID
        );

        if (redisHashExist('Port', md5(json_encode($where)))) {
            //return json_decode(redisHashGet('Port',md5(json_encode($where))),true);
        }

        $field = "$thistable.clearance_loc,$t_port.bn,$t_port.name";
        try {
            $result = $this->field($field)->group("$t_port.bn")->join($t_port . " On $thistable.to_port = $t_port.bn AND $thistable.lang =$t_port.lang", 'LEFT')->where($where)->select();
            $data = array();
            if ($result) {
                $data = $result;
                redisHashSet('Port', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

}
