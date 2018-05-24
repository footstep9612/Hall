<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 10:01
 */
class SpecialGoodsModel extends Model {
    protected $tableName = 'special_goods';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getList($input){

        try{
            $thisTable = $this->getTableName();
            $sckModel = new SpecialCategoryKeywordModel();
            $sckTable = $sckModel->getTableName();
            $condition = [
                "$thisTable.special_id" => $input['special_id'],
                "$thisTable.deleted_at"=>['exp', 'is null'],
                "$sckTable.deleted_at"=>['exp','is null']
            ];
            if(isset($input['category_id'])){
                $condition[$thisTable.'.category_id'] = trim($input['category_id']);
            }
            if(isset($input['keyword'])){
                $condition[$sckTable.'.keyword'] = trim($input['keyword']);
            }

            $result = $this->field("$thisTable.sku,$thisTable.category_id,$sckTable.keyword")
                ->join($sckTable." ON $thisTable.category_id=$sckTable.category_id")->where($condition)->select();
            if($result){

            }
        }catch (Exception $e){
            return false;
        }






    }

}