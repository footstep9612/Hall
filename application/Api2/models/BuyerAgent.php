<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/30
 * Time: 11:43
 */
class BuyerAgentModel extends PublicModel{

    protected $tableName = 'buyer_agent';
    protected $dbName = 'erui2_buyer'; //数据库名称

    public function __construct($str = ''){

        parent::__construct();
    }
}