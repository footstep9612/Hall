<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/31
 * Time: 9:25
 */
class BuyerLevelModel extends PublicModel
{

    protected $dbName = 'erui_config';
    protected $tableName = 'buyer_level';

    const STATUS_VALID = 'VALID';
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    public function __construct(){
        parent::__construct();
    }


}