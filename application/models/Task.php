<?php
/*
	任务Model
*/
class TaskModel extends ZysModel {
	private $g_table = 'task';
	
	public function __construct() {
		parent::__construct($this->g_table);
	}
	/*
		添加任务
		@param $data array
		@return mixed
	*/
	public function TaskCreate($data){
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			return false;
		}
	}
	
	/*
		任务列表
		@param	$field	string	查询字段
		@param	$option	array	列表where条件
		@param	$start	int		起始条数	
		@param	$limit	int		每页多少条	
		@return array
	*/
	public function TaskList($field, $option, $start, $limit){
		return $this->field($field)->where($option)->limit($start, $limit)->select();
	}
	/*
		任务详情
		@param	$option	array	列表where条件
		@return array
	*/
	public function TaskOne($option){
		return $this->where($option)->find();
	}
	/*
		删除任务逻辑删除
		@param	$option	array	where条件
		@param	$data	array	更新的数据
		@return	bool
	*/
	public function TaskDel($option, $data){
		$status = $this->where($option)->save($data);
		if($status){
			return true;
		}else{
			return false;
		}
	}
	/*
		mongo
	*/
	public function mongo(){
		$this->db = "db_mongo";
		
		print_r($this);
	}


	/**
     * 获取符合条件的数据总数
     * @param array $where
     * @return int
     * @author Wen
     */

	public function SelCount( $where = null )
    {
        $sql  = 'SELECT COUNT(*) AS num FROM '.$this->g_table.' AS t' ;
        if ( $where ){
            $sql .= ' WHERE '.$where;
        }
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 任务列表
     * @param int $start 开始
     * @param int $limit 每页条数
     * @param string $where 条件
     * @return array
     * task_id      任务id
     * task_name    任务名称
     * task_content 任务内容(json格式)
     * task_type    任务类型( 1一次性常规任务 2重复性常规任务 3单一项目流程任务 4重复项目流程任务 )
     * assigned_by  做分配动作的人
     * assigned_to  分配给的用户
     * task_state   任务状态
     * plan_end_at  任务计划结束时间
     * inforce_at   任务下发时间
     * remark       任务描述
     * @author Wen
     */

    public function SelList( $start = 0, $limit = null, $where = null )
    {
        $sql  = 'SELECT * ';
        // $sql .= ' t.`task_id`,t.`project_id`,t.`task_name`,t.`task_content`,t.`task_type`,t.`assigned_by`,t.`assigned_to`,t.`task_state`,t.`plan_end_at`,t.`inforce_at`,t.`created_at`,`remark`';
        $sql .= ' FROM '.$this->g_table.' AS t';
        if ( $where ){
            $sql .= ' WHERE '.$where;
        }
        $sql .= ' ORDER BY t.`updated_at` DESC,t.`created_at` DESC';
        if ( $limit ){
            $sql .= ' LIMIT '.$start.','.$limit;
        }
        return $res = $this->query( $sql );
    }

    /**
     * 任务创建
     * @param array $data
     * @return bool $res
     * @author Wen
     */

    public function CreateOne( $data )
    {
        /*$sql  = 'INSERT INTO '.$this->g_table;
        $sql .= ' ( `task_name`,`task_type`,`task_content`,`task_state`,`plan_end_at`,`created_at`)';
        $sql .= ' VALUES ("'.$data['task_name'].'",'.$data['task_type'].',"'.$data['task_content'].'",'.$data['task_state'].',"'.date( 'Y-m-d H:i:s', time() ).'","'.date( 'Y-m-d H:i:s', time() ).'")';
        return $this->execute($sql);*/
        return $this->add( $data );
    }

    /**
     * 批量创建
     * @param array $data
     * @return int $res 影响行
     * @author Wen
     */

    public function CreateMore( $data )
    {
        // 处理数据获取 要添加数据的字段名
        $sql_key = NULL;
        $data_key = array_keys( $data[0] );
        foreach ( $data_key as $k_k => $k_v ){
            $sql_key .= '`'.$k_v.'`,';
        }
        $sql_key = substr( $sql_key,0,-1);
        // 处理数据 获取要添加的数据
        $sql_value = NULL;
        foreach ( $data as $v_k => $v_v ){
            $sql_value .= '(';
            $data_v_data = array_values( $v_v );
            foreach ( $data_v_data as $v_d_k => $v_d_v ){
                $sql_value .= "'".$v_d_v."',";
            }
            $sql_value = substr( $sql_value,0,-1 );
            $sql_value .= '),';
        }
        $sql_value = substr( $sql_value,0,-1 );
        // 组装sql
        $sql  = 'INSERT INTO '.$this->g_table;
        $sql .= ' ('.$sql_key.')';
        $sql .= ' VALUES '.$sql_value;
        $res = $this->execute( $sql );
        return $res;
    }

    /**
     * 修改状态
     * @param array $where
     * @param array $data
     * @return bool (true/false)
     * @author Wen
     */

    public function UpdOne( $where, $data )
    {
        return $this->where($where)->save($data);
    }

    /**
     * 删除
     * @param array $where
     * @return bool
     * @author Wen
     */

    public function DelOne( $where )
    {
        return $this->where( $where )->delete();
    }

}
