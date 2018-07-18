<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class SupplierModel extends PublicModel {

    //put your code here
    protected $tableName = 'supplier';
    protected $dbName = 'erui_supplier'; //数据库名称
    protected $g_table = 'erui_supplier.supplier';

//    protected $autoCheckFields = false;
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */

    public function getlist($condition = [], $order = " id desc") {
        $sql = 'SELECT `supplier`.`id`,`lang`,`serial_no`,`supplier_no`,`supplier_type`,`supplier`.`name`,`bn`,`profile`,`reg_capital`,`employee_count`,`country_code`,`country_bn`,`province`,`city`,`official_email`,';
        $sql .= '`official_email`,`social_credit_code`,`official_phone`,`official_fax`,`first_name`,`last_name`,`brand`,`official_website`,`logo`,`sec_ex_listed_on`,`line_of_credit`,`credit_available`,`credit_cur_bn`,`supplier_level`,`credit_level`,';
        $sql .= '`finance_level`,`logi_level`,`qa_level`,`steward_level`,`recommend_flag`,`supplier`.`status`,`supplier`.`remarks`,`apply_at`,`supplier`.`created_by`,`supplier`.`created_at`,`checked_by`,`em`.`name` as `checked_name`,`checked_at`';
        $sql_count = 'SELECT count(`supplier`.`id`) as num ';
        $str = ' FROM ' . $this->g_table;
        $str .= ' left join `erui_sys`.`employee` as `em` on `em`.`id` = `erui_supplier`.`supplier`.`checked_by` ';
        $sql .= $str;
        $sql_count .= $str;
        $where = " WHERE supplier.deleted_flag = 'N'";
        if (!empty($condition['country_bn'])) {
            $where .= ' And country_bn ="' . $condition['country_bn'] . '"';
        }
        if (!empty($condition['name'])) {
            $where .= " And supplier.name like '%" . $condition['name'] . "%'";
        }
        if (!empty($condition['supplier_no'])) {
            $where .= " And supplier.supplier_no like '%" . $condition['supplier_no'] . "%'";
        }
        if (!empty($condition['status'])) {
            $where .= ' And supplier.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And supplier.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And supplier.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if (!empty($condition['supplier_type'])) {
            $where .= ' And supplier_type  ="' . $condition['supplier_type'] . '"';
        }

        if ($where) {
            $sql .= $where;
            $sql_count .= $where;
        }
        $sql .= ' Order By ' . $order;
        if ($condition['num']) {
            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        }
        $count = $this->query($sql_count);
        $res['count'] = $count[0]['num'];
        $res['data'] = $this->query($sql);
        return $res;
    }

    /**
     * @desc 已开发供应商数量
     *
     * @author zhongyg
     * @time 2017-11-30
     */
    public function create_data($create = []) {
        if (isset($create['serial_no'])) {
            $data['serial_no'] = $create['serial_no'];
        }
        if (isset($create['supplier_no'])) {
            $data['supplier_no'] = $create['supplier_no'];
        }
        if (isset($create['supplier_type'])) {
            $data['supplier_type'] = $create['supplier_type'];
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        } else {
            $data['lang'] = 'en';
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['country_code'])) {
            $data['country_code'] = $create['country_code'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['social_credit_code'])) {
            $data['social_credit_code'] = $create['social_credit_code'];
        }
        if (isset($create['last_name'])) {
            $data['last_name'] = $create['last_name'];
        }

        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }

        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['sec_ex_listed_on'])) {
            $data['sec_ex_listed_on'] = $create['sec_ex_listed_on'];
        }
        if (isset($create['line_of_credit'])) {
            $data['line_of_credit'] = $create['line_of_credit'];
        }
        if (isset($create['credit_available'])) {
            $data['credit_available'] = $create['credit_available'];
        }
        if (isset($create['credit_cur_bn'])) {
            $data['credit_cur_bn'] = $create['credit_cur_bn'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['checked_at'])) {
            $data['checked_at'] = $create['checked_at'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['status'])) {
            $data['status'] = $create['status'];
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        if (isset($create['created_by'])) {
            $data['created_by'] = $create['created_by'];
        }
        if (isset($create['address'])) {
            $data['address'] = $create['address'];
        }
        try {
            $datajson = $this->create($data);
            $res = $this->add($datajson);
            return $res;
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author jhw
     */
    public function info($data) {
        if ($data['id']) {
            $buyerInfo = $this->where(array("supplier.id" => $data['id']))->field('supplier.*,em.name as checked_name,ma.name as country_name ')
                    ->join('erui_sys.employee em on em.id=supplier.checked_by', 'left')
                    ->join('erui_dict.country ma on ma.`bn`=supplier.country_bn and  ma.`lang`=supplier.lang', 'left')
                    ->find();
            return $buyerInfo;
        } else {
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create, $where) {

        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['serial_no'])) {
            $data['serial_no'] = $create['serial_no'];
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['country_code'])) {
            $data['country_code'] = $create['country_code'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['last_name'])) {
            $data['last_name'] = $create['last_name'];
        }
        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['bank_name'])) {
            $data['bank_name'] = $create['bank_name'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['checked_at'])) {
            $data['checked_at'] = $create['checked_at'];
        }
        if ($create['status']) {
            $data['status'] = $create['status'];
        }
        if (isset($create['social_credit_code'])) {
            $data['social_credit_code'] = $create['social_credit_code'];
        }
        if (isset($create['supplier_type'])) {
            $data['supplier_type'] = $create['supplier_type'];
        }
        return $this->where($where)->save($data);
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getSkuSupplierList($condition = []) {
        $where = ' where s.deleted_flag="N" ';
        if (!empty($condition['name'])) {
            $where .= 'and s.name like "%' . $condition['name'] . '%"';
        }
        if (!empty($condition['sec_ex_listed_on'])) {
            $where .= 'and s.sec_ex_listed_on like "%' . $condition['sec_ex_listed_on'] . '%"';
        }

        $currentPage = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSizce']) ? $condition['pageSize'] : 10;
        $num = $pagesize;
        $page = ($currentPage - 1) * $pagesize;

        if (!empty($condition['sku'])) {
            $sql = 'SELECT s.id as supplier_id,s.name,s.sec_ex_listed_on,s.sec_ex_listed_on,t.* FROM erui_supplier.supplier s ';
            $sql .= 'LEFT JOIN (SELECT gs.supplier_id as s_id,gs.sku,gs.pn,gs.brand,p.price as purchase_unit_price,p.price_cur_bn as purchase_price_cur_bn,p.price_validity as period_of_validity,';
            $sql .= 'g.gross_weight_kg,g.pack_type as package_mode ';
            $sql .= 'FROM erui_goods.goods_supplier gs ';
            $sql .= 'LEFT JOIN erui_goods.goods g ON g.sku = gs.sku ';
            $sql .= 'LEFT JOIN erui_goods.goods_cost_price p ON p.sku = gs.sku ';
            $sql .= 'WHERE gs.sku = ' . $condition['sku'] . ' GROUP BY gs.supplier_id  ';
            $sql .= ') t ON t.s_id = s.id ' . $where;
            $sql_count = $sql;
            $sql = $sql . ' ORDER BY t.sku DESC LIMIT ' . $page . ',' . $num;
        } else {
            $sql = 'SELECT s.id as supplier_id,s.name,s.sec_ex_listed_on,s.sec_ex_listed_on FROM erui_supplier.supplier s ';
            $sql .= $where;
            $sql_count = $sql;
            $sql = $sql . ' ORDER BY s.id DESC LIMIT ' . $page . ',' . $num;
        }

        try {
            $list = $this->query($sql_count);
            $data = $this->query($sql);

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = count($list);
                $results['data'] = $data;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    private function _getcondition($condition) {
        $where = ['deleted_flag' => 'N',];
        $map['name'] = ['neq', ''];
        $map[] = '`name` is not null';
        $map['_logic'] = 'and';
        $where['_complex'] = $map;
        $this->_getValue($where, $condition, 'created_at', 'between', 'created_at');
        return $where;
    }

    /**
     * 已开发供应商数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCount($condition = []) {

        $where = $this->_getcondition($condition);
        try {
            $count = $this->where($where)->count();

            return $count;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return 0;
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setSupplier(&$arr) {
        if ($arr) {

            $supplier_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['supplier_id']) && $val['supplier_id']) {
                    $supplier_ids[] = trim($val['supplier_id']);
                }
            }
            if ($supplier_ids) {
                $where = ['deleted_flag' => 'N', 'id' => ['in', $supplier_ids]];
                $map['name'] = ['neq', ''];
                $map[] = '`name` is not null';
                $map['_logic'] = 'and';
                $where['_complex'] = $map;
                $supplier_ret = $this
                                ->field('id,name')
                                ->where($where)->select();

                $suppliers = [];
                foreach ($supplier_ret as $supplier) {
                    $suppliers[$supplier['id']] = $supplier['name'];
                }
                foreach ($arr as $key => $val) {
                    if (trim($val['supplier_id']) && isset($suppliers[trim($val['supplier_id'])])) {
                        $val['supplier_name'] = $suppliers[trim($val['supplier_id'])];
                    } else {
                        $val['supplier_name'] = '';
                    }
                    $arr[$key] = $val;
                }
            }
        }
    }

}
