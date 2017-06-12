<?php

/**
 * model BidModel
 * 需求 model
 */

class BidModel extends ZysModel
{
    private $g_table = 'bid';

    public function __construct() {
        parent::__construct( $this->g_table );
    }

	/*
		H5单条详情
	*/
	public function GetOne($field, $option){
		return $this->field($field)->where($option)->find();
	}
    /**
     * 获取总数
     * @param string $where
     * @return int
     * @author Wen
     */

    public function SelCount( $where = null )
    {
        $sql  = 'SELECT COUNT(*) as num FROM '.$this->g_table.' AS b';
        $sql .= ' WHERE b.`del_flag` = 0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 需求列表
     * @param int $start 开始位置
     * @param int $length 每页显示长度
     * @param string $where 条件
     * @return array
     * @author Wen
     */

    public function SelList( $start = 0, $length = 10, $where = null )
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
        $sql .= ' FROM '.$this->g_table.' AS b';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = b.`user_main_id`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = b.`organization_id`';
        $sql .= ' WHERE b.`del_flag` = 0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $sql .= ' ORDER BY u_time DESC';
        $sql .= ' LIMIT '.$start.','.$length;
        $res = $this->query( $sql );
        return $res;
    }

    /**
     * 数据详情
     * @param int/string $id
     * @param string $where
     * @return array
     * @author Wen
     */

    public function SelOne( $id, $where = null )
    {
        $sql  = 'SELECT';
        $sql .= ' b.`bid_id` AS id';                        // 数据id
        $sql .= ',b.`state_history` AS d_history';          // 历史操作记录
        $sql .= ',b.`status` AS d_status';                  // 需求状态（1新提交/2驳回/3重新提交/4同意立项/5拒绝立项 ）
        $sql .= ',b.`bid_tran_type` AS d_type';             // 交易类型（11租赁/12购买/13购买or租赁）
        $sql .= ',b.`bid_target_category` AS dt_type';      // 设备类型（0所有/1闲置新设备/2二手设备）
        // $sql .= ',b.`bid_target_name` AS dt_name';          // 设备名称
        $sql .= ',b.`bid_clue_desc` AS dt_desc';            // 设备描述
        $sql .= ',b.`bid_location` AS d_add';               // 提交地址（APP 定位地址）
        $sql .= ',b.`created_at` AS d_cre';                 // 提交时间（数据创建时间）
        $sql .= ',b.`owner_dept` AS d_dept';                // 所属部门
        $sql .= ',b.`plan_reply_time` AS d_r_time';         // 最晚答复时间
        $sql .= ',b.`updated_at` AS u_time';                // 更新时间（用于排序）
        $sql .= ',u_sub.`username` AS sub_name';            // 提交人姓名
        $sql .= ',u_sub.`mobile` AS sub_phone';             // 提交人账号
        $sql .= ',u_org.`org_name` AS org_name';            // 客户名称
        $sql .= ',u_org.`org_home_country` AS org_country'; // 所在国家
        $sql .= ',u_org.`org_priority` AS org_level';       // 客户级别
        $sql .= ',u_org.`org_size_level` AS org_size';      // 客户分级
        $sql .= ',u_org.`org_contact_address` AS org_cadd'; // 联系地址
        $sql .= ',u_org.`org_contact_person` AS org_cname'; // 联系人
        $sql .= ',u_org.`org_contact_telno` AS org_cphone'; // 联系方式
        $sql .= ' FROM '.$this->g_table.' AS b';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = b.`user_main_id`';
        $sql .= ' LEFT JOIN user_main AS u_fol ON u_fol.`user_main_id` = b.`followed_by`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = b.`organization_id`';
        $sql .= ' WHERE b.`del_flag` = 0';
        if ( $id && strpos( $id, ',' ) ){
            $sql .= ' AND b.`bid_id` in('.$id.')';
        }else{
            $sql .= ' AND b.`bid_id` ='.$id;
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


}
