<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class MarketAreaCountryModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_operation';
    protected $tableName = 'market_area_country';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {
        if (!empty($limit)) {
            return $this->field('market_area_bn,country_bn,id')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('market_area_bn,country_bn,id')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,time_zone,region')
                    ->find();
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
            return $this->where($where)
                            ->save(['status' => 'DELETED']);
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (isset($data['lang'])) {
            $arr['lang'] = $data['lang'];
        }
        if (isset($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (isset($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (isset($data['time_zone'])) {
            $arr['time_zone'] = $data['time_zone'];
        }
        if (isset($data['region'])) {
            $arr['region'] = $data['region'];
        }
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['lang'])) {
            $arr['lang'] = $create['lang'];
        }
        if (isset($create['bn'])) {
            $arr['bn'] = $create['bn'];
        }
        if (isset($create['name'])) {
            $arr['name'] = $create['name'];
        }
        if (isset($create['time_zone'])) {
            $arr['time_zone'] = $create['time_zone'];
        }
        if (isset($create['region'])) {
            $arr['region'] = $create['region'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

    /**
     * 获取列表
     * @param string $name 国家名称;
     * @param string $lang 语言
     * @return array
     * @author jhw
     */
    public function getbnbynameandlang($name, $lang = 'zh') {

        try {
            $data = ['country.name' => $name,
                'country.lang' => $lang,
                'country.status' => 'VALID',
            ];
            $row = $this->alias('mac')
                    ->join('erui2_dict.country country on country.bn=mac.country_bn')
                    ->field('mac.market_area_bn')
                    ->where($data)
                    ->find();
            if ($row) {
                return $row['market_area_bn'];
            } else {
                return 'Asia-Paific / Europe';
            }
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return 'Asia';
        }
    }

}
