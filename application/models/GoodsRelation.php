<?php

/**
 * Class GoodsRelationModel
 * 需求、商品关联类
 */

class GoodsRelationModel extends ZysModel
{
    private $g_table = 'goods_relation';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     *
     * @param int $pk 数据id
     * @param int $type (0 bid 1 quota)
     * @param string $where
     * @return array
     * @author Wen
     */

    public function WhereList( $pk, $type =0, $where = null )
    {
        $sql  = ' SELECT * ';
        $sql .= ' FROM '.$this->g_table.' as g_r';
        $sql .= ' LEFT JOIN goods AS g ON g.`goods_id` = g_r.`goods_id`';
        $sql .= ' WHERE g_r.`del_flag` = 0';
        $sql .= ' AND g_r.`target_type` ='.$type;
        $sql .= ' AND g_r.`target_pk` ='.$pk;
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        return $res;
    }

    /**
     * 获取一条数据
     * @param $where
     * @return mixed
     */

    public function SelOne( $where )
    {
        return $this->where( $where )->find();
    }

    /**
     * 添加一条数据
     * @param $data
     * @return mixed
     */

    public function CreOne( $data )
    {
        return $this->add( $data );
    }

    /**
     * 修改数据
     * @param $where
     * @param $data
     * @return bool
     */

    public function UpdOne( $where, $data )
    {
        return $this->where( $where )->save( $data );
    }

}