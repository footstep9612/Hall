<?php
/**
 * @desc 报价单控制器
 * @author liujf 2017-06-17
 */
class QuoteController extends PublicController {

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
	 * @desc 新增报价单
	 * @author liujf 2017-06-24
	 * @return mix
	 */
	public function createQuoteAction() {
		$condition = $this->put_data;

		$serial_no_arr = explode(',', $condition['serial_no']);

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
	 * @desc 商务技术获取报价列表接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function getQuoteListAction() {
		$condition = $this->put_data;

		$data = $this->quoteModel->getJoinList($condition);

		if ($data) {
			$res['code'] = 1;
			$res['message'] = '成功!';
			$res['data'] = $data;
			$res['count'] = $this->quoteModel->getJoinCount($condition);
			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}
	}

	/**
	 * @desc 商务技术获取报价详情接口
	 * @author liujf 2017-06-28
	 * @return json
	 */
	public function getQuoteInfoAction() {
		$condition = $this->put_data;

		$res = $this->quoteModel->getJoinDetail($condition);

		$this->jsonReturn($res);
	}

	/**
	 * @desc 商务技术修改报价接口
	 * @author liujf 2017-06-30
	 * @return json
	 */
	public function updateQuoteAction() {
		$quote = $condition = $this->put_data;

		if (!empty($condition['quote_no'])) {

			$user = $this->getUserInfo();

			$calculateQuoteInfo = $this->getCalculateQuoteInfo($condition);
			
			$condition['size_unit'] = 'm^3';
			$condition['weight_unit'] = 'kg';
			$condition['total_exw_cur'] = 'USD';
			$condition['total_quote_cur'] = 'USD';
			$condition['total_logi_fee_cur'] = 'USD';
			$condition['total_bank_fee_cur'] = 'USD';
			$condition['total_insu_fee_cur'] = 'USD';
			$condition['payment_received_days'] = strtotime($condition['payment_received_days']);
    		$condition['exw_delivery_period'] = strtotime($condition['exw_delivery_period']);
    		$condition['period_of_validity'] = strtotime($condition['period_of_validity']);

			$condition['total_weight'] = $calculateQuoteInfo['$totalWeight'];
			$condition['exchange_rate'] = $calculateQuoteInfo['exchangeRate'];
			$condition['total_purchase_price'] = $calculateQuoteInfo['totalPurchasePrice'];
			$exw = exw($calculateQuoteInfo['exwData'], $condition['gross_profit_rate']);
			$condition['total_exw_price'] = $exw['total'];
			$condition['quoter'] = $user['name'];
			$condition['quoter_email'] = $user['email'];
			$condition['quote_at'] = time();

			$where['quote_no'] = $condition['quote_no'];
			
			$res = $this->quoteModel->updateQuote($where, $condition);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术获取报价SKU列表接口
	 * @author liujf 2017-06-26
	 * @return json
	 */
	public function getQuoteItemListAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$data = $this->quoteItemModel->getJoinList($condition);

			if ($data) {
				$res['code'] = 1;
				$res['message'] = '成功!';
				$res['data'] = $data;
				$res['count'] = $this->quoteItemModel->getJoinCount($condition);
				$this->jsonReturn($res);
			} else {
				$this->jsonReturn(false);
			}
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术获取报价SKU详情接口
	 * @author liujf 2017-06-28
	 * @return json
	 */
	public function getQuoteItemDetailAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$res = $this->quoteItemModel->getJoinDetail($condition);

			$this->jsonReturn($res);
		}
	}

	/**
	 * @desc 商务技术添加报价SKU接口
	 * @author liujf 2017-06-26
	 * @return json
	 */
	public function addQuoteItemAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$quote = $this->quoteModel->getDetail($condition);

			$condition['total_purchase_price'] = round($condition['purchase_price'] * $quoteItem['quote_quantity'], 8);
			$exchangeRate = $this->getRateUSD($condition['purchase_cur']);

			if ($quote['gross_profit_rate'] != '') {
				$condition['exw_unit_price'] = round($condition['purchase_price'] * $quote['gross_profit_rate'] / $exchangeRate, 8);
				$condition['total_exw_price'] = $condition['exw_unit_price'] * $condition['quote_quantity'];
			}
			
			$condition['exw_cur'] = 'USD';

			if ($quote['total_quote_price'] != '') {
				$data = array('total_quote_price' => $quote['total_quote_price'],
							  'total_exw_price' => $quote['total_exw_price'],
							  'exw_unit_price' => $condition['exw_unit_price']
				);
				$quoteArr = quoteUnitPrice($data);
				$condition['quote_unit_price'] = $quoteArr['quote_unit_price'];
				$condition['total_quote_price'] = $quoteArr['quote_unit_price'] * $condition['quote_quantity'];
			}
			
			$condition['quote_cur'] = 'USD';
			$condition['weight_unit'] = 'kg';
			$condition['size_unit'] = 'm^3';

			$res = $this->quoteItemModel->addItem($condition);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术修改报价SKU接口
	 * @author liujf 2017-06-26
	 * @return json
	 */
	public function uptateQuoteItemAction() {
		$quoteItem = $condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$quote = $this->quoteModel->getDetail($condition);

			$quoteItem['total_purchase_price'] = round($condition['purchase_price'] * $quoteItem['quote_quantity'], 8);

			$exchangeRate = $this->getRateUSD($condition['purchase_cur']);

			if ($quote['gross_profit_rate'] != '') {
				$quoteItem['exw_unit_price'] = round($condition['purchase_price'] * $quote['gross_profit_rate'] / $exchangeRate, 8);
				$quoteItem['total_exw_price'] = $quoteItem['exw_unit_price'] * $quoteItem['quote_quantity'];
			}

			if ($quote['total_quote_price'] != '') {
				$data = array('total_quote_price' => $quote['total_quote_price'],
						'total_exw_price' => $quote['total_exw_price'],
						'exw_unit_price' => $quoteItem['exw_unit_price']
				);
				$quoteArr = quoteUnitPrice($data);
				$quoteItem['quote_unit_price'] = $quoteArr['quote_unit_price'];
				$quoteItem['total_quote_price'] = $quoteArr['quote_unit_price'] * $quoteItem['quote_quantity'];
			}

			$res = $this->quoteItemModel->updateItem($quoteItem);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术删除报价SKU接口
	 * @author liujf 2017-06-26
	 * @return json
	 */
	public function delQuoteItemAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {
			$res = $this->quoteItemModel->delItem($condition);
			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}
	}

	/**
	 * @desc 商务技术获取报价附件列表接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function getQuoteAttachListAction() {
		$condition = $this->put_data;

		$res = $this->quoteAttachModel->getAttachList($condition);

		$this->jsonReturn($res);
	}

	/**
	 * @desc 商务技术添加报价附件接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function addQuoteAttachAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {

			$res = $this->quoteAttachModel->addAttach($condition);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术删除报价附件接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function delQuoteAttachAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {

			$res = $this->quoteAttachModel->delAttach($condition);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}
	}

	/**
	 * @desc 商务技术获取报价SKU附件列表接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function getQuoteItemAttachListAction() {
		$condition = $this->put_data;

		$res = $this->quoteItemAttachModel->getAttachItemList($condition);

		$this->jsonReturn($res);
	}

	/**
	 * @desc 商务技术添加报价SKU附件接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function addQuoteItemAttachAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {

			$res = $this->quoteItemAttachModel->addAttachItem($condition);

			$this->jsonReturn($res);
		} else {
			$this->jsonReturn(false);
		}

	}

	/**
	 * @desc 商务技术删除报价SKU附件接口
	 * @author liujf 2017-06-27
	 * @return json
	 */
	public function delQuoteItemAttachAction() {
		$condition = $this->put_data;

		if (!empty($condition['quote_no'])) {

			$res = $this->quoteItemAttachModel->delete($condition);

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

	/**
	 * @desc 获取审核数据
	 * @author liujf 2017-06-21
	 * @param array $condition 条件参数
	 * @return array
	 */
	private function getExamine($condition) {
		$data = array();

		switch ($condition['examine_type']) { // 审核类型： logi(物流) 、biz(商务) 、quote(报价)
			case 'logi'  : $data['logi_quote_status'] = $condition['status'];
				break;
			case 'biz'   : $data['biz_quote_status'] = $condition['status'];
				break;
			case 'quote' : $user = $this->getUserInfo();
				$data['quote_status'] = $condition['status'];
				$data['checker'] = $user['name'];
				$data['checker_email'] = $user['email'];
				$data['check_at'] = time();
				$data['check_notes'] = $condition['check_notes'];
		}

		return $data;

	}

	/**
	 * @desc 审核通过后的操作
	 * @author liujf 2017-06-21
	 * @param array $condition 条件参数
	 * @return array
	 */
	private function afterExamine($condition) {

		if ($condition['status'] == 'APPROVED') { // 报价完成
			$quote = $this->quoteModel->getDetail($condition);
			$this->finalQuoteModel->add($quote);

			$quoteItemList = $this->quoteItemModel->getItemList($condition);
			$this->finalQuoteItemModel->addAll($quoteItemList);

			$quoteAttachList = $this->quoteAttachModel->getAttachList($condition);
			$this->finalQuoteAttachModel->addAll($quoteAttachList);

			$quoteItemAttachList = $this->quoteItemAttachModel->getAttachList($condition);
			$this->finalQuoteItemModel->addAll($quoteItemAttachList);

			$this->createGoodsPriceHis($condition);
		}

	}

	/**
	 * @desc 获取当前用户信息
	 * @author liujf 2017-06-26
	 * @return array
	 */
	private function getUserInfo() {
		return $this->userModel->info($this->user['id']);
	}

	/**
	 * @desc 重写jsonReturn方法
	 * @author liujf 2017-06-24
	 */
	public function jsonReturn($data = array(), $type = 'JSON') {
		if ($data) {
			$this->setCode('1');
			$this->setMessage('成功!');
			parent::jsonReturn($data, $type);
		} else {
			$this->setCode('-101');
			$this->setMessage('失败!');
			parent::jsonReturn();
		}
	}

}