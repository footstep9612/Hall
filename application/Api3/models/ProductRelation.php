<?php
/**
 * SPU关联
 * Created by PhpStorm.
 * @author  link
 * @date    2017-12-8 9:12:49
 * @version V2.0
 * @desc
 */
class ProductRelationModel extends PublicModel {
    protected $tableName = 'product_relation';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }
}
