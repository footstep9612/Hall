<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/30
 * Time: 19:46
 */
class PortModel extends PublicModel {

    protected $dbName = 'erui2_dict'; //数据库名称
    protected $tableName = 'port'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取港口
     * @param string $lang
     * @param string $country
     * @return array|mixed
     */
    public function getPort($lang = '', $country = '') {
        $condition = array(
            'lang' => $lang,
        );
        if (!empty($country)) {
            $condition['country_bn'] = $country;
        }

        if (redisHashExist('Port', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('Port', md5(json_encode($condition))), true);
        }
        try {
            $field = 'lang,country_bn,bn,name,port_type,trans_mode,description,address,longitude,latitude';
            $result = $this->field($field)->where($condition)->order('bn')->select();
            if ($result) {
                redisHashSet('Port', md5(json_encode($condition)), json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
    }

    /*
     * 条件id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude
     */

    function getCondition($condition) {
        $where = [];
        if (isset($condition['id']) && $condition['id']) {
            $where['id'] = $condition['id'];
        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['bn']) && $condition['bn']) {
            $where['bn'] = $condition['bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
        }
        if (isset($condition['port_type']) && $condition['port_type']) {
            $where['port_type'] = $condition['port_type'];
        }
        if (isset($condition['trans_mode']) && $condition['trans_mode']) {
            $where['trans_mode'] = $condition['trans_mode'];
        }
        if (isset($condition['name']) && $condition['name']) {
            $where['name'] = ['like', '%' . $condition['name'] . '%'];
        }
        if (isset($condition['address']) && $condition['address']) {
            $where['address'] = ['like', '%' . $condition['address'] . '%'];
        }
        return $where;
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {

            return 0;
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getListbycondition($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude';

            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getAll($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude';

            $result = $this->field($field)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (!isset($data['bn']) || !$data['bn']) {
            return false;
        }
        $where['bn'] = $data['bn'];

        $newbn = ucwords($data['en']['name']);
        $data['en']['name'] = ucwords($data['en']['name']);
        $this->startTrans();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $flag = $this->updateandcreate($data, $lang, $newbn);
            if (!$flag) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    private function updateandcreate($data, $lang, $newbn) {
        if (isset($data[$lang]['name'])) {
            $where['lang'] = $lang;
            $where['bn'] = $data['bn'];
            $arr['bn'] = $newbn;
            $arr['lang'] = $lang;
            $arr['name'] = $data[$lang]['name'];
            $arr['country_bn'] = $data['country_bn'];
            $arr['trans_mode'] = $data['trans_mode'];
            $arr['port_type'] = $data['port_type'];
            $arr['description'] = $data['description'];

            if ($this->Exits($where)) {
                $flag = $this->where($where)->save($arr);
                return $flag;
            } else {
                $flag = $this->add($data);
                return $flag;
            }
        } else {
            return true;
        }
    }

    public function Exits($where) {

        return $this->where($where)->find();
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $datalist = [];
            $arr['bn'] = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $arr['country_bn'] = $create['country_bn'];
            $arr['trans_mode'] = $create['trans_mode'];
            $arr['port_type'] = $create['port_type'];
            $arr['description'] = $create['description'];
            $langs = ['en', 'zh', 'es', 'ru'];
            foreach ($langs as $lang) {
                if (isset($create[$lang]['name'])) {
                    $arr['lang'] = $lang;
                    $arr['name'] = $create[$lang]['name'];
                    $datalist[] = $arr;
                }
            }
            return $this->addAll($datalist);
        } else {
            return false;
        }
    }

}
