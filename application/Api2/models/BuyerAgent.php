<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/30
 * Time: 11:43
 */
class BuyerAgentModel extends PublicModel{

    protected $tableName = 'buyer_agent';
    protected $dbName = 'erui2_buyer'; //数据库名称

    public function __construct($str = ''){

        parent::__construct();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     */
    public function getlist($condition = [],$order=" id desc") {
        return $this->where($condition)
            ->field('buyer_agent.id,buyer_agent.buyer_id,buyer_agent.agent_id,em.name as agent_name,em.show_name,em.mobile,em.email,em.user_no as user_no,group_concat(`org`.`name`) as group_name,buyer_agent.role,buyer_agent.created_by,buyer_agent.created_at')
            ->join('erui2_sys.employee em on em.id=buyer_agent.agent_id', 'left')
            ->join('erui2_sys.org_member on org_member.employee_id=buyer_agent.agent_id', 'left')
            ->join('erui2_sys.org on org.id=org_member.org_id', 'left')
            ->group('em.id')
            ->order('buyer_agent.id desc')
            ->select();
    }
}