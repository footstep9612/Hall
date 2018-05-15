<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolutionGoods
 * @author  zhongyg
 * @date    2018-5-15 9:38:08
 * @version V2.0
 * @desc
 */
class SolutionGoodsModel extends PublicModel {

    //put your code here
    protected $tableName = 'sol_content_goods';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    public function Info($id) {
        $where = ['id' => intval($id)];
        return $this->field('id,lang,spu,sku,name,show_name,goods_url')
                        ->where($where)
                        ->find();
    }

}
