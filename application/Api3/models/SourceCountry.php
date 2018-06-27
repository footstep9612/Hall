<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BuyerSource
 * @author  zhongyg
 * @date    2018-4-22 10:28:07
 * @version V2.0
 * @desc
 */
class SourceCountryModel extends PublicModel {

    //put your code here
    protected $tableName = 'source_country';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.source_country';

    public function __construct() {
        parent::__construct();
    }

    /*
     * 判断资源是否存在
     */

    public function exist($source) {
        $where['source'] = $source;
        $where['deleted_flag'] = 'N';

        return $this->where($where)->getField('jump_url');
    }

}
