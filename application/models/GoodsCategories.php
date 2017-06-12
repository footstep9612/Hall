<?php
/**
 * 商品品类 model
 */
class GoodsCategoriesModel extends ZysModel
{
    private $g_table = 'goods_categories';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 根据条件获取
     * @param $where string 条件
     * @return array
     */
    public function WhereList( $where = NULL )
    {
        $sql = 'SELECT *';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' WHERE `categories_status`=0';
        if ( $where ) {
            $sql .= ' AND ' . $where;
        }
        $sql .= ' ORDER BY `created_at` DESC,`updated_at` DESC';
        return $this->query($sql);
    }

}
