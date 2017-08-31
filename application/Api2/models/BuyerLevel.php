<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/31
 * Time: 9:25
 */
class BuyerLevelModel extends PublicModel
{
    protected $dbName = 'erui2_config';
    protected $tableName = 'buyer_level';

    public function __construct(){
        parent::__construct();
    }

}