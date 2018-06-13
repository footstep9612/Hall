<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/6/4
 * Time: 14:54
 */
class SupplierGoodsModel extends PublicModel{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_goods';

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
        //$condition['current_no'] = $condition['currentPage'];

        //list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,lang,spu,sku,name,show_name,model,description,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,price,price_cur_bn,status,source,created_at';
        return $this->field($field)
            ->where($where)
            //->limit($start_no, $pagesize)
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
     * 获取商品详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function getDetail($condition = []) {
        $where = $this->_getWhere($condition);
        $supplier_goods_attr_model = new SupplierGoodsAttrModel();
        $attr_table = $supplier_goods_attr_model->getTableName();
        return $this->field('g.*, a.ex_goods_attrs, a.other_attrs')
                     ->alias('g')
                     ->join($attr_table.' as a ON a.sku = g.sku AND a.deleted_flag = \'N\'', 'left')
                     ->where($where)
                     ->select();
    }

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return mix
     * @author klp
     */
    protected function _getWhere($condition = []) {
        $where = [];
        if (isset($condition['spu']) && !empty($condition['spu'])) {
            if(is_array($condition['spu'])){
                $where['g.spu'] = ['in',$condition['spu']];
            }else{
                $where['g.spu'] = $condition['spu'];
            }
        }
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            if(is_array($condition['sku'])){
                $where['g.sku'] = ['in',$condition['sku']];
            }else{
                $where['g.sku'] = $condition['sku'];
            }
        }
        if (isset($condition['supplier_id']) && !empty($condition['supplier_id'])) {
            $where['g.supplier_id'] = $condition['supplier_id'];                  //瑞商id
        }
        if (isset($condition['lang']) && !empty($condition['lang'])) {
            $where['g.lang'] = $condition['lang'];                  //语言
        }else {
            $where['g.lang'] = 'zh';
        }
        if (isset($condition['id']) && !empty($condition['id'])) {
            $where['g.id'] = $condition['id'];                  //id
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where['g.status'] = strtoupper($condition['status']);                  //状态
        }
        if (isset($condition['deleted_flag']) && !empty($condition['deleted_flag'])) {
            $where['g.deleted_flag'] = strtoupper($condition['deleted_flag']);                  //是否删除状态
        }else {
            $where['g.deleted_flag'] = 'N';
        }
        return $where;
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
    public function deleteInfo($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        }
        if (!empty($condition['spu'])) {
            $where['spu'] = ['in', explode(',', $condition['spu'])];
        }
        if (!empty($condition['sku'])) {
            $where['sku'] = ['in', explode(',', $condition['sku'])];
        }
        if(empty($where)){
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
        if (isset($condition['spu']) && !empty($condition['spu'])) {
            if(is_array($condition['spu'])){
                $where['spu'] = ['in',$condition['spu']];
            }else{
                $where['spu'] = $condition['spu'];
            }
        }
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            if(is_array($condition['sku'])){
                $where['sku'] = ['in',$condition['sku']];
            }else{
                $where['sku'] = $condition['sku'];
            }
        }
        if (isset($condition['supplier_id']) && !empty($condition['supplier_id'])) {
            $where['supplier_id'] = $condition['supplier_id'];                  //瑞商id
        }
        if (isset($condition['lang']) && !empty($condition['lang'])) {
            $where['lang'] = $condition['lang'];                  //语言
        }else {
            $where['lang'] = 'zh';
        }
        if (isset($condition['id']) && !empty($condition['id'])) {
            $where['id'] = $condition['id'];                  //id
        }
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where['status'] = strtoupper($condition['status']);                  //状态
        }
        if (isset($condition['deleted_flag']) && !empty($condition['deleted_flag'])) {
            $where['deleted_flag'] = strtoupper($condition['deleted_flag']);                  //是否删除状态
        }else {
            $where['deleted_flag'] = 'N';
        }
        return $where;
    }

    /**
     * 生成sku编码 - NEW
     * @time 2017-09-26(经史总,平总确认新规则)
     * 规则:SPU的编码规则为：6位物料分类编码 + 00 + 4位产品编码 + 0000
    SKU的编码规则为: 产品的12位编码 + 4位商品编码
     */
    public function setRealSku($spu, $sku = '') {
        if (empty($sku)) {
            if (empty($spu)) {
                return false;
            }
            $temp_num = substr($spu, 0, 12);
            $data = $this->getSkus($spu);
            if ($data && substr($data, 0, 12) == $temp_num) {
                $num = substr($data, 12, 4);
                $num++;
                $num = str_pad($num, 4, "0", STR_PAD_LEFT);
            } else {
                $num = str_pad('1', 4, "0", STR_PAD_LEFT);
            }
            $real_num = $temp_num . $num;
            return $this->setRealSku($spu, $real_num);
        } else {
            $lockFile = MYPATH . '/public/tmp/' . $sku . '.lock';
            if (file_exists($lockFile)) {
                $spu = substr($sku, 0, 12);
                $num = substr($sku, 12, 4);
                $num++;
                $sku = $spu . str_pad($num, 4, '0', STR_PAD_LEFT);
                return $this->setRealSku($spu, $sku);
            } else {
                //目录
                $dirName = MYPATH . '/public/tmp';
                if (!is_dir($dirName)) {
                    if (!mkdir($dirName, 0777, true)) {
                        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                    }
                }

                //上锁
                $handle = fopen($lockFile, "w");
                if (!$handle) {
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Lock Error: Lock file [' . MYPATH . '/public/tmp/' . $sku . '.lock' . '] create faild.', Log::ERR);
                } else {
                    fclose($handle);
                    return $sku;
                }
                return false;
            }
        }
    }

    /**
     * 获取sku 获取列表
     * @author
     */
    public function getSkus($spu) {
        $result = $this->field('sku')->where(array('spu' => $spu))->order('sku DESC')->find();
        return $result ? $result['sku'] : false;
    }

}