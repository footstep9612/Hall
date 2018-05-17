<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/5/16
 * Time: 21:37
 */
class SpecialCategoryKeywordModel extends Model{
    protected $tableName = 'special_category_keyword';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

}