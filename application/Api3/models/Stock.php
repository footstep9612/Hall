<?php
/**
 * 现货
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 23:02
 */
class StockModel extends PublicModel
{
    protected $tableName = 'stock';
    protected $dbName = 'erui_stock';

    public function __construct()
    {
        parent::__construct();
    }
}