<?php
/**
 * name: QuoteFinal.php
 * desc: 市场报价单控制器
 * User: zhangyuliang
 * Date: 2017/6/30
 * Time: 14:35
 */
class QuoteFinalController extends PublicController {

    public function init() {
        parent::init();
        $this->inquiryModel = new InquiryModel();
        $this->inquiryItemModel = new InquiryItemModel();
        $this->inquiryAttachModel = new InquiryAttachModel();
        $this->inquiryItemAttachModel = new InquiryItemAttachModel();
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->quoteAttachModel = new QuoteAttachModel();
        $this->quoteItemAttachModel = new QuoteItemAttachModel();
        $this->finalQuoteModel = new FinalQuoteModel();
        $this->finalQuoteItemModel = new FinalQuoteItemModel();
        $this->finalQuoteAttachModel = new FinalQuoteAttachModel();
        $this->finalQuoteItemAttachModel = new FinalQuoteItemAttachModel();
        $this->exchangeRateModel = new ExchangeRateModel();
        $this->userModel = new UserModel();
        $this->goodsPriceHisModel = new GoodsPriceHisModel();
    }

    /**
     * @desc 创建报价单
     * @author liujf 2017-06-24
     * @return mix
     */
    public function createFinalQuoteAction() {
        $condition = $this->put_data;

        $serial_no_arr = explode(',', $condition['quote_no']);

        $whereQuote = $where = array('serial_no' => array('in', $serial_no_arr));

        $whereQuote['quote_status'] = 'NOT_QUOTED';

        $inquiryList = $this->inquiryModel->where($where)->select();

        $quoteList = $correspond = array();

        $user = $this->getUserInfo();

        $time = time();

        foreach ($inquiryList as $inquiry) {
            $quote['serial_no'] = $this->getQuoteSerialNo(); // 报价单流水号
            $quote['quote_no'] = $this->getQuoteNo(); // 报价单号
            $quote['inquiry_no'] = $inquiry['inquiry_no'];
            $quote['quote_lang'] = 'zh';
            $quote['trade_terms'] = $inquiry['trade_terms'];
            $quote['payment_received_days'] = '';
            $quote['exw_delivery_period'] = '';
            $quote['period_of_validity'] = '';
            $quote['logi_quote_status'] = 'NOT_QUOTED';
            $quote['biz_quote_status'] = 'NOT_QUOTED';
            $quote['quote_status'] = 'NOT_QUOTED';
            $quote['quoter'] = $user['name']; //获取当前用户信息
            $quote['quoter_email'] = $user['email']; //获取当前用户信息
            $quote['quote_at'] = $time;
            $quote['created_by'] = $user['name'];
            $quote['created_at'] = $time;

            $correspond[$inquiry['serial_no']] = $quote['quote_no']; //询单流水号和报价单号的对应
            $quoteList[] = $quote;
        }

        if ($this->quoteModel->addAll($quoteList)) {
            $this->createQuoteItem($where, $correspond);
            $this->createQuoteAttach($where, $correspond);
            $this->createQuoteItemAttach($where, $correspond);

            $this->jsonReturn(true);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 创建报价单项目
     * @author liujf 2017-06-24
     * @param array $where 报价单号查询条件
     * @param array $correspond 询单号和报价单号的对应
     * @return json
     */
    private function createQuoteItem($where, $correspond) {

        $quoteItemList = $quoteItem = array();

        $inquiryItemList = $this->inquiryItemModel->where($where)->select();

        foreach ($inquiryItemList as $inquiryItem) {
            $quoteItem['quote_no'] = $correspond[$inquiryItem['serial_no']];
            $quoteItem['inquiry_sku'] = $inquiryItem['sku'];
            $quoteItem['inquiry_item_id'] = $inquiryItem['id'];
            $quoteItem['buyer_sku'] = '';
            $quoteItem['quote_sku'] = '';
            $quoteItem['name_en'] = $inquiryItem['name_en'];
            $quoteItem['name_cn'] = $inquiryItem['name_cn'];
            $quoteItem['quote_model'] = $inquiryItem['model'];
            $quoteItem['quote_spec'] = $inquiryItem['spec'];
            $quoteItem['quote_brand'] = $inquiryItem['brand'];
            $quoteItem['quote_quantity'] = $inquiryItem['quantity'];
            $quoteItem['quote_unit'] = $inquiryItem['unit'];
            $quoteItem['inquiry_desc'] = $inquiryItem['description'];
            $quoteItem['status'] = 'ONGOING';
            $quoteItem['created_at'] = time();

            $quoteItemList[] = $quoteItem;

        }

        return $this->quoteItemModel->addAll($quoteItemList);
    }

    /**
     * @desc 创建报价单附件
     * @author liujf 2017-06-24
     */
    private function createQuoteAttach($where, $correspond) {

        $quoteAttachList = $quoteAttach = array();

        $inquiryAttachList = $this->inquiryAttachModel->where($where)->select();

        foreach ($inquiryAttachList as $inquiryAttach) {
            $quoteAttach['quote_no'] = $correspond[$inquiryAttach['serial_no']];
            $quoteAttach['attach_group'] = $inquiryAttach['attach_group'];
            $quoteAttach['attach_type'] = $inquiryAttach['attach_type'];
            $quoteAttach['attach_name'] = $inquiryAttach['attach_name'];
            $quoteAttach['attach_url'] = $inquiryAttach['attach_url'];

            $quoteAttachList[] = $quoteAttach;
        }

        return $this->quoteAttachModel->addAll($quoteAttachList);
    }

    /**
     * @desc 创建报价单项目附件
     * @author liujf 2017-06-24
     */
    private function createQuoteItemAttach($where, $correspond) {

        $quoteItemAttachList = $quoteItemAttach = array();

        $inquiryItemAttachList = $this->inquiryItemAttachModel->where($where)->select();

        foreach ($inquiryItemAttachList as $inquiryItemAttach) {
            $quoteItemAttach['quote_no'] = $correspond[$inquiryItemAttach['serial_no']];
            $quoteItemAttach['quote_sku'] = $inquiryItemAttach['sku'];
            $quoteItemAttach['attach_type'] = $inquiryItemAttach['attach_type'];
            $quoteItemAttach['attach_name'] = $inquiryItemAttach['attach_name'];
            $quoteItemAttach['attach_url'] = $inquiryItemAttach['attach_url'];

            $quoteItemAttachList[] = $quoteItemAttach;
        }

        return $this->quoteItemAttachModel->addAll($quoteItemAttachList);
    }

    /**
     * @desc 市场获取可修改报价列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteListAction() {

        $condition = $this->put_data;

        $data = $this->finalQuoteModel->getList($condition);

        if ($data) {
            $res['code'] = 1;
            $res['message'] = '成功!';
            $res['data'] = $data;
            $res['count'] = $this->finalQuoteModel->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价详情接口
     * @author liujf 2017-06-28
     * @return json
     */
    public function getFinalQuoteDetailApiAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteModel->getDetail($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场修改报价接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function updateFinalQuoteAction() {
        $finalQuote = $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {
            $where['quote_no'] = $condition['quote_no'];

            $user = $this->getUserInfo();

            $quote = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->find();

            $inquiry = $this->inquiryModel->where(array('inquiry_no' => $quote['inquiry_no']))->find();

            $calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);

            $finalQuote['total_weight'] = $calculateQuoteInfo['$totalWeight'];
            $finalQuote['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
            $finalQuote['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
            $exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
            $finalQuote['total_exw_price'] = $exw['total'];
            $finalQuote['quoter'] = $inquiry['agent'];
            $finalQuote['quoter_email'] = $inquiry['agent_email'];
            $finalQuote['quote_at'] = time();

            $res = $this->finalQuoteModel->update($where,$finalQuote);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场获取报价附件列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteAttachListAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteAttachModel->getAttachList($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场添加报价附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function addFinalQuoteAttachApiAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {

            $res = $this->finalQuoteAttachModel->addAttach($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场删除报价附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function delFinalQuoteAttachAction() {
        $condition = $this->put_data;

        if (!empty($condition['id'])) {

            $res = $this->finalQuoteAttachModel->where(array('id' => $condition['id']))->delete();

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价SKU列表接口
     * @author liujf 2017-06-26
     * @return json
     */
    public function getFinalQuoteItemListAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {
            if (!empty($condition['currentPage']) && !empty($condition['pageSize'])) {
                $data = $this->finalQuoteItemModel
                    ->alias('a')
                    ->join("quote_item b ON a.id = b.id", 'LEFT')
                    ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                    ->where(array('a.quote_no' => $condition['quote_no']))->page($condition['currentPage'], $condition['pageSize'])->select();
            } else {
                $data = $this->finalQuoteItemModel
                    ->alias('a')
                    ->join("quote_item b ON a.id = b.id", 'LEFT')
                    ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                    ->where(array('a.quote_no' => $condition['quote_no']))->select();
            }

            if ($data) {
                $res['code'] = 1;
                $res['message'] = '成功!';
                $res['data'] = $data;
                $res['count'] = $this->finalQuoteItemModel->getCount($condition);
                $this->jsonReturn($res);
            } else {
                $this->jsonReturn(false);
            }
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场获取报价SKU详情接口
     * @author liujf 2017-06-28
     * @return json
     */
    public function getFinalQuoteItemDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['id'])) {

            $res = $this->finalQuoteItemModel
                ->alias('a')
                ->join("quote_item b ON a.id = b.id", 'LEFT')
                ->field("a.*,b.exw_unit_price AS quote_exw_unit_price,b.exw_unit_price AS q_exw_unit_price,b.quote_unit_price AS q_quote_unit_price")
                ->where(array('a.id' => $condition['id']))->find();

            $this->jsonReturn($res);
        }
    }

    /**
     * @desc 市场修改报价SKU接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function uptateFinalQuoteItemApiAction() {
        $finalQuoteItem = $condition = $this->put_data;

        if (!empty($condition['id'])) {
            $finalQuote = $this->finalQuoteModel->getDetail($condition);

            $finalQuoteItem['quote_quantity'] = $condition['quote_quantity'];
            $finalQuoteItem['total_purchase_price'] = round($condition['purchase_price'] * $finalQuoteItem['quote_quantity'], 8);

            $exchangeRate = $this->getRateUSD($condition['purchase_cur']);

            if ($finalQuote['gross_profit_rate'] != '') {
                $finalQuoteItem['exw_unit_price'] = round($condition['purchase_price'] * $finalQuote['gross_profit_rate'] / $exchangeRate, 8);
                $finalQuoteItem['total_exw_price'] = $finalQuoteItem['exw_unit_price'] * $finalQuoteItem['quote_quantity'];
            }
            $finalQuoteItem['exw_cur'] = 'USD';

            if ($finalQuote['total_quote_price'] != '') {
                $data = array('total_quote_price' => $finalQuote['total_quote_price'],
                    'total_exw_price' => $finalQuote['total_exw_price'],
                    'exw_unit_price' => $finalQuoteItem['exw_unit_price']
                );
                $quoteArr = quoteUnitPrice($data);
                $finalQuoteItem['quote_unit_price'] = $quoteArr['quote_unit_price'];
                $finalQuoteItem['total_quote_price'] = $quoteArr['quote_unit_price'] * $finalQuoteItem['quote_quantity'];
            }

            $finalQuoteItem['quote_cur'] = 'USD';
            $finalQuoteItem['weight_unit'] = 'kg';
            $finalQuoteItem['size_unit'] = 'm^3';

            $res = $this->finalQuoteItemModel->updateItem($finalQuoteItem);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场删除报价SKU接口
     * @author liujf 2017-06-26
     * @return json
     */
    public function deleteFinalQuoteItemAction() {
        $condition = $this->put_data;

        if (!empty($condition['id'])) {
            $res = $this->quoteItemModel->delItem($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 市场获取报价SKU附件列表接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function getFinalQuoteItemAttachListAction() {
        $condition = $this->put_data;

        $res = $this->finalQuoteItemAttachModel->getAttachList($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 市场添加报价SKU附件接口
     * @author liujf 2017-06-27
     * @return json
     */
    public function addFinalQuoteItemAttachAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {

            $res = $this->finalQuoteItemAttachModel->addAttach($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }

    }

    /**
     * @desc 市场删除报价SKU附件接口Z
     * @author liujf 2017-06-27
     * @return json
     */
    public function deleteFinalQuoteItemAttachAction() {
        $condition = $this->put_data;

        if (!empty($condition['id'])) {

            $res = $this->finalQuoteItemAttachModel->delAttach();

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取报价计算后的数据
     * @author liujf 2017-06-20
     * @param array $condition 条件参数
     * @return array
     */
    private function getCalculateQuoteInfo($condition) {
        $quoteItemList = $this->quoteItemModel->where(array('quote_no' => $condition['quote_no']))->select();

        $exchangeRate = $this->getRateUSD($condition['purchase_cur']);

        $totalWeight = 0;
        $totalPurchasePrice	= 0;
        foreach ($quoteItemList as $quoteItem) {
            $totalWeight += $quoteItem['unit_weight'];
            $itemRate = $this->exchangeRateModel->where(array('currency1' => $quoteItem['purchase_cur'], 'currency2' => $condition['purchase_cur']))->field('rate')->find();

            $exwData[] = array('busyer_unit_price' => $quoteItem['purchase_price'] * $exchangeRate, 'num' => $quoteItem['quote_quantity']);
            $totalPurchasePrice += $quoteItem['total_purchase_price'] * $itemRate['rate'];
        }

        return array('totalWeight' => $totalWeight, 'totalPurchasePrice' => $totalPurchasePrice, 'exchangeRate' => $exchangeRate, 'exwData' => $exwData);
    }

    /**
     * @desc 获取币种兑美元汇率
     * @author liujf 2017-06-20
     * @return float
     */
    private function getRateUSD($cur) {
        $exchangeRate = $this->exchangeRateModel->where(array('currency1' => $cur, 'currency2' => 'USD'))->field('rate')->find();

        return $exchangeRate['rate'];
    }

    /**
     * @desc 处理报价相关审核接口
     * @author liujf 2017-06-20
     * @return json
     */
    public function examineApiAction() {
        $condition = $this->put_data;

        if (!empty($condition['quote_no'])) {
            $data = $this->getExamine($condition);

            $res = $this->quoteModel->where(array('quote_no' => $condition['quote_no']))->save($data);

            if ($condition['examine_type'] == 'quote') $this->afterExamine($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }
}