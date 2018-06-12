<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:47
 */
class SpecialPositionModel extends PublicModel {
    protected $tableName = 'special_position';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    public function getList($condition,$condition_ext=[],$fields=''){
        $where =['deleted_at'=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where['special_id'] = intval($condition['special_id']);
        }
        if(isset($condition['type'])){
            $where['type'] = trim($condition['type']);
        }
        if(!empty($condition_ext)){
            foreach($condition_ext as $k => $v){
                $where[$k] = $v;
            }
        }
        try{
            $data = [];
            list($from, $size) = $this->_getPage($condition);
            $rel = $this->field($fields ? $fields : 'id,special_id,type,name,description,remark,sort_order,thumb,link')->where($where)->order('sort_order DESC')
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