<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 11:39
 */
class MemberServiceModel extends PublicModel {

    protected $dbName = 'erui2_config';
    protected $tableName = 'member_service';

    public function __construct() {
        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID';          //有效
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除

    /**
     * 会员等级查看
     * @author klp
     */
<<<<<<< HEAD
    public function levelInfo($limit){
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try{
            if(!empty($limit)){
            $result = $this->field($fields)->where($where)->limit($limit['page'] . ',' . $limit['num'])->order('buyer_level')->group('buyer_level')->select();
            } else{
                $result = $this->field($fields)->where($where)->order('buyer_level')->group('buyer_level')->select();
=======

    public function levelInfo($limit, $where) {
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try {
            if (!empty($limit)) {
                $result = $this->field($fields)->where($where)->limit($limit['page'] . ',' . $limit['num'])->order('buyer_level')->select();
            } else {
                $result = $this->field($fields)->where($where)->order('buyer_level')->select();
>>>>>>> Branch_dev
            }
            $data = array();
            if ($result) {
                $employee = new EmployeeModel();
<<<<<<< HEAD
                foreach($result as $item) {
=======
                foreach ($result as $item) {
                    $data[$item['service_cat_id']]['created_at'] = $item['created_at'];
>>>>>>> Branch_dev
                    $createder = $employee->getInfoByCondition(array('id' => $item['created_by']), 'id,name,name_en');
                    if ($createder && isset($createder[0])) {
                        $item['created_by'] = $createder[0];
                    }
<<<<<<< HEAD
                    $data[$item['buyer_level']][] = $item;
=======
                    $data[$item['service_cat_id']]['buyer_level'] = $item['buyer_level'];

                    $data[$item['service_cat_id']]['category']['service_cat_id'] = $item['service_cat_id'];

                    $data[$item['service_cat_id']]['category']['term'][$item['service_term_id']]['service_term_id'] = $item['service_term_id'];

                    $data[$item['service_cat_id']]['category']['term'][$item['service_term_id']]['item'][$item['service_item_id']]['service_item_id'] = $item['service_item_id'];

                    $data[$item['service_cat_id']]['category']['term'][$item['service_term_id']]['item'][$item['service_item_id']]['id'] = $item['id'];
                }
                foreach ($data as $key => $value) {
                    $arr[] = $value;
>>>>>>> Branch_dev
                }
                return $data;
            }
            return array();
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return array();
        }
    }
    /**
     * 会员等级匹配服务
     * @author klp
     */
    public function levelService($buyer_level){
        $where['buyer_level'] = $buyer_level;
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $result = $this->field('service_cat_id')->where($where)->group('service_cat_id')->select();
        foreach($result as $key=>$val){
            $where1 = ['service_cat_id' =>$val['service_cat_id'],'status'=>'VALID','deleted_flag'=>'N'];
            $rs =  $this->field('service_term_id')->where($where1)->group('service_term_id')->select();
            foreach($rs as $key1=>$val1){
                $where2 = ['service_term_id'=>$val1['service_term_id'],'status'=>'VALID','deleted_flag'=>'N'];
                $rs1 =  $this->field('service_item_id')->where($where2)->group('service_item_id')->select();
                $rs[$key1]['item'] = $rs1;
            }
            $result[$key]['term'] = $rs;
        }
        return $result? $result:array();
    }

    /**
     * 新增/编辑数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author klp
     */
    public function editInfo($data = [], $userInfo) {
        if (!$data || !is_array($data)) {
            return false;
        }
        if (empty($data['buyer_level'])) {
            jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
        }
        try {
            foreach ($data['levels'] as $items) {
                //处理条款id
                foreach ($items['term'] as $term) {
                    //处理条款内容id
                    foreach ($term['item'] as $im) {
                        $save = [
                            'service_cat_id' => $items['category']['service_cat_id'],
                            'service_term_id' => $term['service_term_id'],
                            'service_item_id' => $im['service_item_id'],
                            'buyer_level' => $data['buyer_level']
                        ];
                        if (isset($im['id']) && !empty($im['id'])) {
                            $res = $this->field('id')->where(['id' => $items['id']])->find();
                            if ($res) {
                                $save['id'] = $im['id'];
                                $result = $this->update_data($save, $userInfo);
                                if (1 != $result['code']) {
                                    return false;
                                }
                            } else {
                                $result = $this->create_data($save, $userInfo);
                                if (1 != $result['code']) {
                                    return false;
                                }
                            }
                        } else {
                            $result = $this->create_data($save, $userInfo);
                            if (1 != $result['code']) {
                                return false;
                            }
                        }
                    }
                }
            }
            if ($result) {
                $results['code'] = '1';
                $results['message'] = '成功!';
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 删除数据
     * @author klp
     * @param $id
     * @return bool
     */
    public function delData($id) {
        if (empty($id)) {
            return false;
        }
        try {
            $where = ['id' => $id];
            $res = $this->where($where)->save(['status' => self::STATUS_DELETED]);
            if (!$res) {
                return false;
            }
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author klp
     */
    public function create_data($createcondition, $userInfo) {
        $create = $this->checkParam($createcondition);
        if (!empty($create['buyer_level'])) {
            $data['buyer_level'] = $create['buyer_level'];
        }
        if (!empty($create['service_cat_id'])) {
            $data['service_cat_id'] = $create['service_cat_id'];
        }
        if (!empty($create['service_term_id'])) {
            $data['service_term_id'] = $create['service_term_id'];
        }
        if (!empty($create['service_item_id'])) {
            $data['service_item_id'] = $create['service_item_id'];
        }
        if (!empty($create['status'])) {
            $data['status'] = $create['status'];
        }
        if (!empty($create['created_by'])) {
            $data['created_by'] = $userInfo['id'];
        }
        $data['created_at'] = $this->getTime();
        try {
            $res = $this->add($data);
            if ($res) {
                $results['code'] = '1';
                $results['message'] = '成功!';
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author klp
     */
    public function delete_data() {

    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author klp
     */
    public function update_data($updatecondition = [], $userInfo) {
        $create = $this->checkParam($updatecondition);
        if (!empty($create['id'])) {
            $where = array('id' => $create['id']);
        }
        if (!empty($create['buyer_level'])) {
            $data['buyer_level'] = $create['buyer_level'];
        }
        if (!empty($create['service_cat_id'])) {
            $data['service_cat_id'] = $create['service_cat_id'];
        }
        if (!empty($create['service_term_id'])) {
            $data['service_term_id'] = $create['service_term_id'];
        }
        if (!empty($create['service_item_id'])) {
            $data['service_item_id'] = $create['service_item_id'];
        }
        if (!empty($create['status'])) {
            $data['status'] = $create['status'];
        }
        if (!empty($upcondition['updated_by'])) {
            $data['updated_by'] = $userInfo['id'];
        }
        $data['updated_at'] = $this->getTime();
        try {
            $res = $this->where($where)->save($data);
            if ($res) {
                $results['code'] = '1';
                $results['message'] = '成功!';
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 参数校验,目前只测必须项
     * @author klp
     * @return array
     */
    public function checkParam($data) {
        if (empty($data)) {
            return false;
        }
        $results = array();
        if (empty($data['buyer_level'])) {
            $results['code'] = '-1';
            $results['message'] = '[buyer_level]缺失';
        }
        if (empty($data['service_cat_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_cat_id]缺失';
        }
        if (empty($data['service_term_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_term_id]缺失';
        }
        if (empty($data['service_item_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_item_id]缺失';
        }
        if ($results) {
            jsonReturn($results);
        }
        return $data;
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s', time());
    }

}
