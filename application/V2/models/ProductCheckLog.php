<?php
/**
 * 产品审核日志
 * User: linkai
 * Date: 2017/8/3
 * Time: 11:41
 */
class ProductCheckLogModel extends PublicModel{
    const STATUS_PASS = 'PASS';    //-通过；
    const STATUS_CHANGED = 'TO_BE_CHANGED';    //-通过，但需要修改；
    const STATUS_REJECTED = 'REJECTED';  //-不通过

    /**
     * 构造方法
     * 初始化数据库表
     */
    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'product_check_log';

        parent::__construct();
    }

    /**
     * 添加审核日志
     * @param string $spu
     * @param string $sku
     * @param string $status 审核状态
     * @param string $remarks 审核评语
     * @return int/null
     */
    public function addLog($spu='', $sku='',$lang='', $status='OTHER', $remarks='') {
        if(empty($spu)) {
            return false;
        }

        try{
            $userInfo = getLoinInfo();
            $data = array(
                'spu' => $spu,
                'sku' => $sku,
                'lang' => $lang,
                'status' => $status,
                'remarks' => $remarks,
                'approved_by' => isset($userInfo['id']) ? $userInfo['id'] : '',
                'approved_at' => date('Y-m-d H:i:s',time()),
            );
            return $this->add($data);
        }catch (Exception $e){
            return ;
        }
    }

}