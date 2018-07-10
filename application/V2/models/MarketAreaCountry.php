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
    protected $dbName = 'erui_operation';
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

    /*
     * 根据国家简称获取营销区域
     * @param array $bns // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getAreasBybns($country_bns, $lang = 'zh') {

        $lang = $this->escapeString($lang);
        $market_area_model = new MarketAreaModel();
        $market_area_table = $market_area_model->getTableName();
        try {
            $where = [];

            if (is_string($country_bns)) {
                $where['country_bn'] = $country_bns;
            } elseif (is_array($country_bns)) {
                $where['country_bn'] = ['in', $country_bns];
            } else {
                return false;
            }


            $areas = $this->where($where)
                            ->join($where)
                            ->field('country_bn,market_area_bn,(select `name` from ' . $market_area_table
                                    . ' where bn=market_area_bn and lang=\'' . $lang . '\') as market_area_name')->select();
            $area_names = [];
            foreach ($areas as $area) {
                $area_names[$area['country_bn']] = $area;
            }
            return $area_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据营销区域获取国家简称
     * @param array $Areabn // 区域简称
     * @return array
     * @author  zhanguliang
     *  @date    2018-3-3 13:39:16
     */

    public function getCountryBn($Areabn, $lang = 'zh') {

        try {
            $where = [];

            if (is_string($Areabn)) {
                $where['market_area_bn'] = $Areabn;
            } elseif (is_array($Areabn)) {
                $where['market_area_bn'] = ['in', $Areabn];
            } else {
                return false;
            }


            $country_bn = $this->field('country_bn')->where($where)->select();

            foreach ($country_bn as $val) {
                $countrybns[] = $val['country_bn'];
            }

            return $countrybns;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setAreaBn(&$arr) {
        if ($arr) {

            $country_bns = [];
            foreach ($arr as $key => $val) {

                if (isset($val['country_bn']) && $val['country_bn']) {
                    $country_bns[] = trim($val['country_bn']);
                }
            }
            if ($country_bns) {
                $area_bns = $this->field('country_bn,market_area_bn')
                                ->where(['country_bn' => ['in', $country_bns]])->select();


                $countrytoarea_bns = [];
                foreach ($area_bns as $item) {
                    $countrytoarea_bns[$item['country_bn']] = $item['market_area_bn'];
                }

                foreach ($arr as $key => $val) {
                    if (trim($val['country_bn']) && isset($countrytoarea_bns[trim($val['country_bn'])])) {
                        $val['area_bn'] = $countrytoarea_bns[trim($val['country_bn'])];
                    } else {
                        $val['area_bn'] = '';
                    }

                    $arr[$key] = $val;
                }
            }
        }
    }

}
