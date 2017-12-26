<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/26
 * Time: 16:49
 */
class InquiryContactModel extends PublicModel
{

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_contact'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取联系人信息.
     * @param Array $condition
     * @return Array
     * @author klp
     */
    public function getInfo($condition = []) {
        $where = $this->_getCondition($condition);

        return $info = $this->where($where)->find();
    }

    private function _getCondition($condition) {
        $where = [];
        $this->_getValue($where, $condition, 'inquiry_id');
        return $where;
    }

    /**
     * 添加数据.
     * @param Array $condition
     * @return Array
     * @author klp
     */
    public function addData($condition = []) {
        $data = $this->create($condition);
        if(isset($condition['inquiry_id'])){
            $data['inquiry_id'] = $condition['inquiry_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if(isset($condition['name'])){
            $data['name'] = trim($condition['name']);
        }
        if(isset($condition['company'])){
            $data['company'] = trim($condition['company']);
        }
        if(isset($condition['country_bn'])){
            $data['country_bn'] = trim($condition['country_bn']);
        }
        if(isset($condition['city'])){
            $data['city'] = trim($condition['city']);
        }
        if(isset($condition['phone'])){
            $data['phone'] = trim($condition['phone']);
            if(isset($condition['tel_code'])){
                $data['phone'] = trim($condition['tel_code']).' '.$condition['phone'];
            }
        }
        if(isset($condition['email'])){
            $data['email'] = trim($condition['email']);
        }
        if(isset($condition['addr'])){
            $data['addr'] = trim($condition['addr']);
        }
        if(isset($condition['remarks'])){
            $data['remarks'] = trim($condition['remarks']);
        }
        if(isset($condition['created_by'])){
            $data['created_by'] = trim($condition['created_by']);
        }
        $data['created_at'] = $this->getTime();

        try {
            $data = $this->create($data);
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
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s',time());
    }

}