<?php
/**
 * name: InquiryItemAttach
 * desc: 询单明细附件表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 15:36
 */
class InquiryItemAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_item_attach'; //数据表表名

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
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];
        }
        if (!empty($condition['inquiry_item_id'])) {
            $where['inquiry_item_id'] = $condition['inquiry_item_id'];
        }
        if (!empty($condition['attach_url'])) {
            $where['attach_url'] = $condition['attach_url'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getcondition($condition);
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
            if(isset($list)){
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
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        $data = $this->create($condition);
        if (isset($condition['inquiry_id'])) {
            $data['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if (isset($condition['inquiry_item_id'])) {
            $data['inquiry_item_id'] = $condition['inquiry_item_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有询单明细ID!';
            return $results;
        }
        if (isset($condition['attach_url'])) {
            $data['attach_url'] = $condition['attach_url'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有附件URL!';
            return $results;
        }
        $data['created_at'] = $this->getTime();

        try {
            $id = $this->add($data);
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
        $data = $this->create($condition);
        if (isset($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }


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
        if (isset($condition['id'])) {
            $where['id'] = array('in',explode(',',$condition['id']));
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
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
}