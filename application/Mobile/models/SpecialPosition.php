<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:47
 */
class SpecialPositionModel extends Model {
    protected $tableName = 'special_position';
    protected $dbName = 'erui_stock'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

}