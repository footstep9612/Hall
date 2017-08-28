<?php

/**
 * name: InquiryItem
 * desc: 询单明细表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:54
 */
class InquiryitemModel extends PublicModel {

    protected $dbName = 'erui2_rfq'; //数据库名称
    protected $tableName = 'inquiry_item'; //数据表表名

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
            $list = $this->where($where)->order('created_at desc')->select();
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
            $results['message'] = '没有询单ID!';
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
            $id = $this->where($where)->delete();
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
        return date('Y-m-d h:i:s', time());
    }

}
