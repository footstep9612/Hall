<?php

/**
 * name: Inquiry
 * desc: 询价单表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:11
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /*
     * 检查流程编码是否存在　－在用
     * @param string $serial_no  流水号
     * @return true/false
     * @author link    2017-12-19
     */

    public function checkSerialNo($serial_no) {
        if (!empty($serial_no)) {
            $where['serial_no'] = $serial_no;
        } else {
            return false;
        }

        try {
            $id = $this->field('id')->where($where)->find();
            return $id ? false : true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @param  添加询单/添加sku询单项明细.　　－在用
     * 验证询单号是否存在
     * @author link
     */
    public function addInquiry($data) {
        $this->startTrans();
        try {
            $res = $this->addData($data);
            if (!$res || $res['code'] != 1) {
                $this->rollback();
                return false;
            } else {
                $data['inquiry_id'] = $res['data']['id'];
            }

            //添加sku询单项明细
            $InquiryItemModel = new InquiryItemModel();
            if ($res['code'] == 1 && isset($data['arr_sku']) && !empty($data['arr_sku'])) {
                $scModel = new ShoppingCarModel();
                foreach ($data['arr_sku'] as $item) {
                    $item['inquiry_id'] = $res['data']['id'];
                    $item['created_by'] = $data['buyer_id'];
                    $resItem = $InquiryItemModel->addData($item);
                    if (!$resItem || $resItem['code'] != 1) {
                        $this->rollback();
                        return false;
                    }
                    //清询单车
                    $scModel->clear($item['sku'], $data['buyer_id'], 0);
                }
            }
            //添加附件询单
            $inquiryAttachModel = new InquiryAttachModel();
            if ($res['code'] == 1 && isset($data['files_attach']) && !empty($data['files_attach'])) {
                foreach ($data['files_attach'] as $item) {
                    $item['inquiry_id'] = $res['data']['id'];
                    $item['created_by'] = $data['buyer_id'];
                    $resAttach = $inquiryAttachModel->addData($item);
                    if (!$resAttach || $resAttach['code'] != 1) {

                        $this->rollback();
                        return false;
                    }
                }
            }
            //添加询单联系人信息
            $inquiryContactModel = new InquiryContactModel();
            if ($res['code'] == 1 && isset($data['arr_contact']) && !empty($data['arr_contact'])) {
                $data['arr_contact']['inquiry_id'] = $res['data']['id'];
                $data['arr_contact']['created_by'] = $data['buyer_id'];
                $resContact = $inquiryContactModel->addData($data['arr_contact']);
                if (!$resContact || $resContact['code'] != 1) {
                    $this->rollback();
                    return false;
                }
            }
            $this->commit();
            return $res['data']['id'];
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * 根据条件获取查询条件.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getCondition($condition = []) {
        $where = [];
//        switch ($condition['status']) {
//            case'unpaid':
//                $where['status'] = ['notin', ['QUOTE_SENT', 'CONFIRM']];
//                break;
//            case'payment_part':
//                $where['status'] = ['in', ['QUOTE_SENT', 'CONFIRM']];
//                break;
//            case'payment_completed':
//                $where['status'] = ['in', ['QUOTE_SENT', 'CONFIRM']];
//                break;
//            default :
//                break;
//        }

        switch ($condition['status']) {
            case'waiting_for_quotation':
                $where['quote_status'] = ['notin', ['QUOTED', 'COMPLETED']];
                break;
            case'quotation_finished':
                $where['quote_status'] = ['in', ['QUOTED', 'COMPLETED']];
                break;
            default :
                break;
        }

        if (!empty($condition['term'])) {
            $where['trade_terms_bn'] = $condition['term'];    //贸易术语简称
        }
        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];  //流程编码
        }
        if (!empty($condition['buyer_name'])) {
            $where['buyer_name'] = $condition['buyer_name'];  //客户名称
        }
        if (!empty($condition['buyer_id'])) {
            $where['buyer_id'] = $condition['buyer_id'];  //客户名称
        } else {
            jsonReturn('', -104, '用户ID不能为空!');
        }

        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = $condition['agent_id']; //市场经办人
        }
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];  //项目经理
        }
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = array(
                array('egt', date('Y-m-d 0:0:0', strtotime($condition['start_time']))),
                array('elt', date('Y-m-d 23:59:59', strtotime($condition['end_time'])))
            );
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态

        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);

        $count = $this->where($where)->count('id');

        return $count > 0 ? $count : 0;
    }

    /**
     * 获取列表.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        $where = $this->getCondition($condition);

        $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

        try {
            $count = $this->where($where)->count('id');

            $list = $this->where($where)->page($page, $pagesize)->order('id desc')->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
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

    /**
     * 获取详情信息.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } elseif (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有id或 serial_no!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if ($info) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $info;
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

    /**
     * 添加数据.
     * @return mix
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        $data = $this->create($condition);
        if (empty($data['est_delivery_date']) || !strtotime($data['est_delivery_date'])) {
            unset($data['est_delivery_date']);
        }
        if (!empty($condition['buyer_code'])) {
            $data['buyer_code'] = $condition['buyer_code'];
        }
//        if (!empty($condition['buyer_account_id'])) {
//            $data['buyer_account_id'] = $condition['buyer_account_id'];
//        } else {
//            $results['code'] = '-103';
//            $results['message'] = '没有采购商工作人!';
//            return $results;
//        }
        if (!empty($condition['serial_no'])) {
            $data['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }
        if (!empty($condition['buyer_id'])) {
            $data['buyer_id'] = $condition['buyer_id'];
        } else {
            $data['buyer_id'] = 0;
//            $results['code'] = '-103';
//            $results['message'] = '没有客户ID!';
//            return $results;
        }
        if (!empty($condition['country_bn'])) {
            $data['country_bn'] = $condition['country_bn'];
        } else {

            $results['code'] = '-103';
            $results['message'] = '没有国家简称!';
            return $results;
        }
        //根据客户查询市场负责人
        $bagentModel = new BuyerAgentModel();
        $agentIds = $bagentModel->getAgentIdByBuyerId($condition['buyer_id']);

        if ($agentIds) {
            $data['agent_id'] = $agentIds[0]['agent_id'];
            $data['now_agent_id'] = $agentIds[0]['agent_id'];
        } elseif ($data['country_bn']) {
            $agentIds = (new CountryMemberModel())->getAgentIdByCountryBn($data['country_bn']);

            $data['agent_id'] = $agentIds[0]['agent_id'];
            $data['now_agent_id'] = $agentIds[0]['agent_id'];
        }
        $data['status'] = 'DRAFT';
        $data['quote_status'] = 'NOT_QUOTED';
        $data['created_at'] = $this->getTime();
        $data['updated_at'] = $this->getTime();

        try {

            $insert_data = $this->create($data);
            $id = $this->add($insert_data);

            $data['id'] = $id;
            if ($id) {

                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $data;
            } else {

                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }


            return $results;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL);
            Log::write($e->getMessage() . PHP_EOL);
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s', time());
    }

}
