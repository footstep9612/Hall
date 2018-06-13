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

    public function getInfo($input){
        if(!isset($input['id']) && !isset($input['name'])){
            jsonReturn('', MSG::MSG_FAILED,'请传递id或name');
        }

        try{
            $condition = [
                'status' => 'VALID',
                'deleted_at' => ['exp', 'is null']
            ];

            if(isset($input['id']) && is_numeric($input['id'])){
                $condition['id'] = intval($input['id']);
            }elseif(isset($input['name'])){
                $condition['name'] = trim($input['name']);
            }
            $result = $this->field('id,country_bn,name,lang,remark,type,settings')->where($condition)->find();
            return $result;
        }catch (Exception $e){
            return false;
        }
    }

    public function goods(){}

}