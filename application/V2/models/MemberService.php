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
    public function levelInfo(){
        $where = array(
            'status'=>'VALID',
            'deleted_flag'=>'N'
        );
        $fields = 'id, buyer_level, service_cat_id, service_term_id, service_item_id, status, created_at, updated_by, updated_at, checked_by, checked_at, deleted_flag';
        try{
            $result = $this->field($fields)->where($where)->select();
            if($result) {
                return $result;
            }
            return array();
        } catch(Exception $e){
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
    public function editInfo($data = [],$userInfo) {
        if($data || is_array($data)){
            return false;
        }
        $this->startTrans();
        try{
            foreach($data as $item){
                $res = $this->field('id')->where(['id'=>$item['id']])->find();
                if($res){
                    $result = $this->update_data($item,$userInfo);
                    if(1 != $result['code']){
                        return false;
                    }
                } else {
                    $result = $this->create_data($item,$userInfo);
                    if(1 != $result['code']){
                        return false;
                    }
                }
            }
            if($result){
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
    public function create_data($createcondition = [],$userInfo) {
        $create = $this->checkParam($createcondition);
        if(isset($create['buyer_level'])){
            $data['buyer_level'] = $create['buyer_level'];
        }
        if(isset($create['service_cat_id'])){
            $data['service_cat_id'] = $create['service_cat_id'];
        }
        if(isset($create['service_term_id'])){
            $data['service_term_id'] = $create['service_term_id'];
        }
        if(isset($create['service_item_id'])){
            $data['service_item_id'] = $create['service_item_id'];
        }
        if(isset($create['status'])){
            $data['status'] = $create['status'];
        }
        if(isset($create['created_by'])){
            $data['created_by'] = $userInfo['id'];
        }
        $data['created_at'] = $this->getTime();
        try{
            $res = $this->add($data);
            if($res){
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
    public function update_data($updatecondition = [],$userInfo) {
        $upcondition = $this->checkParam($updatecondition);
        if(isset($create['id'])){
            $where = array('id'=>$create['id']);
        }
        if(isset($create['buyer_level'])){
            $data['buyer_level'] = $create['buyer_level'];
        }
        if(isset($create['service_cat_id'])){
            $data['service_cat_id'] = $create['service_cat_id'];
        }
        if(isset($create['service_term_id'])){
            $data['service_term_id'] = $create['service_term_id'];
        }
        if(isset($create['service_item_id'])){
            $data['service_item_id'] = $create['service_item_id'];
        }
        if(isset($create['status'])){
            $data['status'] = $create['status'];
        }
        if(isset($upcondition['updated_by'])){
            $data['updated_by'] = $userInfo['id'];
        }
        $data['updated_at'] = $this->getTime();
        try{
            $res = $this->where($where)->save($data);
            if($res){
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
    public function checkParam($data){
        if(empty($data)) {
            return false;
        }
        $results = array();
        if(empty($data['buyer_level'])) {
            $results['code'] = '-1';
            $results['message'] = '[buyer_level]缺失';
        }
        if(empty($data['service_cat_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_cat_id]缺失';
        }
        if(empty($data['service_term_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_term_id]缺失';
        }
        if(empty($data['service_item_id'])) {
            $results['code'] = '-1';
            $results['message'] = '[service_item_id]缺失';
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