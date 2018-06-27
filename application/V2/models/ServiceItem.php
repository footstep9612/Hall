<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/5
 * Time: 10:59
 */
class ServiceItemModel extends PublicModel{
   //会员服务条款(三级)
    protected $dbName = 'erui_config';
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
                    $item['item'] = json_decode($item['item'],true);
                    foreach($item['item'] as $value){
                        unset($item['item']);
                        $data[$value['lang']]['item'][] = array_merge($value,$item);
                    }
                }
            }
            return $data;
        } catch(Exception $e) {
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
            $data['item'] = json_encode($condition['item'],JSON_UNESCAPED_UNICODE);
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
            $data['item'] = json_encode($condition['item'],JSON_UNESCAPED_UNICODE);
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
            $res = $this->where($where)->save(['deleted_flag' => 'Y']);
            if(!$res){
                return false;
            }
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }


    /********************************************************
     * 根据服务类型获取条款内容
     * @param string $catId    类型
     * @return array|bool|mixed
     * @author link 2017-08-18
     */
    public function getItemByCatId($catId=''){
        if(empty($catId)) {
            return false;
        }

        $condition = array(
            'service_cat_id' => $catId,
            'deleted_flag' => 'N'
        );

        try{
            $result = $this->field('id as service_item_id,service_cat_id,service_term_id,item,status')->where($condition)->select();
            return $result ? $result : array();
        }catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * 添加或编辑服务条款内容
     * @param $service_cat_id
     * @param $service_term_id
     * @param $data
     * @return bool
     * @author link
     */
    public function editItem($service_cat_id,$service_term_id,$data){
        if(empty($service_cat_id) || empty($service_term_id)) {
            return false;
        }

        if(!empty($data)){
            $userInfo = getLoinInfo();
            try{
                foreach($data as $r){
                    if(isset($r['service_item_id']) && !empty($r['service_item_id'])){    //修改
                        $id = $r['service_item_id'];
                        unset($r['service_item_id']);
                        $data_item = array_values($r);
                        $data_edit = array(
                            'service_cat_id' => $service_cat_id,
                            'service_term_id' => $service_term_id,
                            'item' => json_encode($data_item,JSON_UNESCAPED_UNICODE),
                            'updated_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                            'updated_at' => date('Y-m-d H:i:s')
                        );
                        $rel = $this->where(array('id'=>$id))->save($data_edit);
                        if(!$rel){
                            return false;
                        }
                    }else{    //添加
                        unset($r['service_item_id']);
                        $data_item = array_values($r);
                        $data_edit = array(
                            'service_cat_id' => $service_cat_id,
                            'service_term_id' => $service_term_id,
                            'item' => json_encode($data_item,JSON_UNESCAPED_UNICODE),
                            'created_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                            'created_at' => date('Y-m-d H:i:s')
                        );
                        $rel = $this->add($data_edit);
                        if(!$rel){
                            return false;
                        }
                    }
                }
                return true;
            }catch (Exception $e){
                return false;
            }
        }
        return false;
    }

    /**
     * 根据id删除
     * @param string $id
     * @return bool
     * @author link 2017-08-21
     */
    public function deleteById($id=''){
        if(empty($id)) {
            return false;
        }

        $condition = array('id'=>$id);
        $data = array(
            'deleted_flag' => 'Y'
        );
        try{
            $result = $this->where($condition)->save($data);
            return $result ? true : false;
        }catch (Exception $e){
            return false;
        }
    }
}