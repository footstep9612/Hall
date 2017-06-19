<?PHP

class PublicController extends Yaf_Controller_Abstract {

	/**
	 * 获取生成的询价单流水号
	 * @author liujf 2017-06-19
	 * @return string $inquirySerialNo 询价单流水号
	 */
	public function getInquirySerialNo() {
		$time = date('Ymd');
		$duration = 3600 * 48;
		$inquirySerialNoCreateTime = redisGet('inquirySerialNoCreateTime') ? : '19700101';
		if ($time > $inquirySerialNoCreateTime) {
			redisSet('inquirySerialNoStep', 0, $duration);
			redisSet('inquirySerialNoCreateTime', $time, $duration);
		}
		$inquirySerialNoStep = redisGet('inquirySerialNoStep') ? : 0;
		$inquirySerialNoStep ++;
		redisSet('inquirySerialNoStep', $inquirySerialNoStep, $duration);
		$inquirySerialNo = $this->createSerialNo('INQ_', $inquirySerialNoStep);
		
		return $inquirySerialNo;
	}
	
	/**
	 * 获取生成的报价单流水号
	 * @author liujf 2017-06-19
	 * @return string $quoteSerialNo 报价单流水号
	 */
	public function getQuoteSerialNo() {
		$time = date('Ymd');
		$duration = 3600 * 48;
		$quoteSerialNoCreateTime = redisGet('quoteSerialNoCreateTime') ? : '19700101';
		if ($time > $quoteSerialNoCreateTime) {
			redisSet('quoteSerialNoStep', 0, $duration);
			redisSet('quoteSerialNoCreateTime', $time, $duration);
		}
		$quoteSerialNoStep = redisGet('quoteSerialNoStep') ? : 0;
		$quoteSerialNoStep ++;
		redisSet('inquirySerialNoStep', $quoteSerialNoStep, $duration);
		$quoteSerialNo = $this->createSerialNo('QUO_', $quoteSerialNoStep);
		
		return $quoteSerialNo;
	}
	
	/**
	 * 生成流水号
	 * @param string $prefix 前缀
	 * @param string $step 需要补零的字符
	 * @author liujf 2017-06-19
	 * @return string $code
	 */
	private function createSerialNo($prefix = '', $step = 1) {
		$time = date('Ymd');
		$pad  = str_pad($step, 5, '0', STR_PAD_LEFT);
		$code = $prefix . $time . '_' . $pad;
		return $code;
	}

}
