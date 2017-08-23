<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/23
 * Time: 19:31
 */
class BuyerLevelModel extends PublicModel{
    protected $dbName = 'erui2_config';
    protected $tableName = 'buyer_level';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 会员等级查看
     * @author klp
     */
    public function levelInfo(){
        $where['status'] = 'VALID';
        $where['deleted_flag'] = 'N';
        $fields = 'id, buyer_level, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try{
            $result = $this->field($fields)->where($where)->order('id')->group('buyer_level')->select();

            $arr = $data = $level = array();
            if ($result) {
                $employee = new EmployeeModel();
                foreach($result as $item) {
                    $createder = $employee->getInfoByCondition(array('id' => $item['created_by']), 'id,name,name_en');
                    if ($createder && isset($createder[0])) {
                        $item['created_by'] = $createder[0];
                    }
                    $item['buyer_level'] = json_decode($item['buyer_level'],true);
                    foreach($item['buyer_level'] as $val) {
                        $level[$val['lang']]['buyer_level'] = $val['name'];
                        unset($item['buyer_level']);
                        $data[$val['lang']] = $item;
                        $data[$val['lang']]['buyer_level'] = $level[$val['lang']]['buyer_level'];
                    }
                    $arr[]=$data;
                }jsonReturn($arr);
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
     * 新增/编辑数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author klp
     */
    public function editLevel($data = [], $userInfo){
        $checkout = $this->checkParam($data);
        try {
            $result = $this->field('id')->where(['id'=>$checkout['id']])->find();
            if($result){
                $checkout['updated_by'] = $userInfo['id'];
                $checkout['updated_at'] = $this->getTime();
                $res = $this->where(['id'=>$checkout['id']])->save($checkout);
                if (!$res) {
                    $results['code'] = '-1';
                    $results['message'] = '失败!';
                }
            } else{
                $checkout['created_by'] = $userInfo['id'];
                $checkout['created_at'] = $this->getTime();
                $res = $this->add($checkout);
                if (!$res) {
                    $results['code'] = '-1';
                    $results['message'] = '失败!';
                }
            }

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
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author klp
     */
    public function checkParam($create) {
        if (!empty($create['buyer_level'])) {
            $data['buyer_level'] = json_encode($create['buyer_level']);
        }
        if (!empty($data['buyer_level_id'])) {
            $data['id'] = $create['buyer_level_id'];
        }
        if (!empty($create['status'])) {
            $data['status'] = strtoupper($create['status']);
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