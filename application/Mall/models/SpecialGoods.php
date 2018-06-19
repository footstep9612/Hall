<?php
/**
 * 专题商品
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 10:01
 */
class SpecialGoodsModel extends PublicModel {
    protected $tableName = 'special_goods';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 专题商品
     * @param $input
     * @return array|bool
     */
    public function getList($input){
        try{
            $data = [];
            $thisTable = $this->getTableName();

            $pModel = new ProductModel();
            $pTable = $pModel->getTableName();

            $paModel = new ProductAttachModel();
            $paTable = $paModel->getTableName();
            $condition = [
                "$thisTable.special_id" => $input['special_id'],
                "$thisTable.deleted_at"=>['exp', 'is null']
            ];
            if(isset($input['cat_id'])){
                $condition[$thisTable.'.cat_id'] = trim($input['cat_id']);
            }
            if(isset($input['keyword_id'])){
                $condition[$thisTable.'.keyword_id'] = trim($input['keyword_id']);
            }
            list($from, $size) = $this->_getPage($input);
            $result = $this->field("$thisTable.spu,$thisTable.lang,$thisTable.cat_id,$thisTable.keyword_id,$pTable.name,$pTable.show_name,$pTable.sku_count,$pTable.brand,$pTable.exe_standard,$pTable.warranty,$pTable.tech_paras,attach.attach_url")
                ->join($pTable." ON $thisTable.spu=$pTable.spu AND $thisTable.lang=$pTable.lang AND $pTable.deleted_flag='N' AND $pTable.status='VALID'")
                ->join("(SELECT spu,MAX(sort_order),attach_url,default_flag,deleted_flag,status FROM $paTable GROUP BY spu) as attach ON $thisTable.spu=attach.spu AND attach.default_flag='Y' AND attach.deleted_flag='N' AND attach.status='VALID'","LEFT")
                ->where($condition)
                ->limit($from,$size)
                ->select();
            if($result){
                $data['data'] = $result;
                $count = $this->getCount($condition);
                $data['count'] = $count ? $count : 0;
                $data['current_no'] = isset($condition['current_no']) ? $condition['current_no'] :1;
                $data['pagesize'] = $size;
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