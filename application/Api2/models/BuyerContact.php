<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/9/11
 * Time: 15:45
 */
class BuyerContactModel extends PublicModel{

    //put your code here
    protected $dbName = 'erui2_buyer';
    protected $tableName = 'buyer_contact';

    public function __construct($str = ''){
        parent::__construct($str = '');
    }
}