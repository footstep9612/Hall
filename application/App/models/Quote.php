<?php

/**
 * @desc   QuoteModel
 * @Author 买买提
 */
class QuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'quote';

    const INQUIRY_DRAFT = 'DRAFT'; //新建询单
    const INQUIRY_BIZ_DISPATCHING = 'BIZ_DISPATCHING'; //事业部分单员
    const INQUIRY_CC_DISPATCHING = 'CC_DISPATCHING'; //易瑞客户中心
    const INQUIRY_BIZ_QUOTING = 'BIZ_QUOTING'; //事业部报价
    const INQUIRY_LOGI_DISPATCHING = 'LOGI_DISPATCHING'; //物流分单员
    const INQUIRY_LOGI_QUOTING = 'LOGI_QUOTING'; //物流报价
    const INQUIRY_LOGI_APPROVING = 'LOGI_APPROVING'; //物流审核
    const INQUIRY_BIZ_APPROVING = 'BIZ_APPROVING'; //事业部核算
    const INQUIRY_MARKET_APPROVING = 'MARKET_APPROVING'; //事业部审核
    const INQUIRY_MARKET_CONFIRMING = 'MARKET_CONFIRMING'; //市场确认
    const INQUIRY_QUOTE_SENT = 'QUOTE_SENT'; //报价单已发出
    const INQUIRY_INQUIRY_CLOSED = 'INQUIRY_CLOSED'; //报价关闭
    const QUOTE_NOT_QUOTED = 'NOT_QUOTED'; //未报价
    const QUOTE_ONGOING = 'ONGOING'; //报价中
    const QUOTE_QUOTED = 'QUOTED'; //已报价
    const QUOTE_COMPLETED = 'COMPLETED'; //已完成

    public function __construct() {
        parent::__construct();
    }

    public function getQuoteIdByInQuiryId($inquiry_id) {
        return $this->where(['inquiry_id' => $inquiry_id])->getField('id');
    }

    /**
     * 获取综合报价信息
     * @param array $condition    条件
     * @param string $field    筛选字段
     * @return array
     */
    public function getGeneralInfo(array $condition, $field = "*") {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * @param array $condition    条件
     * @param array $data    数据
     * @return array|bool
     */
    public function updateGeneralInfo(array $condition, $data) {

        try {
            $this->where($condition)->save($this->create($data));
            //处理计算相关逻辑
            $this->calculate($condition);
            return true;
        } catch (Exception $exception) {
            return [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ];
        }
    }

    /**
     * 处理所有计算相关逻辑
     * @param $condition    条件
     * @return bool
     */
    private function calculate($condition) {

        $quoteItemModel = new QuoteItemModel();
        $exchangeRateModel = new ExchangeRateModel();

        /*
          |--------------------------------------------------------------------------
          | 计算商务报出EXW单价         计算公式 : EXW单价=采购单价*毛利率/汇率
          |--------------------------------------------------------------------------
         */
        $quoteInfo = $this->where($condition)->field('id,gross_profit_rate,exchange_rate')->find();
        $gross_profit_rate = $quoteInfo['gross_profit_rate']; //毛利率

        $quoteItemIds = $quoteItemModel->where($condition)->field('id,purchase_unit_price,purchase_price_cur_bn,reason_for_no_quote')->select();

        if (!empty($quoteItemIds)) {
            foreach ($quoteItemIds as $key => $value) {
                if (empty($value['reason_for_no_quote']) && !empty($value['purchase_unit_price'])) {
                    $exchange_rate = $exchangeRateModel->where(['cur_bn2' => $value['purchase_price_cur_bn'], 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $exw_unit_price = $value['purchase_unit_price'] * $gross_profit_rate / $exchange_rate;
                    $exw_unit_price = sprintf("%.8f", $exw_unit_price);
                    $quoteItemModel->where(['id' => $value['id']])->save([
                        'exw_unit_price' => $exw_unit_price
                    ]);
                }
            }
        }

        /*
          |--------------------------------------------------------------------------
          | 计算商务报出EXW总价        计算公式 : EXW总价=EXW单价*条数*数量
          |--------------------------------------------------------------------------
         */
        $quoteItemExwUnitPrices = $quoteItemModel->where($condition)->field('exw_unit_price,quote_qty,gross_weight_kg')->select();

        $total_exw_price = [];
        foreach ($quoteItemExwUnitPrices as $price) {
            $total_exw_price[] = $price['exw_unit_price'] * $price['quote_qty'];
        }
        $total_exw_price = array_sum($total_exw_price);

        $total_gross_weight_kg = [];
        foreach ($quoteItemExwUnitPrices as $price) {
            $total_gross_weight_kg[] = $price['gross_weight_kg'];
        }
        $total_gross_weight_kg = array_sum($total_gross_weight_kg);

        $this->where($condition)->save([
            //总重
            'total_weight' => $total_gross_weight_kg,
            //exw合计
            'total_exw_price' => $total_exw_price
        ]);

        /*
          |--------------------------------------------------------------------------
          | 采购合计          计算公式 : 采购总价=采购单价*条数
          |--------------------------------------------------------------------------
         */
        $totalPurchase = [];
        $quoteItemsData = $quoteItemModel->where($condition)->field('purchase_unit_price,purchase_price_cur_bn,quote_qty')->select();
        foreach ($quoteItemsData as $quote => $item) {
            switch ($item['purchase_price_cur_bn']) {
                case 'EUR' :
                    $rate = $exchangeRateModel->where(['cur_bn2' => 'EUR', 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] / $rate;
                    break;
                case 'USD' :
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'];
                    break;
                case 'CNY' :
                    $rate = $exchangeRateModel->where(['cur_bn2' => 'CNY', 'cur_bn1' => 'USD'])->order('created_at DESC')->getField('rate');
                    $totalPurchase[] = $item['purchase_unit_price'] * $item['quote_qty'] / $rate;
                    break;
            }
        }

        return $this->where($condition)->save(['total_purchase' => array_sum($totalPurchase)]);
    }

    /**
     * @param array $condition
     * @return array
     */
    public function rejectToBiz(array $condition) {

        $this->startTrans();
        $quoteResult = $this->where($condition)->save(['status' => self::INQUIRY_BIZ_DISPATCHING]);

        $inquiry = new InquiryModel();
        $inquiry->startTrans();
        $inquiryResult = $inquiry->where([
                    'id' => $condition['inquiry_id']
                ])->save([
            'status' => self::INQUIRY_BIZ_DISPATCHING,
            'quote_status' => self::QUOTE_NOT_QUOTED
        ]);

        if ($quoteResult && $inquiryResult) {
            $this->commit();
            $inquiry->commit();
            return ['code' => '1', 'message' => '退回成功!'];
        } else {
            $this->rollback();
            $inquiry->rollback();
            return ['code' => '-104', 'message' => '不能重复退回!'];
        }
    }

    /**
     * 提交物流分单员
     * @param $request 数据
     * @param $user 操作用户id
     * @return array
     */
    public function sendLogisticsHandler($request, $user) {

        $inquiry = new InquiryModel();
        $inquiry->startTrans();

        $org = new OrgModel();
        $orgList = $org->field('id')->where(['org_node' => ['in', ['lg', 'elg']]])->select();
        $orgId = [];
        foreach ($orgList as $org) {
            $orgId[] = $org['id'];
        }

        $inquiryResult = $inquiry->where(['id' => $request['inquiry_id']])->save([
            'status' => self::INQUIRY_LOGI_DISPATCHING,
            'logi_org_id' => $orgId[0],
            'now_agent_id' => $inquiry->getRoleUserId([$orgId[0]], $inquiry::logiIssueMainRole, ['in', ['lg', 'elg']])
        ]);

        $this->startTrans();
        $quoteResult = $this->where(['inquiry_id' => $request['inquiry_id']])->save(['status' => self::INQUIRY_LOGI_DISPATCHING]);


        if ($inquiryResult && $quoteResult) {

            //给物流表创建一条记录
            $quoteLogiFeeModel = new QuoteLogiFeeModel();
            //防止重复提交
            $hasFlag = $quoteLogiFeeModel->where(['inquiry_id' => $request['inquiry_id']])->find();

            if (!$hasFlag) {

                $quoteInfo = $this->where(['inquiry_id' => $request['inquiry_id']])->field('id,premium_rate')->find();

                $quoteLogiFeeModel->add($quoteLogiFeeModel->create([
                            'quote_id' => $quoteInfo['id'],
                            'inquiry_id' => $request['inquiry_id'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'created_by' => $user['id'],
                            'premium_rate' => $quoteInfo['premium_rate']
                ]));

                //给物流报价单项形成记录
                $quoteItemModel = new QuoteItemModel();
                $quoteItemIds = $quoteItemModel->where(['quote_id' => $quoteInfo['id'], 'deleted_flag' => 'N'])->getField('id', true);

                $quoteItemLogiModel = new QuoteItemLogiModel();
                foreach ($quoteItemIds as $quoteItemId) {
                    $quoteItemLogiModel->add($quoteItemLogiModel->create([
                                'inquiry_id' => $request['inquiry_id'],
                                'quote_id' => $quoteInfo['id'],
                                'quote_item_id' => $quoteItemId,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => $user['id']
                    ]));
                }
            }

            $inquiry->commit();
            $this->commit();

            return ['code' => '1', 'message' => '提交成功!'];
        } else {

            $inquiry->rollback();
            $this->rollback();

            return ['code' => '-104', 'message' => '不能重复提交!'];
        }
    }

}
