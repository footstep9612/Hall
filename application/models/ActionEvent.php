<?php

class ActionEventModel extends ZysModel
{

    private $g_table = 'action_event';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 添加记录
     * @param $data
     * @return bool true/false
     * @author Wen
     */

    public function CreOne( $data )
    {
        return $this->add( $data );
    }

}