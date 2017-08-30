<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/5
 * Time: 11:00
 */
class ServiceTermModel extends PublicModel{
    //会员服务条款(二级,条目)
    protected $dbName = 'erui2_config';
    protected $tableName = 'service_term';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 会员服务条款(二级)
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        if(isset($data['id']) && !empty($data['id'])) {
            $condition["service_cat_id"] = $data['id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order='id desc';
        $fields = 'id,service_cat_id,term,choice_flag,add_flag,status,created_by,created_at,updated_by,checked_by,checked_at,deleted_flag';
        try {
            $result = $this->field($fields)
                           ->where($condition)
                           ->select();
            $data = array();
            if($result) {
                foreach($result as $item){
                    $item['term'] = json_decode($item['term'],true);
                    $ServiceItemModel = new ServiceItemModel();
                    $resultItem = $ServiceItemModel->getInfo($item);
                     foreach($item['term'] as $value){
                         if(empty($resultItem)){
                             $resultItem[$value['lang']] = $resultItem;
                         }
                        unset($item['term']);
                        $data[$value['lang']]['term'][] = array_merge($value,$item,$resultItem[$value['lang']]);
                    }
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
     * @return  bool
     */
    public function addData($condition,$userInfo,$service_cat_id) {
        if (!isset($condition)) {
            return false;
        }
       if(!empty($condition['choice_flag'])){
           $data['choice_flag'] = $condition['choice_flag'];
       }
        if(!empty($condition['add_flag'])){
            $data['add_flag'] = $condition['add_flag'];
        }
        if(!empty($condition['term'])){
            $data['term'] = json_encode($condition['term'],JSON_UNESCAPED_UNICODE);
        }
        $data['service_cat_id'] = $service_cat_id;
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
        if(!empty($condition['choice_flag'])){
            $data['choice_flag'] = $condition['choice_flag'];
        }
        if(!empty($condition['add_flag'])){
            $data['add_flag'] = $condition['add_flag'];
        }
        if(!empty($condition['term'])){
            $data['term'] = json_encode($condition['term'],JSON_UNESCAPED_UNICODE);
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

    /*******************************************************
     * 根据服务类型获取服务条款
     * @param string $catId    类型
     * @return array|bool|mixed
     * @author link  2017-08-18
     */
    public function getTermByCatId($catId=''){
        if(empty($catId)) {
            return false;
        }

        $condition = array(
            'service_cat_id' => $catId,
            'deleted_flag' => 'N'
        );
        try{
            $result = $this->field('id as service_term_id,term,choice_flag,add_flag,status')
                ->where($condition)
                ->select();

            return $result ? $result : array();
        }catch (Exception $e){
            return false;
        }
        return false;
    }

    /**
     * 添加或修改服务条款
     * @param string $service_cat_id
     * @param array $data
     * @return bool
     */
    public function editTerm($service_cat_id='',$data=[]){
        if(empty($service_cat_id) || empty($data) || !isset($data['term'])){
            return false;
        }

        $userInfo = getLoinInfo();
        try{
            $serviceItemModel = new ServiceItemModel();
            foreach($data['term'] as $r){
                $data_edit = array(
                    'term' => json_encode($r['term']),
                    'service_cat_id' => $service_cat_id,
                    'choice_flag' => $r['choice_flag'],
                    'add_flag' => $r['add_flag']
                );
                if(isset($r['service_term_id']) && !empty($r['service_term_id'])){
                    $condition_update = array(
                        'id' => $r['service_term_id']
                    );
                    $data_edit['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                    $data_edit['updated_at'] = date('Y-m-d H:i:s');
                    $rel = $this->where($condition_update)->save($data_edit);
                    if(!$rel){
                        return false;
                    }else{
                        $item = $serviceItemModel->editItem($service_cat_id,$r['service_term_id'],$r['item']);
                        if(!$item) {
                            return false;
                        }
                    }
                }else{
                    $data_edit['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                    $data_edit['created_at'] = date('Y-m-d H:i:s');
                    $rel = $this->add($data_edit);
                    if(!$rel){
                        return false;
                    }else{
                        $item = $serviceItemModel->editItem($service_cat_id,$rel,$r['item']);
                        if(!$item) {
                            return false;
                        }
                    }
                }
            }
            return true;
        }catch (Exception $e){
            return false;
        }
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