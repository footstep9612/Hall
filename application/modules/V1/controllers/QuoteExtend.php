<?php
/**
 * @desc 报价单扩展控制器
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
    

}