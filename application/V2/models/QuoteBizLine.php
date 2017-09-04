<?php

/**
 * 产品线报价
 * Class QuoteBizLineModel
 * @author 买买提
 */
class QuoteBizLineModel extends PublicModel {

    /**
     * 数据库名称
     * @var string
     */
    protected $dbName = 'erui2_rfq';

    /**
     * 数据表名称
     * @var string
     */
    protected $tableName = 'quote_bizline';

    /*
     * 询单(项目)状态
     */

    const INQUIRY_DRAFT = 'DRAFT'; //起草
    const INQUIRY_APPROVING_BY_SC = 'APPROVING_BY_SC'; //方案中心审核中
    const INQUIRY_APPROVED_BY_SC = 'APPROVED_BY_SC'; //方案中心已确认
    const INQUIRY_QUOTING_BY_BIZLINE = 'QUOTING_BY_BIZLINE'; //产品线报价中
    const INQUIRY_QUOTED_BY_BIZLINE = 'QUOTED_BY_BIZLINE'; //产品负责人已确认
    const INQUIRY_BZ_QUOTE_REJECTED = 'BZ_QUOTE_REJECTED'; //项目经理驳回产品报价
    const INQUIRY_QUOTING_BY_LOGI = 'QUOTING_BY_LOGI'; //物流报价中
    const INQUIRY_QUOTED_BY_LOGI = 'QUOTED_BY_LOGI'; //物流审核人已确认
    const INQUIRY_LOGI_QUOTE_REJECTED = 'LOGI_QUOTE_REJECTED'; //项目经理驳回物流报价
    const INQUIRY_APPROVED_BY_PM = 'APPROVED_BY_PM'; //项目经理已确认
    const INQUIRY_APPROVING_BY_MARKET = 'APPROVING_BY_MARKET'; //市场主管审核中
    const INQUIRY_APPROVED_BY_MARKET = 'APPROVED_BY_MARKET'; //市场主管已审核
    const INQUIRY_QUOTE_SENT = 'QUOTE_SENT'; //报价单已发出
    const INQUIRY_INQUIRY_CLOSED = 'INQUIRY_CLOSED'; //报价关闭

    /*
     * 报价状态
     */
    const QUOTE_NOT_QUOTED = 'NOT_QUOTED'; //未报价
    const QUOTE_QUOTED = 'QUOTED'; //已报价
    const QUOTE_APPROVED = 'APPROVED'; //已审核
    const QUOTE_REJECTED = 'REJECTED'; //被驳回

    public function __construct() {
        parent::__construct();
    }

    /**
     * 处理退回产品线重新报价逻辑
     * 操作说明:(1)更改询单的状态及询单的产品线报价状态 (2)更改产品线报价的状态(quote_bizine)
     * @param $request
     * @return bool
     */
    public function rejectBizline($request) {

        //(1)更改询单的状态及询单的产品线报价状态
        $inquiry = new InquiryModel();
        //$inquiry->startTrans();
        $updateInquiry = $inquiry->where(['serial_no' => $request['serial_no']])->save([
            'status' => self::INQUIRY_BZ_QUOTE_REJECTED,
            'goods_quote_status' => self::QUOTE_REJECTED
        ]);

        //(2)更改产品线报价的状态(quote_bizine)
        $this->startTrans();
        $bizline_ids = explode(',', $request['bizline_id']);

        foreach ($bizline_ids as $item => $value) {
            $this->where(['bizline_id' => intval($value)])->save(['status' => self::QUOTE_REJECTED]);
        }
        //结果
        if ($updateInquiry) {
            $inquiry->commit();
            $this->commit();
            return ['code' => '1', 'message' => '退回成功!'];
        } else {
            $inquiry->rollback();
            $this->rollback();
            return ['code' => '-104', 'message' => '退回失败!'];
        }
    }

    /**
     * @desc 提交物流报价(项目经理)
     * @param $request 请求
     * @return array 结果
     */
    public function sentLogistics($request, $user) {

        //更改询单(inqury项目)的状态
        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryUpdates = $inquiry->where(['id' => $request['inquiry_id']])->save([
            'status' => self::INQUIRY_QUOTING_BY_LOGI, //物流报价中
            'goods_quote_status' => self::QUOTE_APPROVED //已审核
        ]);

        //修改报价的状态
        $quoteModel = new QuoteModel();
        $quoteID = $quoteModel->where(['inquiry_id' => $request['inquiry_id']])->getField('id');
        $quoteResult = $quoteModel->where(['id' => $quoteID])->save([
            'status' => self::INQUIRY_QUOTING_BY_LOGI
        ]);

        //给物流表创建一条记录
        $quoteLogiFeeModel = new QuoteLogiFeeModel();
        $quoteLogiFeeModel->startTrans();
        $quoteLogiFeeResult = $quoteLogiFeeModel->add($quoteLogiFeeModel->create([
                    'quote_id' => $quoteID,
                    'inquiry_id' => $request['inquiry_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $user
        ]));

        $quoteItemModel = new QuoteItemModel();
        $quoteItemIds = $quoteItemModel->where(['quote_id' => $quoteID])->getField('id', true);

        //给物流报价单项形成记录
        $quoteItemLogiModel = new QuoteItemLogiModel();
        foreach ($quoteItemIds as $quoteItemId) {
            $quoteItemLogiModel->add($quoteItemLogiModel->create([
                        'quote_id' => $quoteID,
                        'quote_item_id' => $quoteItemId,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => $user
            ]));
        }


        if ($inquiryUpdates && $quoteResult && $quoteLogiFeeResult) {
            $inquiry->commit();
            $quoteModel->commit();
            $quoteLogiFeeModel->commit();
            return ['code' => '1', 'message' => '提交成功!'];
        } else {
            $inquiry->rollback();
            $quoteModel->rollback();
            $quoteLogiFeeModel->rollback();
            return ['code' => '-104', 'message' => '提交失败!'];
        }
    }

    /**
     * 根据条件获取所有产品线报价单
     * @param array $param
     *
     * @return array
     */
    public function getQuoteList(array $condition) {

        $where = $this->getQuoteListCondition($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->alias('a')
                        ->join('erui2_rfq.quote b ON a.quote_id = b.id', 'LEFT')
                        ->join('erui2_rfq.inquiry c ON a.inquiry_id = c.id', 'LEFT')
                        ->field('a.id, c.serial_no, c.country_bn, c.buyer_name, c.agent_id, c.pm_id, c.inquiry_time, c.status, b.period_of_validity')
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();
        //p($data);
    }

    public function getQuoteListCondition($condition) {

        $where = [];

        if (!empty($condition['status'])) {
            $where['c.status'] = $condition['status'];
        }

        if (!empty($condition['country_bn'])) {
            $where['c.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }

        if (!empty($condition['inquiry_no'])) {
            $where['c.inquiry_no'] = ['like', '%' . $condition['inquiry_no'] . '%'];
        }

        if (!empty($condition['buyer_name'])) {
            $where['c.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }

        if (!empty($condition['agent_id'])) {
            $where['c.agent_id'] = $condition['agent_id'];
        }

        if (!empty($condition['pm_id'])) {
            $where['c.pm_id'] = $condition['pm_id'];
        }

        if (!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])) {
            $where['c.inquiry_time'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }

        $where['a.deleted_flag'] = 'N';
        //p($where);
        return $where;
    }

    public function getQuoteCount(array $condition) {
        $where = $this->getQuoteListCondition($condition);

        $count = $this->alias('a')
                ->join('erui2_rfq.quote b ON a.quote_id = b.id', 'LEFT')
                ->join('erui2_rfq.inquiry c ON a.inquiry_id = c.id', 'LEFT')
                ->field('a.id, c.serial_no, c.country_bn, c.buyer_name, c.agent_id, c.pm_id, c.inquiry_time, c.status, b.period_of_validity')
                ->where($where)
                ->page($currentPage, $pageSize)
                ->order('a.id DESC')
                ->count('a.id');

        return $count > 0 ? $count : 0;
    }

    /**
     * 根据条件获取报价信息
     * @param $quote_id 报价单id
     * @return mixed 获取的结果
     */
    public function getQuoteInfo($quote_id) {
        return $this->where(['quote_id' => $quote_id])->find();
    }

    /**
     * 产品线负责人暂存报价信息
     */
    public function storageQuote($data, $user) {

        //追加供应商信息
        foreach ($data as $key => $value) {

            //判断价格
            if (!empty($value['purchase_unit_price']) && !is_numeric($value['purchase_unit_price'])){
                return ['code'=>'-104','message'=>'采购单价必须是数字'];
            }
            if (!empty($value['net_weight_kg']) && !is_numeric($value['net_weight_kg'])){
                return ['code'=>'-104','message'=>'净重必须是数字'];
            }
            if (!empty($value['gross_weight_kg']) && !is_numeric($value['gross_weight_kg'])){
                return ['code'=>'-104','message'=>'毛重必须是数字'];
            }
            if (!empty($value['package_size']) && !is_numeric($value['package_size'])){
                return ['code'=>'-104','message'=>'包装体积必须是数字'];
            }

            if (!empty($value['supplier_info'])) {

                $data[$key]['supplier_id'] = $value['supplier_info']['supplier_id'];
                $data[$key]['contact_first_name'] = $value['supplier_info']['first_name'];
                $data[$key]['contact_last_name'] = $value['supplier_info']['last_name'];
                $data[$key]['contact_gender'] = $value['supplier_info']['gender'];
                $data[$key]['contact_email'] = $value['supplier_info']['email'];
                $data[$key]['contact_phone'] = $value['supplier_info']['phone'];
                unset($data[$key]['supplier_info']);
            }

            $data[$key]['updated_by'] = $user;
            $data[$key]['updated_at'] = date('Y-m-d H:i:s');
            $data[$key]['status'] = 'QUOTED'; //报价状态
        }

        //更新信息
        try {
            $quoteItemFormModel = new QuoteItemFormModel();
            foreach ($data as $k => $v) {
                $result = $quoteItemFormModel->save($quoteItemFormModel->create($v));
                if (!$result) {
                    return ['code' => '1', 'message' => '暂存失败!'];
                }
            }

            return ['code' => '1', 'message' => '成功!'];
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 产品线负责人退回产品线报价人重新报价
     */
    public function bizlineManagerRejectQuote($request) {

        //1.更改当前的报价状态为被退回
        $this->startTrans();
        $quoteBizline = $this->where(['quote_id' => $request['quote_id']])->save(['status' => 'REJECTED']);

        //2.更改该报价所属的sku状态为被驳回状态
        $quoteItemFormModel = new QuoteItemFormModel();
        $quoteItemFormModel->startTrans();
        $quoteItemFormResult = $quoteItemFormModel->where(['quote_id' => $request['quote_id']])->save([
            'status' => 'REJECTED'
        ]);

        //记录审核日志
        $inquiryCheckLog = new InquiryCheckLogModel();
        $inquiryCheckLog->startTrans();
        $inquiryCheckLogResult = $inquiryCheckLog->add($inquiryCheckLog->create([
                    'op_id' => $request['user_id'],
                    'inquiry_id' => $request['inquiry_id'],
                    'quote_id' => $request['quote_id'],
                    'category' => 'BIZLINE',
                    'action' => 'APPROVING',
                    'op_note' => $request['op_note'],
                    'op_result' => 'REJECTED',
                    'created_by' => $request['user_id'],
                    'created_at' => date('Y-m-d H:i:s')
        ]));

        if ($quoteBizline && $quoteItemFormResult && $inquiryCheckLogResult) {
            $this->commit();
            $quoteItemFormModel->commit();
            $inquiryCheckLog->commit();
            return ['code' => '1', 'message' => '成功!'];
        } else {
            $this->rollback();
            $quoteItemFormModel->rollback();
            $inquiryCheckLog->rollback();
            return ['code' => '-104', 'message' => '失败!'];
        }
    }

    /**
     * 产品线报价人暂存报价
     * @param $quote_id 报价id
     *
     * @return bool
     */
    public function quoterStorage($quote_id) {
        //TODO 这里添加保存数据的逻辑才行
        return $this->where(['quote_id' => $quote_id])->save(['status' => 'SUBMIT']);
    }

    /**
     * 过滤条件
     * @param array $param
     *
     * @return array
     */
    private function filterParam(array $param) {
        $data = [];
        if (isset($param['quote_id'])) {
            //报价单id
            $data['quote_id'] = $param['quote_id'];
        }

        return $data;
    }

    /**
     * 根据条件获取总数
     * @param $where
     * @return mixed
     */
    private function getTotal($where) {
        return $this->where($where)->count('id');
    }

    /**
     * 产品线报价->产品线报价人->提交产品线负责人审核
     * 操作说明:当前报价单状态改为(........)
     * @param $params
     * @return array
     */
    public function submitToBizlineManager($params) {

        //更新当前的报价单状态为产品线报价
        try {
            if ($this->where(['quote_id' => $params['quote_id']])->save(['status' => 'QUOTED'])) {
                return ['code' => '1', 'message' => '提交成功!'];
            } else {
                return ['code' => '-104', 'message' => '提交失败!'];
            }
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 划分产品线
     * @param $param
     * @return array
     */
    public function setPartitionBizline($param) {
        //先查找询单相关的字段 inquiry_id biz_agent_id
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['serial_no' => $param['serial_no']])
                ->field(['id', 'agent_id'])
                ->find();
        //判断一个quote_id是一个或者是多个
        $quoteItem = explode(',', $param['quote_item_id']);
        $data = [
            'inquiry_id' => $inquiryInfo['id'],
            'biz_agent_id' => $inquiryInfo['agent_id'],
            'bizline_id' => $param['bizline_id'],
            'created_by' => $param['created_by'],
            'created_at' => date('Y-m-d H:i:s'),
            'quote_id' => $param['quote_id']
        ];
        foreach ($quoteItem as $k => $v) {
            $data['quote_id'] = $v;
            $this->add($data);
        }
        return ['code' => '1', 'message' => '成功!'];
    }

    /**
     * 产品线负责人指派报价人
     * @param $request 请求参数
     * @return array 返回结果
     */
    public function assignQuoter($request) {
        $this->select();
        p($this->getLastSql());
        p($request);
        try {
            if ($this->where(['quote_id' => $request['quote_id']])->save(['biz_agent_id' => $request['biz_agent_id']])) {
                return ['code' => '1', 'message' => '指派成功!'];
            } else {
                return ['code' => '-104', 'message' => '指派失败!'];
            }
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * @desc 获取关联列表
     */
    public function getJoinList($condition = []) {

        $where = $this->getJoinWhere($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $quoteModel = new QuoteModel();
        return $quoteModel->alias('a')
                        ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id', 'LEFT')
                        ->field('a.id, b.serial_no, b.country_bn, b.buyer_name, b.agent_id, b.pm_id, b.inquiry_time, b.status, a.period_of_validity')
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();
    }

    /**
     * @desc 获取关联查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-02
     */
    public function getJoinWhere($condition = []) {

        $where = [];

        if (!empty($condition['quote_id'])) {
            $where['a.quote_id'] = $condition['quote_id'];
        }

        if (!empty($condition['status'])) {
            $where['a.status'] = $condition['status'];
        }

        if (!empty($condition['country_bn'])) {
            $where['d.country_bn'] = ['like', '%' . $condition['country_bn'] . '%'];
        }

        if (!empty($condition['inquiry_no'])) {
            $where['d.inquiry_no'] = ['like', '%' . $condition['inquiry_no'] . '%'];
        }

        if (!empty($condition['buyer_name'])) {
            $where['d.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }

        if (!empty($condition['agent_id'])) {
            $where['d.agent_id'] = $condition['agent_id'];
        }

        if (!empty($condition['pm_id'])) {
            $where['d.pm_id'] = $condition['pm_id'];
        }

        if (!empty($condition['start_inquiry_time']) && !empty($condition['end_inquiry_time'])) {
            $where['d.inquiry_time'] = [
                ['egt', $condition['start_inquiry_time']],
                ['elt', $condition['end_inquiry_time'] . ' 23:59:59']
            ];
        }

        $where['a.deleted_flag'] = 'N';

        return $where;
    }

    /**
     * @desc 获取l列表记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-08-07
     */
    public function getListCount($condition = []) {

        $where = $this->getJoinWhere($condition);

        $quoteModel = new QuoteModel();

        $count = $quoteModel->alias('a')
                ->join('erui2_rfq.inquiry b ON a.inquiry_id = b.id', 'LEFT')
                ->field('a.id, b.serial_no, b.country_bn, b.buyer_name, b.agent_id, b.pm_id, b.inquiry_time, b.status, a.period_of_validity')
                ->where($where)
                ->order('a.id DESC')
                ->count('a.id');

        return $count > 0 ? $count : 0;
    }

    public function getPmQuoteList($request) {

        $where = ['a.inquiry_id' => $request['inquiry_id']];

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        $quoteItemModel = new QuoteItemModel();

        return  $quoteItemModel->alias('a')
                        ->join('erui2_rfq.inquiry_item b ON b.id = a.inquiry_item_id')
                        ->field('a.id,a.bizline_id,a.bizline_agent_id,a.sku,b.buyer_goods_no,b.model,b.name,b.name_zh,b.remarks,b.remarks_zh,b.qty,b.unit,b.brand,a.purchase_unit_price,a.purchase_price_cur_bn,a.exw_unit_price,a.quote_unit_price,a.supplier_id,a.remarks quote_remarks,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.delivery_days,a.period_of_validity,a.goods_source,a.stock_loc,a.reason_for_no_quote')
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('a.id DESC')
                        ->select();
        //p($data);
    }

    public function getPmQuoteListCount($request) {

        $where = ['a.inquiry_id' => $request['inquiry_id']];
        $quoteItemModel = new QuoteItemModel();

        $count = $quoteItemModel->alias('a')
            ->join('erui2_rfq.inquiry_item b ON b.id = a.inquiry_item_id')
            ->field('a.id,a.bizline_id,a.bizline_agent_id,a.sku,b.buyer_goods_no,b.model,b.name,b.name_zh,b.remarks,b.remarks_zh,b.qty,b.unit,b.brand,a.purchase_unit_price,a.purchase_price_cur_bn,a.exw_unit_price,a.quote_unit_price,a.supplier_id,a.remarks quote_remarks,a.net_weight_kg,a.gross_weight_kg,a.package_size,a.package_mode,a.delivery_days,a.period_of_validity,a.goods_source,a.stock_loc,a.reason_for_no_quote')
            ->where($where)
            ->count('a.id');
        return $count > 0 ? $count : 0;
    }

    /**
     * 选择报价(产品线负责人)
     * @param $request
     *
     * @return \Model
     */
    public function selectQuote($request) {

        $quoteItemForm = new QuoteItemFormModel();
        return $quoteItemForm->where([
                            'quote_item_id' => $request['quote_item_id'],
                        ])
                        ->field('id,created_by,status,supplier_id,contact_first_name,contact_last_name,contact_phone,purchase_unit_price,period_of_validity')
                        ->select();
    }

}
