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

    public function getList($condition, $limit, $order = 'id desc') {
        $condition["deleted_flag"] = 'N';
        $fields = 'id,parent_id,level_no,category,sort_order,status,created_by,created_at,updated_by,checked_by,checked_at';
        if (!empty($limit)) {
            return $this->field($fields)
                            ->where($condition)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field($fields)
                            ->where($condition)
                            ->order($order)
                            ->select();
        }
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
        try {
            if (is_array($condition)) {
                foreach ($condition as $item) {
                    $data = $this->create($item);

                    $save['category'] = $data['category'];
                    $save['created_by'] = $userInfo['id'];
                    $save['created_at'] = date('Y-m-d H:i:s', time());
                    $service_cat_id = $this->add($save);
                    if (!$service_cat_id) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceTermModel = new ServiceTermModel();
                    $service_term_id = $ServiceTermModel->addData($item, $userInfo, $service_cat_id);
                    if (!$service_term_id) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceItemModel = new ServiceItemModel();
                    $resultItem = $ServiceItemModel->addData($item, $userInfo, $service_cat_id, $service_term_id);
                    if (!$resultItem) {
                        $this->rollback();
                        return false;
                    }
                }
            } else {
                return false;
            }
            $this->commit();
            return true;
        } catch (Exception $ex) {
            $this->rollback();
            LOG::write('CLASS ' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
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
        if ($info) {
            for ($i = 0; $i < count($info); $i++) {
                $sql = "SELECT `id`,`lang`,`service_cat_no`,`service_code`,`service_name`,`remarks`,`status`,`created_by`,`created_at`,`updated_by`,`updated_at`,`checked_by`,`checked_at` FROM `erui2_config`.`service_item` where deleted_flag ='N' and service_cat_no = '" . $info[$i]['cat_no'] . "'";
                $row = $this->query($sql);
            }
        }
        $sql = "SELECT `id`,`lang`,`service_cat_no`,`service_code`,`service_name`,`remarks`,`status`,`created_by`,`created_at`,`updated_by`,`updated_at`,`checked_by`,`checked_at` FROM `erui2_config`.`service_item` where deleted_flag ='N' and service_cat_no = '" . $info['cat_no'] . "'";
        $row = $this->query($sql);
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
        try {
            if (is_array($condition)) {
                foreach ($condition as $item) {
                    $where = ['id' => $item['id']];
                    $data['category'] = $item['category'];
                    $data['updated_by'] = $userInfo['id'];
                    $data['updated_at'] = date('Y-m-d H:i:s', time());
                    $res = $this->where($where)->save($data);
                    if (!$res) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceTermModel = new ServiceTermModel();
                    $resTerm = $ServiceTermModel->update_data($item, $userInfo);
                    if (!$resTerm) {
                        $this->rollback();
                        return false;
                    }
                    $ServiceItemModel = new ServiceItemModel();
                    $resItem = $ServiceItemModel->update_data($item, $userInfo);
                    if (!$resItem) {
                        $this->rollback();
                        return false;
                    }
                }
            } else {
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
        try {
            $status = self::STATUS_DELETED;
            $where = ['id' => $id];
            $res = $this->where($where)->save(['status' => $status]);
            if (!$res) {
                $this->rollback();
                return false;
            }
            $ServiceTermModel = new ServiceTermModel();
            $resTerm = $ServiceTermModel->delData($id, $status);
            if (!$resTerm) {
                $this->rollback();
                return false;
            }
            $ServiceItemModel = new ServiceItemModel();
            $resItem = $ServiceItemModel->delData($id, $status);
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
        if (isset($data['service_cat_id']) && !empty($data['service_cat_id'])) {
            $condition["id"] = $data['service_cat_id'];
        }
        $condition["deleted_flag"] = 'N';
        $condition["status"] = 'VALID';
        $order = 'id desc';
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
                foreach ($result as $item) {
                    $ServiceTermModel = new ServiceTermModel();
                    $resultTerm = $ServiceTermModel->getInfo($item);
                    $ServiceItemModel = new ServiceItemModel();
                    $resultItem = $ServiceItemModel->getInfo($item);
                    $data[] = array_merge($item, $resultTerm, $resultItem);
                }
                // redisHashSet('ServiceCat', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

}
