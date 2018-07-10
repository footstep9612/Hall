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
class Common_MarketAreaCountryModel extends PublicModel {

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
