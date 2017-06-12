<?php

class SolutionModel extends ZysModel{

    private $g_table = 'solution';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 添加 解决方案
     * @param $data
     * @return bool
     * @author Wen
     */

    public function CreOne( $data )
    {
        return $this->add( $data );
    }

    /**
     * 获取一条
     * @param $where
     * @return array
     * @author Wen
     */

    public function ListOne( $where )
    {
        return $this->where( $where )->find();
    }

    /**
     * 列表
     * @param $where
     * @return array
     * @author Wen
     */

    public function SelList( $where )
    {
        return $this->where( $where )->select();
    }

    /**
     * 修改一条
     * @param $where
     * @param $data
     * @return bool
     * @author Wen
     */

    public function UpdOne( $where, $data )
    {
        return $this->where( $where )->save( $data );
    }


}