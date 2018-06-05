<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/30
 * Time: 16:30
 */
class SupplierProductModel extends PublicModel{

    protected $dbName = 'erui_supplier';
    protected $tableName = 'supplier_product';

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
        $condition['current_no'] = $condition['currentPage'];

        list($start_no, $pagesize) = $this->_getPage($condition);
        $field = 'id,lang,name,show_name,brand,keywords,tech_paras,description,warranty,source,status,created_at';
        return $this->field($field)
                     ->where($where)
                     ->limit($start_no, $pagesize)
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

    /**
     * 生成ｓｐｕ编码
     * SPU的编码规则为：6位物料分类编码 + 00 + 4位产品编码 + 0000
     * @return string
     */
    public function createSpu($material_cat_no = '', $spu = '') {
        if (empty($material_cat_no)) {
            return false;
        }
        $supplier_product_model = new SupplierProductModel();
        if (!empty($spu)) {
            $condition = array('spu' => $spu);
            $result2 = $supplier_product_model->field('spu')->where($condition)->find();
            $lockFile = MYPATH . '/public/tmp/' . $spu . '.lock';
            if ($result2 || file_exists($lockFile)) {
                $code = substr($spu, (strlen($material_cat_no) + 2), 4);
                $code = intval($code) + 1;
                $spu = $material_cat_no . '00' . str_pad($code, 4, '0', STR_PAD_LEFT) . '0000';
                return $this->createSpu($material_cat_no, $spu);
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
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Lock Error: Lock file [' . MYPATH . '/public/tmp/' . $spu . '.lock' . '] create faild.', Log::ERR);
                } else {
                    fclose($handle);
                    return $spu;
                }
                return false;
            }
        } else {
            $condition = array(
                'material_cat_no' => $material_cat_no
            );
            $result = $supplier_product_model->field('spu')->where($condition)->order('spu DESC')->find();
            if ($result) {
                $code = substr($result['spu'], (strlen($material_cat_no) + 2), 4);
                $code = intval($code) + 1;
            } else {
                $code = 1;
            }
            $spu = $material_cat_no . '00' . str_pad($code, 4, '0', STR_PAD_LEFT) . '0000';
            return $this->createSpu($material_cat_no, $spu);
        }
    }

}