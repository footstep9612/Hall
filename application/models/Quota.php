<?php

/**
 * model QuotaModel
 * 出售、出租 model
 */

class QuotaModel extends ZysModel
{
    private $g_table = 'quota';

    public function __construct()
    {
        parent::__construct( $this->g_table );
    }

    /**
     * 获取总数
     * @param string $where
     * @return int
     * @author Wen
     */

    public function SelCount( $where = null )
    {
        $sql  = 'SELECT COUNT(*) as num FROM '.$this->g_table.' AS q';
        $sql .= ' WHERE q.`del_flag` = 0';
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
        $sql  = ' SELECT';
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
        $sql .= ' FROM '.$this->g_table.' AS q';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = q.`user_main_id`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = q.`organization_id`';
        $sql .= ' WHERE q.`del_flag` = 0';
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
        $sql .= ' q.`quota_id` AS id';                          // 数据id
        $sql .= ',q.`state_history` AS d_history';              // 历史操作记录
        $sql .= ',q.`status` AS d_status';                      // 需求状态（1新提交/2驳回/3重新提交/4同意立项/5拒绝立项 ）
        $sql .= ',q.`quote_tran_type` AS d_type';               // 交易类型（21出租/22出售/23出售or出租）
        $sql .= ',q.`quote_target_category` AS dt_type';        // 设备类型（0所有/1闲置新设备/2二手设备）
        // $sql .= ',q.`quota_target_name` AS dt_name';            // 设备名称
        $sql .= ',q.`quote_clue_desc` AS dt_desc';              // 设备描述
        $sql .= ',q.`quote_location` AS d_add';                 // 提交地址（APP 定位地址）
        $sql .= ',q.`created_at` AS d_cre';                     // 提交时间（数据创建时间）
        $sql .= ',q.`owner_dept` AS d_dept';                    // 所属部门
        $sql .= ',q.`plan_reply_time` AS d_r_time';             // 最晚答复时间
        $sql .= ',q.`updated_at` AS u_time';                    // 更新时间（用于排序）
        $sql .= ',u_sub.`username` AS sub_name';                // 提交人姓名
        $sql .= ',u_sub.`mobile` AS sub_phone';                 // 提交人账号
        $sql .= ',u_org.`org_name` AS org_name';                // 客户名称
        $sql .= ',u_org.`org_home_country` AS org_country';     // 所在国家
        $sql .= ',u_org.`org_priority` AS org_level';           // 客户级别
        $sql .= ',u_org.`org_size_level` AS org_size';          // 客户分级
        $sql .= ',u_org.`org_contact_address` AS org_cadd';     // 联系地址
        $sql .= ',u_org.`org_contact_person` AS org_cname';     // 联系人
        $sql .= ',u_org.`org_contact_telno` AS org_cphone';     // 联系方式
        $sql .= ' FROM '.$this->g_table.' AS q';
        $sql .= ' LEFT JOIN user_main AS u_sub ON u_sub.`user_main_id` = q.`user_main_id`';
        $sql .= ' LEFT JOIN user_main AS u_fol ON u_fol.`user_main_id` = q.`followed_by`';
        $sql .= ' LEFT JOIN organization AS u_org ON u_org.`organization_id` = q.`organization_id`';
        $sql .= ' WHERE q.`del_flag` = 0';
        if ( $id && strpos( $id, ',' ) ){
            $sql .= ' AND q.`quota_id` in('.$id.')';
        }else{
            $sql .= ' AND q.`quota_id` ='.$id;
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
     * 数据详情
     * @param array $where
     * @return mixed
     */

    public function OneQuota( $where )
    {
        return $this->where( $where )->find();
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
        return $this->where( $where )->save( $data );
    }

    /**
     * 添加一句数据
     * @param $data
     * @return mixed
     * @author Wen
     */

    public function CreOne( $data )
    {
        return $this->add( $data );
    }


}