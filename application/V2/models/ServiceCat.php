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

	/**
     * @desc 获取列表
 	 * @author liujf 2017-07-01
     * @param array $condition
     * @return array
     */
    public function getList($condition,$limit,$order='id desc') {
        $condition["deleted_flag"] = 'N';
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
        if (!isset($condition)) {
            return false;
        }
        $userInfo = getLoinInfo();
        $this->startTrans();
        try{
            if(is_array($condition)) {
                foreach ($condition as $item) {
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
            var_dump($e);
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
        $userInfo = getLoinInfo();
        $this->startTrans();
        try{
            if(is_array($condition)) {
                foreach ($condition as $item) {
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
     * 会员服务信息列表
     * @time  2017-08-05
     * @author klp
     */
    public function getInfo($data) {
        //$data['id'] = 3;
        if(isset($data['id']) && !empty($data['id'])) {
            $condition["id"] = $data['id'];
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
                foreach($result as $item){
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


}
