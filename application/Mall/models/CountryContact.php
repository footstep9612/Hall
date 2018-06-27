<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/18
 * Time: 14:11
 */
class CountryContactModel extends PublicModel{
    protected $tableName = 'country_contact';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getInfo($condition=[]){
        if(empty($condition) || !isset($condition['country_bn'])){
            return false;
        }
        $where = [
            'country_bn' => ucfirst($condition['country_bn']),
            'deleted_at' => ['exp', 'is null'],
        ];
        $result = $this->field('contact,tel,email,department,position')
            ->where($where)
            ->order('listorder DESC')
            ->select();
        return $result ? $result : false;
    }

}