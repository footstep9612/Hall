<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Manufacturer
 * @author  zhongyg
 * @date    2017-8-10 9:03:58
 * @version V2.0
 * @desc
 */
class ManufacturerModel extends PublicModel {

    //put your code here
    protected $tableName = 'manufacturer';
    protected $dbName = 'erui2_supplier'; //数据库名称

//    protected $autoCheckFields = false;

    public function __construct() {
        parent::__construct();
    }

    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    private function _field() {

        return 'id,lang,manufacturer_no,manufacturer_type,name,bn,profile,country_code,'
                . 'country_bn,province,city,official_email,official_phone,official_fax,'
                . 'contact_first_name,contact_last_name,brand,official_website,logo,'
                . 'sec_ex_listed_on,manufacturer_level,recommend_flag,status,remarks,'
                . 'created_by,created_at,checked_by,checked_at,deleted_flag';
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    private function _getCondition($condition) {
        $data = [];

        $this->_getValue($data, $condition, 'lang', 'string');
        $this->_getValue($data, $condition, 'manufacturer_no');
        $this->_getValue($data, $condition, 'id');

        $this->_getValue($data, $condition, 'manufacturer_type');
        $this->_getValue($data, $condition, 'manufacturer_type');
        $this->_getValue($data, $condition, 'status', 'string', 'status', self::STATUS_VALID);
        $this->_getValue($data, $condition, 'bn');
        $this->_getValue($data, $condition, 'country_code');
        $this->_getValue($data, $condition, 'country_bn');
        $this->_getValue($data, $condition, 'name', 'like');
        $this->_getValue($data, $condition, 'province');
        $this->_getValue($data, $condition, 'city');
        $this->_getValue($data, $condition, 'official_email');
        $this->_getValue($data, $condition, 'official_phone');
        $this->_getValue($data, $condition, 'official_fax');
        $this->_getValue($data, $condition, 'contact_first_name');
        $this->_getValue($data, $condition, 'contact_last_name');
        $this->_getValue($data, $condition, 'brand');
        $this->_getValue($data, $condition, 'official_website');
        $this->_getValue($data, $condition, 'logo');
        $this->_getValue($data, $condition, 'sec_ex_listed_on');
        $this->_getValue($data, $condition, 'manufacturer_level');
        $this->_getValue($data, $condition, 'recommend_flag');
        $this->_getValue($data, $condition, 'status');
        $this->_getValue($data, $condition, 'created_by');
        $this->_getValue($data, $condition, 'created_at', 'between');
        $this->_getValue($data, $condition, 'checked_by');
        $this->_getValue($data, $condition, 'checked_at', 'between');
        return $data;
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @param string $order 排序
     * @param bool $type 是否分页
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $data = $this->_getCondition($condition);
            $redis_key = md5(json_encode($data));
            if (redisHashExist('Manufacturer', $redis_key)) {
                return json_decode(redisHashGet('Manufacturer', $redis_key), true);
            }
            $field = $this->_field();
            $result = $this->field($field)
                            ->where($data)->order($order)->select();
            redisHashSet('Manufacturer', $redis_key, json_encode($result));

            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return [];
        }
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            $redis_key = md5(json_encode($data)) . '_COUNT';
            if (redisHashExist('Manufacturer', $redis_key)) {
                return redisHashGet('Manufacturer', $redis_key);
            }
            $count = $this->where($data)->count();

            redisHashSet('Manufacturer', $redis_key, $count);

            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * Description of 详情
     * @param string $bn 区域简码
     * @param string $lang 语言 默认英文
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        $redis_key = md5(json_encode($where));
        if (redisHashExist('Manufacturer', $redis_key)) {
            return json_decode(redisHashGet('Manufacturer', $redis_key), true);
        }
        if (!empty($where)) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,url')
                    ->find();
            redisHashSet('Manufacturer', $redis_key, json_encode($row));
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            try {


                $flag = $this->where($where)
                        ->delete();

                return $flag;
            } catch (Exception $ex) {
                Log::write($ex->getMessage(), Log::ERR);
            }
        } else {


            return false;
        }
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function Exits($where) {

        return $this->_exist($where);
    }

    /**
     * Description of 增
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   生产商信息
     */
    public function create_data($create = [], $uid = 0) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $newbn = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $langs = ['en', 'zh', 'es', 'ru'];
            $this->startTrans();
            foreach ($langs as $lang) {
                $create['bn'] = $newbn;
                $flag = $this->_updateandcreate($create, $lang, $newbn, UID);
                if (!$flag) {
                    $this->rollback();

                    return false;
                }
            }
            $market_area_team_model = new MarketAreaTeamModel();
            $market_area_team_model->updateandcreate($create, $newbn, $uid);
            $this->commit();

            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  array $update
     * @return bool
     * @author jhw
     */
    public function update_data($update) {
        if (!isset($update['id']) || !$update['id']) {
            return false;
        } else {
            $where['id'] = $update['id'];
        }

        $this->startTrans();
        $create['updated_at'] = date('Y-m-d H:i:s');
        $create['updated_by'] = UID;
        $flag = $this->where($where)->save($update);

        $flag = $this->_updateandcreate($create);
        $this->commit();

        return $flag;
    }

    private function _updateandcreate($create, $id) {
        if (isset($create['supplier_ids']) && $create['supplier_ids'] && $id) {
            $supplier_ids = $create['supplier_ids'];
            $create['manufacturer_id'] = $id;
            $supplier_manufacturer_model = new SupplierManufacturerModel();
            if (is_array($supplier_ids)) {
                foreach ($supplier_ids as $supplier_id) {
                    $where['supplier_id'] = $supplier_id;
                    $where['manufacturer_id'] = $id;
                    $flag = $supplier_manufacturer_model->Exits($where);
                    if ($flag) {
                        $data = $create;
                        $data['id'] = $flag;
                        $supplier_manufacturer_model->update_data($data);
                    } else {
                        $supplier_manufacturer_model->create($data);
                    }
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

}
