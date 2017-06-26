<?php
/**
 * name: Inquiry
 * desc: 询价单表
 * User: zhangyuliang
 * Date: 2017/6/16
 * Time: 15:11
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名

    const STATUS_DRAFT = 'DRAFT'; //DRAFT-草稿；
    const STATUS_SENT = 'SENT'; //SENT-已发出；
    const STATUS_CANCELED = 'CANCELED'; //CANCELED-已取消；
    const STATUS_DELETE = 'DELETED'; //DISABLED-删除；
    const STATUS_INVALID = 'INVALID'; //INVALID-无效询价

    const STATUS_NOT_QUOTED = 'NOT_QUOTED'; //NOT_QUOTED-未报价；
    const STATUS_ONGOING = 'ONGOING'; //ONGOING-报价中；
    const STATUS_APPROVING = 'APPROVING'; //APPROVING-待确认；
    const STATUS_APPROVED = 'APPROVED'; //APPROVED-已报价；
    const STATUS_WITHDREW = 'WITHDREW'; //WITHDREW-撤回报价；

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    protected function getcondition($condition = []) {
        $where = [];
        if (isset($condition['serial_no']) && !empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        }
        if (isset($condition['inquiry_no']) && !empty($condition['inquiry_no'])) {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        if (isset($condition['quote_status']) && !empty($condition['quote_status'])) {
            $where['quote_status'] = $condition['quote_status'];
        }
        if (isset($condition['inquiry_region']) && !empty($condition['inquiry_region'])) {
            $where['inquiry_region'] = $condition['inquiry_region'];
        }
        if (isset($condition['inquiry_country']) && !empty($condition['inquiry_country'])) {
            $where['inquiry_country'] = $condition['inquiry_country'];
        }
        if (isset($condition['agent']) && !empty($condition['agent'])) {
            $where['agent'] = $condition['agent'];
        }
        if (isset($condition['customer_id']) && !empty($condition['customer_id'])) {
            $where['customer_id'] = $condition['customer_id'];
        }
        if(isset($condition['start_time']) && isset($condition['end_time']) && !empty($condition['start_time']) && !empty($condition['end_time'])){
            $where['inquiry_time'] = array(
                array('gt',date('Y-m-d H:i:s',strtotime($condition['start_time']))),
                array('lt',date('Y-m-d H:i:s',strtotime($condition['end_time'])))
            );
        }
        $where['inquiry_status'] = isset($condition['inquiry_status'])?$condition['inquiry_status']:self::STATUS_DRAFT;
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        return $this->where($where)
                ->field('id,serial_no,inquiry_no,agent,customer_id,inquiry_name,inquirer,inquiry_time,inquiry_region,inquiry_country,inquiry_lang,project_name,inquiry_status,quote_status,biz_quote_status,logi_quote_status,created_at')
                ->count('id');
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);
        $filed = 'id,serial_no,inquiry_no,agent,customer_id,inquiry_name,inquirer,inquiry_time,inquiry_region,inquiry_country,inquiry_lang,project_name,inquiry_status,quote_status,biz_quote_status,logi_quote_status,created_at';
        $page = isset($condition['page'])?$condition['page']:1;
        $pagesize = isset($condition['countPerPage'])?$condition['countPerPage']:10;

        try {
            if (isset($page) && isset($pagesize)) {
                //$count = $this->getcount($condition);
                return $this->where($where)->field($filed)->select();
                    //->page($page, $pagesize)
                    //->field($filed)
                    //->select();
            } else {
                return $this->where($where)->select();
            }
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * 获取详情信息
     * @param  int $inquiry_no 询单号
     * @return mix
     * @author zhangyuliang
     */
    public function getinfo($condition = []) {
        if(isset($createcondition['serial_no'])){
            $where['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }
        if(isset($createcondition['inquiry_no'])){
            $where['inquiry_no'] = $createcondition['inquiry_no'];
        }else{
            return false;
        }

        try {
            return $this->where($where)->find();
        } catch (Exception $e) {
            return false;
        }

    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {
        $data = $this->create($createcondition);

        if(isset($createcondition['serial_no'])){
            $data['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }
        if(isset($createcondition['inquiry_region'])){
            $data['inquiry_region'] = $createcondition['inquiry_region'];
        }else{
            return false;
        }
        if(isset($createcondition['inquiry_country'])){
            $data['inquiry_country'] = $createcondition['inquiry_country'];
        }else{
            return false;
        }
        $data['inquiry_no'] = isset($createcondition['inquiry_no'])?$createcondition['inquiry_no']:$createcondition['serial_no'];
        $data['inquiry_time'] = isset($createcondition['inquiry_time'])?$createcondition['inquiry_time']:$this->getTime();
        $data['inquiry_lang'] = isset($createcondition['inquiry_lang'])?$createcondition['inquiry_lang']:'en';
        $data['kerui_flag'] = isset($createcondition['kerui_flag'])?$createcondition['kerui_flag']:'N';
        $data['bid_flag'] = isset($createcondition['bid_flag'])?$createcondition['bid_flag']:'N';

        $data['inquiry_status'] = self::STATUS_DRAFT;
        $data['quote_status'] = self::STATUS_NOT_QUOTED;
        $data['biz_quote_status'] = self::STATUS_NOT_QUOTED;
        $data['logi_quote_status'] = self::STATUS_NOT_QUOTED;
        $data['created_at'] = $this->getTime();

        try {
            return $this->add($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($createcondition = []) {
        $data = $this->create($createcondition);
        if(isset($createcondition['serial_no'])){
            $where['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }
        if(isset($createcondition['inquiry_no'])){
            $where['inquiry_no'] = $createcondition['inquiry_no'];
        }else{
            return false;
        }

        try {
            return $this->where($where)->save($data);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($createcondition = []) {
        if(isset($createcondition['serial_no'])){
            $where['serial_no'] = $createcondition['serial_no'];
        }else{
            return false;
        }
        if(isset($createcondition['inquiry_no'])){
            $where['inquiry_no'] = $createcondition['inquiry_no'];
        }else{
            return false;
        }

        try {
            return $this->where($where)->save(['inquiry_status' => 'DELETED']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime(){
        return date('Y-m-d h:i:s',time());
    }
}
