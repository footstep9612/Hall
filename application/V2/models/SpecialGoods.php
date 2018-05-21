<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SpecialGoods
 * @author  zhongyg
 * @date    2018-05-17 13:38:48
 * @version V2.0
 * @desc
 */
class SpecialGoodsModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_goods';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 根据PSU 获取专题 关键字信息
     */

    public function getSpecialsBySpu($spus, $lang = 'en') {


        if (empty($lang)) {
            return [];
        }
        if (empty($spus)) {
            return [];
        }
        $special_table = (new SpecialModel())->getTableName();

        $special_keyword_table = (new SpecialKeywordModel())->getTableName();
        $where = ['s.lang' => $lang, 'sg.spu' => ['in', $spus], 's.deleted_at is null',];

        $list = $this->alias('sg')
                ->field('sg.spu,sg.special_id,sg.keyword_id,s.name as special_name,sk.keyword,s.country_bn')
                ->join($special_table . ' s on s.id=sg.special_id', 'left')
                ->join($special_keyword_table . ' sk on sk.id=sg.keyword_id', 'left')
                ->where($where)
                ->group('sg.spu,sg.special_id,sg.keyword_id')
                ->select();
        $ret = [];

        foreach ($list as $special_goods) {
            $spu = $special_goods['spu'];
            unset($special_goods['spu']);
            $ret[$spu][] = $special_goods;
        }

        return $ret;
    }

}
