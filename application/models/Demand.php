<?php

/**
 * model DemandModel
 * 供需model
 */

class DemandModel extends ZysModel
{
    private $g_b_table = 'bid';
    private $g_q_table = 'quota';

    public function __construct() {
        parent::__construct( 'bid' );
    }

    /**
     * 获取总数
     * @param string $where_b
     * @param string $where_q
     * @return int
     * @author Wen
     */

    public function SelCount( $where_b = null,$where_q = null )
    {
        $sql  = ' SELECT COUNT( C.M ) AS num FROM (';
        $sql .= ' SELECT b.bid_id M FROM '.$this->g_b_table.' AS b WHERE b.`del_flag` = 0 AND b.`status` != 4';
        if ( $where_b ){
            $sql .= ' AND '.$where_b;
        }
        $sql .= ' UNION ALL';
        $sql .= ' SELECT q.quota_id M FROM '.$this->g_q_table.' AS q WHERE q.`del_flag` = 0 AND q.`status` != 4';
        if ( $where_q ){
            $sql .= ' AND '.$where_q;
        }
        $sql .= ')C';
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 供需列表
     * @param int $start 开始位置
     * @param int $length 每页显示长度
     * @param string $where_b 条件
     * @param string $where_q 条件
     * @param string $where 条件
     * @return array
     * @author Wen
     */

    public function SelList( $start = 0, $length = 10, $where_b = null, $where_q = null, $where = null )
    {

        $sql  = 'SELECT';
        $sql .= ' b.`bid_id` AS id';                        // 数据id
        $sql .= ', b.`status` AS d_status';                 // 需求状态（1新提交/2驳回/3重新提交/4同意立项/5拒绝立项 ）
        $sql .= ', b.`bid_tran_type` AS d_type';            // 交易类型（11租赁/12购买/13购买or租赁）
        $sql .= ', b.`bid_target_category` AS dt_type';     // 设备类型（0所有/1闲置新设备/2二手设备）
        // $sql .= ', b.`bid_target_name` AS dt_name';         // 设备名称
        $sql .= ', b.`bid_clue_desc` AS dt_desc';           // 设备描述
        $sql .= ', b.`bid_location` AS d_add';              // 提交地址（APP 定位地址）
        $sql .= ', b.`created_at` AS d_cre';                // 提交时间（数据创建时间）
        $sql .= ', b.`owner_dept` AS d_dept';               // 所属部门
        $sql .= ', b.`plan_reply_time` AS d_r_time';        // 最晚答复时间
        $sql .= ', b.`updated_at` AS u_time';               // 更新时间（用于排序）
        $sql .= ', u_sub.`username` AS sub_name';           // 提交人
        $sql .= ', u_org.`org_name` AS org_name';           // 客户名称
        $sql .= ' FROM '.$this->g_b_table.' AS b';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = b.`user_main_id`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = b.`organization_id`';
        $sql .= ' WHERE b.`del_flag` = 0 AND b.`status` != 4';
        if ( $where_b ){
            $sql .= ' AND '.$where_b;
        }
        $sql .= ' UNION ALL';
        $sql .= ' SELECT';
        $sql .= ' q.`quota_id` AS id';                      // 数据id
        $sql .= ', q.`status` AS d_status';                 // 需求状态（1新提交/2驳回/3重新提交/4同意立项/5拒绝立项 ）
        $sql .= ', q.`quote_tran_type` AS d_type';          // 交易类型（21出租/22出售/23出售or出租）
        $sql .= ', q.`quote_target_category` AS dt_type';   // 设备类型（0所有/1闲置新设备/2二手设备）
        // $sql .= ', q.`quota_target_name` AS dt_name';       // 设备名称
        $sql .= ', q.`quote_clue_desc` AS dt_desc';         // 设备描述
        $sql .= ', q.`quote_location` AS d_add';            // 提交地址（APP 定位地址）
        $sql .= ', q.`created_at` AS d_cre';                // 提交时间（数据创建时间）
        $sql .= ', q.`owner_dept` AS d_dept';               // 所属部门
        $sql .= ', q.`plan_reply_time` AS d_r_time';        // 最晚答复时间
        $sql .= ', q.`updated_at` AS u_time';               // 更新时间（用于排序）
        $sql .= ', u_sub.`username` AS sub_name';           // 提交人
        $sql .= ', u_org.`org_name` AS org_name';           // 客户名称
        $sql .= ' FROM '.$this->g_q_table.' AS q';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = q.`user_main_id`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = q.`organization_id`';
        $sql .= ' WHERE q.`del_flag` = 0 AND q.`status` != 4';
        if ( $where_q ){
            $sql .= ' AND '.$where_q;
        }
        $sql .= ' ORDER BY u_time DESC,d_cre DESC';
        $sql .= ' LIMIT '.$start.','.$length;
        $res = $this->query( $sql );
        return $res;
    }


}
