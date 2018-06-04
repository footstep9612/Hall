<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/6/4
 * Time: 14:55
 */
class SupplierGoodsAttrModel extends PublicModel{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_goods_attr';

    public function __construct($str = ''){
        parent::__construct($str = '');
    }


    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getList($condition = []) {
        $where = $this->_getCondition($condition);

        $field = 'id,spu,sku,ex_goods_attrs,other_attrs,status,created_at';
        return $this->field($field)
            ->where($where)
            ->order('id desc')
            ->select();
    }

    /**
     *获取定制数量
     * @param array $condition
     * @author  klp
     */
    public function getCount($condition) {

        $where = $this->_getCondition($condition);

        return $this->where($where)->count();
    }


    /**
     * @desc 添加记录
     * @param array $condition
     */
    public function addRecord($condition = []) {


        $data = $this->create($condition);

        return $this->add($data);
    }

    /**
     * @desc 修改信息
     * @param array $where , $condition
     * @return bool
     */
    public function updateInfo($where = [], $condition = []) {

        $data = $this->create($condition);

        $res = $this->where($where)->save($data);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 软删除
     * @param array $where , $condition
     * @return bool
     */
    public function deleteInfo($where = [], $condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }
        $res = $this->where($where)->save(['deleted_flag'=>'Y']);
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
     * @desc 删除记录
     * @param array $condition
     * @return bool
     */
    public function delRecord($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }

        return $this->where($where)->delete();
    }

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getCondition($condition = []) {
        $where = [];
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where['name'] = $condition['name'];                  //产品名称
        }
        if (isset($condition['material_cat_no']) && !empty($condition['material_cat_no'])) {
            $where['material_cat_no'] = $condition['material_cat_no'];                  //物料分类编码
        }
        if (isset($condition['lang']) && !empty($condition['lang'])) {
            $where['lang'] = $condition['lang'];                  //语言
        }else {
            $where['lang'] = 'zh';
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where['status'] = strtoupper($condition['status']);                  //状态
        }
        if (!empty($condition['credit_date_start']) && !empty($condition['credit_date_end'])) {   //时间
            $where['credit_apply_date'] = array(
                array('egt', date('Y-m-d 0:0:0',strtotime($condition['credit_date_start']))),
                array('elt', date('Y-m-d 23:59:59',strtotime($condition['credit_date_end'])))
            );
        }
        if (isset($condition['deleted_flag']) && !empty($condition['deleted_flag'])) {
            $where['deleted_flag'] = strtoupper($condition['deleted_flag']);                  //是否删除状态
        }else {
            $where['deleted_flag'] = 'N';
        }
        return $where;
    }

}