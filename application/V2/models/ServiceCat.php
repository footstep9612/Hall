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

	/**
     * @desc 获取列表
 	 * @author liujf 2017-07-01
     * @param array $condition
     * @return array
     */
    public function getList($condition,$limit,$order='id desc') {
        $condition["deleted_flag"] = 'N';
        if(!empty($limit)){
            return $this->field('id,lang,parent_cat_no,level_no,name,description,sort_order,choice_flag,add_flag,remarks,status,created_by,created_at,updated_by,checked_by,checked_at')
                ->where($condition)
                ->limit($limit['page'] . ',' . $limit['num'])
                ->order($order)
                ->select();
        }else{
            return $this->field('id,lang,parent_cat_no,level_no,name,description,sort_order,choice_flag,add_flag,remarks,status,created_by,created_at,updated_by,checked_by,checked_at')
                ->where($condition)
                ->order($order)
                ->select();
        }
    }
    
	/**
	 * @desc 添加数据
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function addData($condition) {
		
		$data = $this->create($condition);

		return $this->add($data);
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
	 * @author liujf 2017-07-01
	 * @param array $where , $condition
	 * @return array
	 */
    public function update_data($data,$where) {
        if(!empty($where)){
            if(isset($data['name'])){
                $arr['name'] = $data['name'];
                return $this->where($where)->save($arr);
            }
        }else{
            return false;
        }
    }

	/**
	 * @desc 删除数据
	 * @author liujf 2017-07-01
	 * @param array $condition
	 * @return array
	 */
	public function delData($condition) {
		
		$where = $this->getWhere($condition);

		return $this->where($where)->delete();
	}
}
