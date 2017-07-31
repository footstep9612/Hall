<?php
/**
 * name: InquiryAttach
 * desc: 询价单附件表
 * User: zhangyuliang
 * Date: 2017/6/17
 * Time: 10:14
 */
class InquiryAttachModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_attach'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     *
     */
    protected function getcondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        }
        if (!empty($condition['attach_group'])) {
            $where['attach_group'] = $condition['attach_group'];
        }
        if (!empty($condition['attach_type'])) {
            $where['attach_type'] = $condition['attach_type'];
        }
        if (!empty($condition['attach_name'])) {
            $where['attach_name'] = $condition['attach_name'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        return $this->where($where)->count('id');
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);
        //$page = isset($condition['page'])?$condition['page']:1;
        //$pagesize = isset($condition['countPerPage'])?$condition['countPerPage']:10;

        try {
            if (isset($page) && isset($pagesize)) {
                //$count = $this->getcount($condition);
                $list = $this->where($where)->select();
                    //->page($page, $pagesize)
                    //->select();
                if(isset($list)){
                    $results['code'] = '1';
                    $results['messaage'] = '成功！';
                    $results['data'] = $list;
                }else{
                    $results['code'] = '-101';
                    $results['messaage'] = '没有找到相关信息!';
                }
                return $results;
            } else {
                $list = $this->where($where)->select();
                if(isset($list)){
                    $results['code'] = '1';
                    $results['messaage'] = '成功！';
                    $results['data'] = $list;
                }else{
                    $results['code'] = '-101';
                    $results['messaage'] = '没有找到相关信息!';
                }
                return $results;
            }
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['messaage'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {
        if(isset($createcondition['serial_no'])){
            $data['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }
        if(isset($createcondition['attach_url'])){
            $data['attach_url'] = $createcondition['attach_url'];
        }else{
            return false;
        }

        $data = $this->create($createcondition);
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
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($createcondition = []) {
        if(isset($createcondition['id'])){
            $where['id'] = $createcondition['id'];
        }else{
            return false;
        }
        if(isset($createcondition['serial_no'])){
            $where['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }

        $data = $this->create($createcondition);
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
     * @param  int $id id
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($createcondition = []) {
        if(isset($createcondition['id'])){
            $where['id'] = $createcondition['id'];
        }else{
            return false;
        }
        if(isset($createcondition['serial_no'])){
            $where['serial_no'] = $createcondition['serial_no'];
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
}