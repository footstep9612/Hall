<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/6/28
 * Time: 9:54
 */
class LogiPeriodModel extends Model{
    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 't_logi_period';

    const STATUS_VALID = 'VALID';

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getList($lang='',$to_country='',$from_country='',$warehouse=''){
        if(empty($lang) || empty($to_country)){
            return array();
        }

        $countryModel = new CountryModel();
        $cityModel = new CityModel();
        //库中中国状态暂为无效
        $from_country = $from_country ? $from_country : $countryModel->getCountryByBn('China',$lang);
        //city库中暂无东营,暂时写死以为效果
        $warehouse = '东营';$warehouse ? $warehouse : $cityModel->getCityByBn('Dongying',$lang);

        $condition = array(
            'status' => self::STATUS_VALID,
            'lang' =>$lang,
            'to_country' => $to_country,
            'from_country' => $from_country,
            'warehouse'=>$warehouse
        );
        if(redisHashExist('LogiPeriod',md5(json_encode($condition)))){
            return json_decode(redisHashGet('LogiPeriod',md5(json_encode($condition))),true);
        }
        try{
            $field = 'lang,logi_no,trade_terms,trans_mode,warehouse,from_country,from_port,to_country,clearance_loc,to_port,packing_period_min,packing_period_max,collecting_period_min,collecting_period_max,declare_period_min,declare_period_max,loading_period_min,loading_period_max,int_trans_period_min,int_trans_period_max,logi_notes,period_min,period_max,description';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if($result){
                foreach($result as $item){
                    $data[$item['trade_terms']][] = $item;
                }
                redisHashSet('LogiPeriod',md5(json_encode($condition)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }

    /**
     *
     */

}