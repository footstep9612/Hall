<?php
/**
 * 话题model
 */
class TopicModel extends ZysModel {
    private $g_table = 'topic';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 创建话题
     */
    public function TopicCreate($data){
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			return false;
		}
    }

    /**
     * 获取话题
     * @return array
     * @author Wen
     */
    public function TopicList($start, $limit, $where){
        $sql = 'SELECT ';
        $sql .=' `topic_id` AS id,';
        $sql .=' `user_main_id` AS uid,';
        $sql .=' `topic_title` AS name,';
        $sql .=' `topic_abstract` AS abstract,';
        $sql .=' `topic_image_url` AS img_url,';
        $sql .=' `topic_content` AS content,';
        $sql .=' `topic_keywords` AS keywords,';
        $sql .=' `topic_type` AS t_type,';
        $sql .=' `topic_category` AS category,';
        $sql .=' `topic_url` AS url,';
		$sql .=' `topic_state` AS state,';
		$sql .=' updated_at,';
		$sql .=' updated_by,';
		$sql .=' `recommand_level` AS level';
        $sql .=' FROM '.$this->g_table.' WHERE `topic_state`!=3 '.$where.' order by topic_id desc,recommand_level desc';
		$sql .=' limit '.$start.','.$limit;
        return $this->query( $sql );
    }
	/**
     * 获取话题数量
     * 
     * 
     */
    public function TopicNum($where){
        $sql ='select count(*) as num FROM '.$this->g_table.' WHERE `topic_state`!=3 '.$where;
        return $this->query( $sql );
    }
	/*
		获取一条数据
		@param	$option	array	列表where条件
		@return array
	*/
	public function TopicInfo($field, $option){
		return $this->field($field)->where($option)->find();
	}
    /**
     * 获取一条数据
     * @param int $id 话题id
     * @return array
     * @author Wen
     */
    public function TopicSelOne( $id ){
        // $sql = 'SELECT * FROM '.$this->g_table.' WHERE `topic_state`=0 AND `topic_id` = '.$id;
        $sql = 'SELECT ';
        $sql .=' `topic_id` AS id,';
        $sql .=' `user_main_id` AS uid,';
        $sql .=' `topic_title` AS name,';
        $sql .=' `topic_abstract` AS abstract,';
        $sql .=' `topic_image_url` AS img_url,';
        $sql .=' `topic_content` AS content,';
        $sql .=' `topic_keywords` AS keywords,';
        $sql .=' `topic_type` AS t_type,';
        $sql .=' `topic_category` AS category,';
        $sql .=' `topic_url` AS url,';
		$sql .=' `topic_state` AS state,';
		$sql .=' `recommand_level` AS level,';
		$sql .=' `topic_source`, ';
		$sql .=' `created_at`, ';
		$sql .=' `updated_at`, ';
		$sql .=' `updated_by` ';
        $sql .=' FROM '.$this->g_table.' WHERE `topic_state`!=3 AND `topic_id` = '.$id;
        $res = $this->query( $sql );
		if(!empty($res)){
			return $res['0'];
		}else{
			return array();
		}
    }

    /**
     * 修改一条数据
     */
    public function TopicUpdOne($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
    }

    /**
     * 删除一条数据 逻辑删除
     * @param int $id 话题id
     * @param string $name
     * @return true/false
     * @author Wen
     */
    public function TopicDelOne( $id, $name ) {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `topic_state`=3';
        $sql .= ', `updated_by`= "'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `topic_id`='.$id;
        return $this->execute( $sql );
    }

    /**
     * 批量删除数据 逻辑删除
     * @param string $ids 话题id 的字符串
     * @param string $name
     * @return true/false
     * @author Wen
     */
    public function TopicDel( $ids, $name ) {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `topic_state`=3';
        $sql .= ', `updated_by`= "'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `topic_id` in ('.$ids.')';
		//echo $sql;exit;
        return $this->execute( $sql );
    }
}
