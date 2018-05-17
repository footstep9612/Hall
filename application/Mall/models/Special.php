<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:47
 */
class SpecialModel extends Model {
    protected $tableName = 'special';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getInfo($id){
        try{
            $condition = [
                'id' => $id,
                'deleted_flag' => 'N'
            ];
            $result = $this->field('id,title,keyword,description,banner,url,country_bn')->where($condition)->find();
            return $result;
        }catch (Exception $e){
            return false;
        }
    }

    public function goods(){}

}