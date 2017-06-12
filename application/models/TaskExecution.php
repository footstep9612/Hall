<?php
/**
 * 任务执行Model
 */
class TaskExecutionModel extends ZysModel
{
    private $g_table = 'task_execution';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 添加
     * @param $data
     * @return bool
     * @Author Wen
     */

    public function CreateOne( $data )
    {
        return $this->add($data);
    }


}