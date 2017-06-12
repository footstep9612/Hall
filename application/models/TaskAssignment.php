<?php
/**
 * 任务分配Model
 */
class TaskAssignmentModel extends ZysModel
{
    private $g_table = 'task_assignment';

    public function __construct()
    {
        parent::__construct($this->g_table);
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
     * 添加一个
     * @param array $data
     * @return bool
     * @author Wen
     */

    public function CreateOne( $data )
    {
        return $this->add($data);
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
     * 获取详情
     * @param array $where
     * @return array
     * @author Wen
     */

    public function SelOne( $where )
    {
        return $this->where( $where )->find();
    }

    /**
     * 删除
     * @param array $where
     * @return bool true/false
     * @author Wen
     */

    public function DelOne( $where )
    {
        return $this->where( $where )->delete();
    }

}