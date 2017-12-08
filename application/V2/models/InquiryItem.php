<?php

/**
 * name: InquiryItem
 * desc: 询单明细表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:54
 */
class InquiryitemModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_item'; //数据表表名
    public $isOil = [
        '石油专用管材',
        '钻修井设备',
        '固井酸化压裂设备',
        '采油集输设备',
        '石油专用工具',
        '石油专用仪器仪表',
        '油田化学材料'
    ]; // 油气
    public $noOil = [
        '通用机械设备',
        '劳动防护用品',
        '消防、医疗产品',
        '电力电工设备',
        '橡塑产品',
        '钢材',
        '包装物',
        '杂品'
    ]; // 非油气

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     *
     */
    protected function getCondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];    //明细id
        }
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];    //询单id
        }
        if (!empty($condition['sku'])) {
            $where['sku'] = $condition['sku'];  //商品SKU
        }
        if (!empty($condition['brand'])) {
            $where['brand'] = $condition['brand'];  //品牌
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag'])?$condition['deleted_flag']:'N';
        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);
        return $this->where($where)->count('id');
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        $where = $this->getCondition($condition);

        try {
            $list = $this->where($where)->order('id')->select();
            if($list){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
                $results['data'] = $list;
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 获取详情信息
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if($info){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
                $results['data'] = $info;
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }

    }

    /**
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        if (!empty($condition['inquiry_id'])) {
            $data['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }

        $data = $this->create($condition);
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
            if($id){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 批量添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addDataBatch($condition = []) {
        if (empty($condition['inquiry_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if (empty($condition['inquiry_rows'])) {
            $results['code'] = '-103';
            $results['message'] = '没有询单行数!';
            return $results;
        }

        $inquirydata = [];
        for($i = 0; $i < $condition['inquiry_rows']; $i++){
            $test['inquiry_id'] = $condition['inquiry_id'];
            $test['qty']        = '1';
            $test['created_by'] = $condition['created_by'];
            $test['created_at'] = $this->getTime();
            $inquirydata[] = $test;
        }

        try {
            $id = $this->addAll($inquirydata);
            if(isset($id)){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 更新数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        if(isset($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        //如果从报价过来，品牌是inquiry_brand
        if(!empty($condition['inquiry_brand'])){
            $condition['brand'] = $condition['inquiry_brand'];
        }

        $data = $this->create($condition);
        $data['status'] = !empty($createcondition['status']) ? $createcondition['status'] :'VALID';
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->where($where)->save($data);
            if(isset($id)){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '修改失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 删除数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if(isset($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            return false;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag'=>'Y']);
            if(isset($id)){
                $results['code'] = '1';
                $results['messaage'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['messaage'] = '删除失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s', time());
    }
    
    /**
     * @desc 获取关联询单SKU列表
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-12-07
     */
    public function getJoinList($condition = []) {
        if (!empty($condition['inquiry_id'])) {
            $where['a.deleted_flag'] = 'N';
            
            $where['a.inquiry_id'] = $condition['inquiry_id'];
            
            return $this->alias('a')
                                ->field('a.qty, a.category, b.quote_unit_price, b.total_quote_price')
                                ->join('erui_rfq.final_quote_item b ON a.id = b.inquiry_item_id AND b.deleted_flag = \'N\'', 'LEFT')
                                ->where($where)
                                ->order('a.id DESC')
                                ->select();
        } else {
            return false;
        }
    }

}
