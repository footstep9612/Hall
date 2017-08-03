<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/3
 * Time: 11:39
 */
class MemberServiceModel extends PublicModel{
    protected $dbName = 'erui2_config';
    protected $tableName = 'member_service';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员等级查看
     * @author klp
     */
    public function listLevelInfo(){
        $fields = 'buyer_level, service_code, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at';
        try{
            $result = $this->field($fields)->where(['status'=>'VALID'])->select();
            $data = array();
            if($result) {
                foreach($result  as $item) {
                    $data[$item['buyer_level']][] = $item;
                }
            }
            return $data;
        } catch(Exception $e){
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
    public function create_data($createcondition = []) {
        $create = $this->checkParam($createcondition);
        if(isset($create['buyer_level'])){
            $data['buyer_level'] = $create['buyer_level'];
        }
        if(isset($create['service_code'])){
            $data['service_code'] = $create['service_code'];
        }
        if(isset($create['status'])){
            $data['status'] = $create['status'];
        }
        if(isset($create['created_by'])){
            $data['created_by'] = $create['created_by'];
        }
        $data['created_at'] = $this->getTime();
        try{
            $res = $this->add($data);
            if($res) {
                return $res;
            }
            return array();
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
    public function update_data($updatecondition = []) {
        $upcondition = $this->checkParam($updatecondition);
        if(isset($upcondition['service_code'])){
            $where['service_code'] = $upcondition['service_code'];
        }
        if(isset($upcondition['buyer_level'])){
            $data['buyer_level'] = $upcondition['buyer_level'];
        }
        if(isset($upcondition['status'])){
            $data['status'] = $upcondition['status'];
        }
        if(isset($upcondition['updated_by'])){
            $data['updated_by'] = $upcondition['updated_by'];
        }
        $data['updated_at'] = $this->getTime();
        try{
            $res = $this->where($where)->save($data);
            if($res) {
                return true;
            }
            return array();
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
    public function checkParam($data){
        if(empty($data)) {
            return false;
        }
        $results = array();
        if(empty($data['buyer_level'])) {
            $results['code'] = '-1';
            $results['message'] = '[buyer_level]缺失';
        }
        if(empty($data['service_code'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_code]缺失';
        }
        if($results){
            jsonReturn($results);
        }
        return $data;
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime(){
        return date('Y-m-d h:i:s',time());
    }

}