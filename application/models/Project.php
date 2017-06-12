<?php

/**
 * model ProjectModel
 * 项目 model
 */

class ProjectModel extends ZysModel
{
    private $g_table = 'project';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 项目总数
     * @param string $where
     * @return int
     * @author Wen
     */

    public function SelCount( $where = null )
    {
        $sql  = 'SELECT COUNT(*) AS num FROM '.$this->g_table;
        $sql .= ' WHERE `del_flag`= 0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 项目列表
     * @param int $start
     * @param int $length
     * @param string $where
     * @return array
     * @author Wen
     */

    public function SelList( $start,$length,$where = null )
    {
        $sql  = ' SELECT ';
        $sql .= ' `project_id` AS p_id';
        $sql .= ',`bid_id` AS b_id';
        $sql .= ',`quota_id` AS q_id';
        $sql .= ',`project_number` AS p_num';
        $sql .= ',`project_desc` AS p_desc';
        $sql .= ',`project_step` AS p_step';
        $sql .= ',`status` AS p_status';
        $sql .= ',`state_history` AS p_history';
        $sql .= ',`created_at` AS p_create';
        $sql .= ',`project_step` AS p_step';        // 项目流程状态
        $sql .= ',`project_tran_type` AS p_type';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `del_flag` = 0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $sql .= ' ORDER BY `updated_at` DESC';
        $sql .= ' LIMIT '.$start.','.$length;
        return $res = $this->query( $sql );
    }

    /**
     * 单条项目详情
     * @param int/string $id
     * @param array $where
     * @return array
     * @author Wen
     */

    public function SelOne( $id,$where=null )
    {
        $sql  = ' SELECT ';
        $sql .= ' `project_id` AS p_id';            // 数据id
        $sql .= ',`bid_id` AS b_id';                // 关联数据 bid_id
        $sql .= ',`quota_id` AS q_id';              // 关联数据 quota_id
        $sql .= ',`project_number` AS p_num';       // 项目编号
        $sql .= ',`project_desc` AS p_desc';        // 项目描述
        $sql .= ',`status` AS p_status';            // 项目当前状态
        $sql .= ',`project_temp` AS p_tmp';         // 项目未下发任务
        $sql .= ',`state_history` AS p_history';    // 项目历史操作啊记录
        $sql .= ',`project_tran_type` AS p_type';   // 项目类型（求购、求租、出售、出租）
        $sql .= ',`created_at` AS p_create';         // 立项时间
        $sql .= ',`project_step` AS p_step';        // 项目流程状态
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `del_flag` = 0';
        if ( $id && strpos( $id, ',' ) ){
            $sql .= ' AND `project_id` in('.$id.')';
        }else if ( $id ){
            $sql .= ' AND `project_id` ='.$id;
        }
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        if ( $id && strpos( $id, ',' ) ){
            return $res;
        }else{
            return $res['0'];
        }
    }

    /**
     * 修改数据
     * @param array $where
     * @param array $data
     * @return bool (true/false)
     * @author Wen
     */

    public function UpdOne( $where, $data )
    {
        return $res = $this->where( $where )->save( $data );
    }

    /**
     * 添加项目
     * @param array $data
     * @return bool (true/false)
     * @author Wen
     */

    public function AddOne( $data )
    {
        return $id = $this->add($data);
    }

    /**
     * @param $where
     * @return mixed
     * @author Wen
     */

    public function ProjectOne( $where )
    {
        return $this->where( $where )->find();
    }


}