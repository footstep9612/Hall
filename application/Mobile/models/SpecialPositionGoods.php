<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 10:02
 */
class SpecialPositionGoodsModel extends Model {
    protected $tableName = 'special_position_goods';
    protected $dbName = 'erui_stock'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取模块商品
     * @param $position_id
     */
    public function getList($special_id,$position_id,$size=0){
        try{
            if(!$size){
                $spModel = new SpecialPositionModel();
                $positionInfo = $spModel->field('id,name,maxnum')->where(['id'=>$position_id])->find();
                $size = $positionInfo['maxnum'] ? $positionInfo['maxnum'] : 20;
            }
            $sgModel = new SpecialGoodsModel();
            $sgTable = $sgModel->getTableName();

            $thisTable = $this->getTableName();
            $fields = "$sgTable.name,$sgTable.thumb,$sgTable.spu,$sgTable.sku";
            $condition = [
                "$thisTable.special_id" => $special_id,
                "$thisTable.position_id" => $position_id
            ];
            $result = $sgModel->field($fields)->join("$thisTable ON $sgTable.id = $thisTable.sg_id")->where($condition)->order('listorder DESC')->limit($size)->select();
            return $result;
        }catch (Exception $e){
            return false;
        }
    }
}