<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 10:01
 */
class SpecialGoodsModel extends Model {
    protected $tableName = 'special_goods';
    protected $dbName = 'erui_stock'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

}