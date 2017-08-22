<?php
/**
 * @desc 审核日志模型
 * @author liujf 2017-07-01
 */
class ServiceCatModel extends PublicModel {

    protected $dbName = 'erui2_config';
    protected $tableName = 'service_cat';
    
    public function __construct() {
        parent::__construct();
    }
    //状态
    const STATUS_VALID = 'VALID';          //有效
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除

    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

	/**
     * @desc 获取列表
 	 * @author liujf 2017-07-01
     * @param array $condition
     * @return array
     */
    public function getList($condition,$limit,$order='id desc') {
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $fields = 'id,parent_id,level_no,category,sort_order,status,created_by,created_at,updated_by,checked_by,checked_at';
        if(!empty($limit)){
            $result = $this->field($fields)
                         ->where($condition)
                         ->limit($limit['page'] . ',' . $limit['num'])
                         ->order($order)
                         ->select();

        }else{
            $result = $this->field($fields)
                         ->where($condition)
                         ->order($order)
                         ->select();
        }
        $data =array();
        if($result){
            $termModel = new ServiceTermModel();
            foreach($result as $item){
                $count = $termModel->field('id')->where(['service_cat_id'=>$item['id']])->count();
                $item['count']=$count?$count:0;
                $item['category'] = json_decode($item['category']);
                $data[] = $item;
            }
        }
        return $data;
    }
    
	/**
	 * @desc 添加数据
	 * @author  klp
	 * @param array $condition
	 * @return  bool
	 */
	public function addData($condition) {
        if (!isset($condition['services'])) {
            return false;
        }
        $userInfo = getLoinInfo();
        $this->startTrans();
        try{
            if(is_array($condition['services'])) {
                foreach ($condition['services'] as $item) {
                    $data = $this->create($item);
                    if(!empty($data['category'])){
                        $save['category'] = json_encode($data['category']);
                    }
                    $save['created_by'] = $userInfo['id'];
                    $save['created_at'] = date('Y-m-d H:i:s', time());
                    $service_cat_id = $this->add($save);
                    if (!$service_cat_id) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceTermModel = new ServiceTermModel();
                    $service_term_id = $ServiceTermModel->addData($item,$userInfo,$service_cat_id);
                    if (!$service_term_id) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceItemModel = new ServiceItemModel();
                    $resultItem = $ServiceItemModel->addData($item,$userInfo,$service_cat_id,$service_term_id);
                    if (!$resultItem) {
                        $this->rollback();
                        return false;
                    }
                }
            } else{
                return false;
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
	}

	/**
	 * @desc 获取详情
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function detail($condition) {
            $info = $this->where($condition)->select();
            if($info){
                for($i=0;$i<count($info);$i++){
                    $sql ="SELECT `id`,`lang`,`service_cat_no`,`service_code`,`service_name`,`remarks`,`status`,`created_by`,`created_at`,`updated_by`,`updated_at`,`checked_by`,`checked_at` FROM `erui2_config`.`service_item` where deleted_flag ='N' and service_cat_no = '".$info[$i]['cat_no']."'";
                    $row = $this->query( $sql );
                }
            }
            $sql ="SELECT `id`,`lang`,`service_cat_no`,`service_code`,`service_name`,`remarks`,`status`,`created_by`,`created_at`,`updated_by`,`updated_at`,`checked_by`,`checked_at` FROM `erui2_config`.`service_item` where deleted_flag ='N' and service_cat_no = '".$info['cat_no']."'";
            $row = $this->query( $sql );
            $info['service_item'] = $row;
		return $info;
	}
	

	/**
	 * @desc 修改数据
	 * @author klp
	 * @param array  $condition
	 * @return bool
	 */
    public function update_data($condition) {
        if (!isset($condition['services'])) {
            return false;
        }
        $userInfo = getLoinInfo();
        $this->startTrans();
        try{
            if(is_array($condition['services'])) {
                foreach ($condition['services'] as $item) {
                    $where = ['id'=>$item['id']];
                    if(!empty($item['category'])){
                        $data['category'] = json_encode($item['category']);
                    }
                    $data['updated_by'] = $userInfo['id'];
                    $data['updated_at'] = date('Y-m-d H:i:s', time());
                    $res = $this->where($where)->save($data);
                    if (!$res) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceTermModel = new ServiceTermModel();
                    $resTerm = $ServiceTermModel->update_data($item,$userInfo);
                    if (!$resTerm) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceItemModel = new ServiceItemModel();
                    $resItem = $ServiceItemModel->update_data($item,$userInfo);
                    if (!$resItem) {
                        $this->rollback();
                        return false;
                    }
                }
            } else{
                return false;
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

	/**
	 * @desc 删除数据
	 * @author klp
	 * @param $id
	 * @return bool
	 */
	public function delData($id) {
        $this->startTrans();
        try{
            $status = self::STATUS_DELETED;
            $where = ['id'=>$id];
            $res = $this->where($where)->save(['status'=>$status]);
            if (!$res) {
                $this->rollback();
                return false;
            }
            $ServiceTermModel = new ServiceTermModel();
            $resTerm = $ServiceTermModel->delData($id,$status);
            if (!$resTerm) {
                $this->rollback();
                return false;
            }
            $ServiceItemModel = new ServiceItemModel();
            $resItem = $ServiceItemModel->delData($id,$status);
            if (!$resItem) {
                $this->rollback();
                return false;
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
	}

    /**
     * 会员服务信息
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        //$data['id'] = 3;
        if(isset($data['service_cat_id']) && !empty($data['service_cat_id'])) {
            $condition["id"] = $data['service_cat_id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order='id desc';
        //redis
        if (redisHashExist('ServiceCat', md5(json_encode($condition)))) {
           // return json_decode(redisHashGet('ServiceCat', md5(json_encode($condition))), true);
        }
        $fields = 'id,parent_id,level_no,category,sort_order,status,created_by,created_at,updated_by,checked_by,checked_at';
        try {
            $result = $this->field($fields)
                           ->where($condition)
                           ->select();
            $data = array();
            if ($result) {
                $employee = new EmployeeModel();
                foreach($result as $item){
                    $createder = $employee->getInfoByCondition(array('id' => $item['created_by']), 'id,name,name_en');
                    if ($createder && isset($createder[0])) {
                        $item['created_by'] = $createder[0];
                    }
                    $updateder = $employee->getInfoByCondition(array('id' => $item['updated_by']), 'id,name,name_en');
                    if ($updateder && isset($updateder[0])) {
                        $item['updated_by'] = $updateder[0];
                    }
                    $checkeder = $employee->getInfoByCondition(array('id' => $item['checked_by']), 'id,name,name_en');
                    if ($checkeder && isset($checkeder[0])) {
                        $item['checked_by'] = $checkeder[0];
                    }
                    $item['category'] = json_decode($item['category'],true);
                    $ServiceTermModel = new ServiceTermModel();
                    $resultTerm = $ServiceTermModel->getInfo($item);
                    foreach($item['category'] as $value){
                        if(empty($resultTerm)){
                            $resultTerm[$value['lang']] = $resultTerm;
                        }
                        unset($item['category']);
                        $data[$value['lang']]['category'][] = array_merge($value,$item,$resultTerm[$value['lang']]);
                    }
                }
               // redisHashSet('ServiceCat', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch(Exception $e) {
            return array();
        }

    }
    /**
    * 所有服务的详情
    * @param $input
    * @author klp
    */
    public function getAllService() {
        $condition = array(
            'deleted_flag' => self::DELETE_N,
            'status' => self::STATUS_VALID
        );
        try{
            $data = array();
            $ServiceTermModel = new ServiceTermModel();
            $ServiceItemModel = new ServiceItemModel();
            $result = $this->field('id,category,status')->where($condition)->select();
//            jsonReturn($result);
            if($result){
                foreach($result as $category){
                    //条款
                    $term = $ServiceTermModel->field('id as service_term_id,term,choice_flag,add_flag,status')->where([ 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$category['id']])->select();
                    //内容
                    $item = $ServiceItemModel->field('id as service_item_id,service_cat_id,service_term_id,item,status')->where([ 'deleted_flag' => self::DELETE_N, 'status' => self::STATUS_VALID,'service_cat_id'=>$category['id']])->select();
                    $result = $this->initService($category,$term,$item);
                    if($result === false) {
                        jsonReturn('',ErrorMsg::FAILED);
                    }
                    $data[] = $result;
                }
                return $data;
            }
            return array();
        }catch (Exception $e){
            return false;
        }
    }

    /**************************************************************
     * 服务详情
     * @param $input
     * @author link
     */
    public function getService($input = []) {
        if(!isset($input['id']) || empty($input['id'])) {
            jsonReturn('',ErrorMsg::ERROR_PARAM);
        }

        $catId = trim($input['id']);

        /**
         * 获取服务类型
         */
        $result_cat = $this->getCatById($catId);

        /**
         * 根据服务类型查询服务条款
         */
        $stModel = new ServiceTermModel();
        $result_term = $stModel->getTermByCatId($catId);

        /**
         * 根据服务类型获取条款内容
         */
        $siModel = new ServiceItemModel();
        $result_item = $siModel -> getItemByCatId($catId);

        /**
         * 格式化服务条款
         */
        $result = $this->initService($result_cat,$result_term,$result_item);
        if($result === false) {
            jsonReturn('',ErrorMsg::FAILED);
        }else{
            jsonReturn($result);
        }
    }

    public function editService($input = []){
        if(empty($input)) {
            return false;
        }

        $data = $this->unInitService($input);
        $this->startTrans();
        try{
            $service_cat_id = $this->updateCategoryById(isset($input['id']) ? $input['id'] : '' ,$data);
            if($service_cat_id){
                $service_termModel = new ServiceTermModel();
                $service_term_id = $service_termModel ->editTerm($service_cat_id,$data);
                if(!$service_term_id){
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return true;
        }catch (Exception $e){
            $this->rollback();
            return false;
        }
    }

    /**
     * 添加或根据id修改
     * @param string $id
     * @param array $data
     * @return bool
     */
    public function updateCategoryById($id='',$data=[]){
        if(empty($data) || !isset($data['category'])){
            return false;
        }

        $category = json_encode($data['category']);
        $userInfo = getLoinInfo();

        try{
            if(empty($id)){    //添加
                $data_insert = array(
                    'category' => $category,
                    'created_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                    'created_at' => date('Y-m-d H:i:s')
                );
                $result = $this->add($data_insert);
                if($result){
                    return $result;
                }
            }else{    //修改
                $condition = array(
                    'id' => trim($id)
                );
                $data_update = array(
                    'category'=>$category,
                    'updated_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $result = $this->where($condition)->save($data_update);
                if($result){
                    return $id;
                }
            }
            return false;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 根据id获取分类
     * @param string $id
     * @return array|bool|mixed
     * @author link 2017-08-18
     */
    public function getCatById($id = '') {
        if(empty($id)) {
            return false;
        }

        $condition = array(
            'id' => $id,
            'deleted_flag' => self::DELETE_N
        );
        try{
            $result = $this->field('id,category,status')->where($condition)->find();
            return $result ? $result : array();
        }catch (Exception $e){
            return false;
        }
        return false;
    }

    /**
     * 格式化服务条款
     * @param array $cat
     * @param array $term
     * @param array $item
     */
    protected function initService($category = [] , $term = [] ,$item =[]) {
        if($category !== false && $term !== false && $item !== false){
            $data = $data_term = $data_item = [];
            /**
             * 处理条款内容
             */
            if(!empty($item)) {
                foreach($item as $im){
                    $item_r = json_decode($im['item'],true);
                    if($item_r) {
                        foreach($item_r as $ir){
                            $ir['service_item_id'] = $im['service_item_id'];
                            $ir['status'] = $im['status'];
                            $data_item[$ir['lang']][$im['service_term_id']][] = $ir;
                        }
                    }
                }
            }
            /**
             * 处理条款
             */
            $i=0;
            if(!empty($term)) {
                foreach($term as $tm) {
                    $term_r = json_decode($tm['term'],true);
                    if($term_r) {
                        foreach($term_r as $term_i){
                            $term_i['service_term_id'] = $tm['service_term_id'];
                            $term_i['choice_flag'] = $tm['choice_flag'];
                            $term_i['add_flag'] = $tm['add_flag'];
                            $term_i['status'] = $tm['status'];
                            $term_i['item']=isset($data_item[$term_i['lang']][$tm['service_term_id']]) ? $data_item[$term_i['lang']][$tm['service_term_id']] : array();
                            $data_term[$term_i['lang']][] = $term_i;
                        }
                    }
                }
            }
            /**
             * 处理服务类别
             */

            if(!empty($category)) {
                $data['id'] = $category['id'];
                $category_ary = json_decode($category['category'] , true);
                if($category_ary) {
                    foreach ( $category_ary as $ite ) {
                        $ite['status'] = $category['status'];
                        $data[$ite['lang']]['category'] = $ite;
                        $data[$ite['lang']]['term'] = isset($data_term[$ite['lang']]) ? $data_term[$ite['lang']] : array();
                    }
                }
            }

           return $data;
        } else {
            return false;
        }
    }

    /**
     * 反格式化服务条款
     */
    protected function unInitService($input = []){
        if(empty($input)) {
            return false;
        }

        $data = $term = $item = array();
        foreach($input as $lang => $items){
            if(!in_array($lang,array('zh','en','es','ru'))) {
                continue;
            }
            $items['category']['lang'] = $lang;
            $data['category'][] = $items['category'];

            /**
             * 处理条款
             */
            foreach($items['term'] as $k=>$tm) {
                /**
                 * 处理条款内容
                 */
                foreach($tm['item'] as $key=>$im) {
                    $im['lang'] = $lang;
                    if(isset($im['service_item_id'])){
                        $item[$k][$key]['service_item_id'] = $im['service_item_id'];
                    }
                    unset($im['service_item_id']);
                    $item[$k][$key][$lang] = $im;
                }

                $tm['lang'] = $lang;
                $term[$k]['choice_flag'] = $tm['choice_flag'];
                $term[$k]['add_flag'] = $tm['add_flag'];
                $term[$k]['service_term_id'] = $tm['service_term_id'];
                unset($tm['choice_flag'],$tm['add_flag'],$tm['item'],$tm['service_term_id']);
                $term[$k]['term'][] = $tm;
                $term[$k]['item'] = $item[$k];
                $data['term'] = $term;
            }
        }
        return $data;
    }

}
