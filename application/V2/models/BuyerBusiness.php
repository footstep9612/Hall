<?php
//客户档案管理 wangs
class BuyerBusinessModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer'; //客户表名
    protected $tableAccount = 'buyer_account'; //客户账号表名
    protected $tableBusiness = 'buyer_business'; //采购商业务信息表名
    public $page = 1;
    public $pageSize = 2;
    public function __construct()
    {
        parent::__construct();
    }

    //list
    public function buyerList($data)
    {
//        print_r($data);die;
        $map = array('buyer.created_by'=>$data['created_by']);
//        if(!empty($data['area_bn'])){
//            $map += array('buyer.area_bn'=>$data['area_bn']);
//        }
//        if(!empty($data['country_bn'])){
//            $map += array('buyer.country_bn'=>$data['country_bn']);
//        }
//        if(!empty($data['buyer_code'])){
//            $map += array('buyer.buyer_code'=>$data['buyer_code']);
//        }
//        if(!empty($data['name'])){
//            $map += array('buyer.name'=>$data['name']);
//        }
//        if(!empty($data['buyer_level'])){
//            $map += array('buyer.buyer_level'=>$data['buyer_level']);
//        }
//        if(!empty($data['reg_capital'])){
//            $map += array('buyer.reg_capital'=>$data['reg_capital']);
//        }
//        if(!empty($data['line_of_credit'])){
//            $map += array('buyer.line_of_credit'=>$data['line_of_credit']);
//        }
        if(empty($data['page'])){
            $page = ($this -> page-1)*($this ->pageSize);
        }else{
            $page = ($data['page']-1)*($this ->pageSize);
        }

        $info = $this->alias('buyer')
            ->join('erui_buyer.buyer_account account on buyer.id=account.buyer_id','left')
            ->join('erui_buyer.buyer_business business on account.buyer_id=business.buyer_id','left')
            ->field('buyer.id,buyer.buyer_code,buyer.name,buyer.area_bn,buyer.country_bn,buyer.line_of_credit,buyer.credit_available,buyer.buyer_level,buyer.level_at,buyer.credit_level,buyer.reg_capital,buyer.created_by,account.email,business.is_local_settlement,business.is_purchasing_relationship,business.is_net,business.net_at,business.net_invalid_at')
            ->where($map)
            ->limit($page,$this->pageSize)
            ->select();
        return $info;
    }

    /**
     * 获取列表
     * @param data $data ;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc')
    {

        $field = 'role.id,role.name,role.name_en,role.remarks,role.created_by,'
            . 'emby.name as created_name ,(select name from `erui_sys`.`employee` where id=role.updated_by) as updated_name'
            . ',role_no,admin_show,role_group,role.created_at,role.updated_at,updated_by,'
            . 'role.status,group_concat(`em`.`name`) as employee_name,group_concat(`em`.`id`) as employee_id';
        if (!empty($limit)) {
            $res = $this->field($field)
                ->join('`erui_sys`.`role_member` rm on rm.role_id=role.id', 'left')
                ->join('`erui_sys`.`employee` em on em.id=`rm`.`employee_id`', 'left')
                ->join('`erui_sys`.`employee` emby on emby.id=role.created_by', 'left')
                ->where($data)
                ->limit($limit['page'] . ',' . $limit['num'])
                ->group('role.id')
                ->order($order)
                ->select();
            return $res;
        } else {
            return $this->field($field)
                ->join('`erui_sys`.`role_member` rm on rm.role_id=role.id', 'left')
                ->join('`erui_sys`.`employee` em on em.id=`rm`.`employee_id`', 'left')
                ->join('`erui_sys`.`employee` emby on emby.id=role.created_by', 'left')
                ->where($data)
                ->group('role.id')
                ->order($order)
                ->select();
        }
    }

    /**
     * 获取列表
     * @param data $data ;
     * @return array
     * @author jhw
     */
    public function getRoleslist($id, $order = 'id desc')
    {

        $sql = 'SELECT `role_access_perm`.`func_perm_id`,`func_perm`.`url`, `func_perm`.`fn` , `func_perm`.`parent_id` ';
        $sql .= ' FROM ' . $this->table_name;
        $sql .= ' LEFT JOIN  `role_access_perm` ON `role_access_perm`.`role_id` =`role`.`id`';
        $sql .= ' LEFT JOIN  `func_perm` ON `func_perm`.`id` =`role_access_perm`.`func_perm_id`';
        $sql_where = '';
        if (!empty($id)) {
            $sql_where .= ' WHERE `role`.`id` =' . $id;
            $sql .= $sql_where;
        }

        //        if ( $where ){
        //            $sql .= $sql_where;
        //        }
        return $this->query($sql);
    }
}