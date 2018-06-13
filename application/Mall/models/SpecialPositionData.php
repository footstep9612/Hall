<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 10:02
 */
class SpecialPositionDataModel extends PublicModel {
    protected $tableName = 'special_position_data';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }


    /**
     * 获取推荐位商品
     * @param $condition
     * @return array|bool
     */
    public function getList($condition){
        $pModel = new ProductModel();
        $ptable = $pModel->getTableName();
        $thistable = $this->getTableName();
        $pModel = new ProductAttachModel();
        $patable = $pModel->getTableName();
        $where =["$thistable.deleted_at"=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where["$thistable.special_id"] = intval($condition['special_id']);
        }else{
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(isset($condition['position_id'])){
            $where["$thistable.position_id"] = intval($condition['position_id']);
        }else{
            jsonReturn('', MSG::MSG_FAILED,'请选择推荐位');
        }
        if(isset($condition['created_at_start']) && isset($condition['created_at_end'])){
            $where["$thistable.created_at"] = ['between', trim($condition['created_at_start']).','.trim($condition['created_at_end'])];
        }elseif(isset($condition['created_at_start'])){
            $where["$thistable.created_at"] = ['egt', trim($condition['created_at_start'])];
        }elseif(isset($condition['created_at_end'])){
            $where["$thistable.created_at"] = ['elt', trim($condition['created_at_end'])];
        }

        try{
            $data = [];
            list($from, $size) = $this->_getPage($condition);
            $rel = $this->field("$thistable.id,$thistable.lang,$thistable.special_id,$thistable.position_id,$thistable.sku,$thistable.sort_order,$thistable.created_at,$thistable.created_by,$thistable.spu,$ptable.name,$ptable.show_name,attach.attach_url")
                ->join("$ptable ON $thistable.spu=$ptable.spu AND $ptable.lang=$thistable.lang")
                ->join("(SELECT spu,MAX(sort_order),attach_url,default_flag,deleted_flag,status FROM $patable GROUP BY spu) as attach ON $thistable.spu=attach.spu AND attach.default_flag='Y' AND attach.deleted_flag='N' AND attach.status='VALID'","LEFT")
                ->where($where)
                ->limit($from, $size)
                ->select();
            if($rel){
                $data['data'] = $rel;
                $count = $this->getCount($where);
                $data['count'] = $count ? $count : 0;
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 获取记录数
     * @param $where
     * @return bool
     */
    public function getCount($where) {
        try{
            return $this->field('id')->where($where)
                ->count();
        }catch (Exception $e){
            return false;
        }
    }
}