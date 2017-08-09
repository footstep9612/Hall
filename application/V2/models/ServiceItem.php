<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/5
 * Time: 10:59
 */
class ServiceItemModel extends PublicModel{
   //会员服务条款(三级)
    protected $dbName = 'erui2_config';
    protected $tableName = 'service_item';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员服务条款(三级)
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        if(isset($data['service_cat_id']) && !empty($data['service_cat_id'])) {
            $condition["service_cat_id"] = $data['service_cat_id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order='id desc';
        $fields = 'id,service_cat_id,service_term_id,item,status,created_by,created_at,updated_by,checked_by,checked_at,deleted_flag';
        try {
            $result = $this->field($fields)
                           ->where($condition)
                           ->select();
            $data = array();
            if($result) {
                foreach($result as $item){
                    $item['item'] = json_decode($item['item']);
                    $data[] = $item;
                }
            }
            return $data;
        } catch(Exception $e) {
           // var_dump($e);
            return array();
        }
    }

    /**
     * @desc 添加数据
     * @author  klp
     * @param array $condition
     * @return bool
     */
    public function addData($condition,$userInfo,$service_cat_id,$service_term_id) {
        if (!isset($condition)) {
            return false;
        }
        if(!empty($condition['item'])){
            $data['item'] = json_encode($condition['item']);
        }
        $data['service_cat_id'] = $service_cat_id;
        $data['service_term_id'] = $service_term_id;
        $data['created_by'] = $userInfo['id'];
        $data['created_at'] = date('Y-m-d H:i:s', time());
        try{
            $res = $this->add($data);
            if(!$res){
                return false;
            }
            return $res;
        } catch (Exception $e) {
//            var_dump($e);
            return false;
        }
    }
    /**
     * @desc 修改数据
     * @author klp
     * @param array  $condition
     * @return bool
     */
    public function update_data($condition,$userInfo) {
        if (!isset($condition)) {
            return false;
        }
        $where = ['service_cat_id'=>$condition['id']];
        if(!empty($condition['item'])){
            $data['item'] = json_encode($condition['item']);
        }
        $data['updated_by'] = $userInfo['id'];
        $data['updated_at'] = date('Y-m-d H:i:s', time());
        try{
            $res = $this->where($where)->save($data);
            if(!$res){
                return false;
            }
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @desc 删除数据
     * @author klp
     * @param $id
     * @return bool
     */
    public function delData($id,$status) {
        if (!isset($id)) {
            return false;
        }
        try{
            $where = ['service_cat_id'=>$id];
            $res = $this->where($where)->save(['status'=>$status]);
            if(!$res){
                return false;
            }
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }
}