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
        $warehouse = $warehouse ? $warehouse : $cityModel->getCityByBn('Dongying',$lang);

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
     * 根据条件获取物流时效信息
     */
    public function getInfo($field,$where){
        if(empty($field) || empty($where))
            return array();

        if(redisHashExist('LogiPeriod',md5(json_encode($where)))){
            return json_decode(redisHashGet('LogiPeriod',md5(json_encode($where))),true);
        }
        try{
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if($result){
                $data = $result;
                redisHashSet('LogiPeriod',md5(json_encode($where)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }


    /**
     * 根据贸易术语，运输方式获取起运国
     * @param string $trade_terms
     * @param string $trans_mode
     * @param string $lang
     * @return array|mixed
     */
    public function getFCountry($trade_terms='',$trans_mode='',$lang=''){
        if(empty($trade_terms) || empty($trans_mode))
            return array();

        $countryModel = new CountryModel();
        $t_country = $countryModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable.'.trade_terms'=>$trade_terms,
            $thistable.'.trans_mode'=>$trans_mode,
            $thistable.'.lang'=>$lang,
            $thistable.'.status'=>self::STATUS_VALID
        );

        if(redisHashExist('Country',md5(json_encode($where)))){
           return json_decode(redisHashGet('Country',md5(json_encode($where))),true);
        }

        $field = "$t_country.bn,$t_country.name";
        try{
            $result = $this->field($field)->group("$t_country.bn")->join($t_country . " On $thistable.from_country = $t_country.bn AND $thistable.lang =$t_country.lang", 'LEFT')->where($where)->select();
            $data = array();
            if($result){
                $data = $result;
                redisHashSet('Country',md5(json_encode($where)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国获取港口城市
     * @param string $trade_terms
     * @param string $trans_mode
     * @param $from_country
     * @param string $lang
     */
    public function getFPort($trade_terms='',$trans_mode='',$from_country='',$lang=''){
        if(empty($trade_terms) || empty($trans_mode) || empty($from_country))
            return array();

        $portModel = new PortModel();
        $t_port = $portModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable.'.trade_terms'=>$trade_terms,
            $thistable.'.trans_mode'=>$trans_mode,
            $thistable.'.from_country'=>$from_country,
            $thistable.'.lang'=>$lang,
            $thistable.'.status'=>self::STATUS_VALID
        );

        if(redisHashExist('Port',md5(json_encode($where)))){
            return json_decode(redisHashGet('Port',md5(json_encode($where))),true);
        }

        $field = "$t_port.bn,$t_port.name";
        try{
            $result = $this->field($field)->group("$t_port.bn")->join($t_port . " On $thistable.from_port = $t_port.bn AND $thistable.lang =$t_port.lang", 'LEFT')->where($where)->select();
            $data = array();
            if($result){
                $data = $result;
                redisHashSet('Port',md5(json_encode($where)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国，起运港获取目的国
     * @param string $trade_terms
     * @param string $trans_mode
     * @param string $from_country
     * @param string $from_port
     * @param string $lang
     * @return array|mixed
     */
    public function getToCountry($trade_terms='',$trans_mode='',$from_country='',$from_port='',$lang=''){
        if(empty($trade_terms) || empty($trans_mode) || empty($from_country) || empty($from_port))
            return array();

        $countryModel = new CountryModel();
        $t_country = $countryModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable.'.trade_terms'=>$trade_terms,
            $thistable.'.trans_mode'=>$trans_mode,
            $thistable.'.from_country'=>$from_country,
            $thistable.'.from_port'=>$from_port,
            $thistable.'.lang'=>$lang,
            $thistable.'.status'=>self::STATUS_VALID
        );

        if(redisHashExist('Country',md5(json_encode($where)))){
            return json_decode(redisHashGet('Country',md5(json_encode($where))),true);
        }

        $field = "$t_country.bn,$t_country.name";
        try{
            $result = $this->field($field)->group("$t_country.bn")->join($t_country . " On $thistable.to_country = $t_country.bn AND $thistable.lang =$t_country.lang", 'LEFT')->where($where)->select();
            $data = array();
            if($result){
                $data = $result;
                redisHashSet('Country',md5(json_encode($where)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }

    /**
     * 根据贸易术语，运输方式，起运国,起运港口，目的国获取目的港口城市
     * @param string $trade_terms
     * @param string $trans_mode
     * @param $from_country
     * @param string $lang
     */
    public function getToPort($trade_terms='',$trans_mode='',$from_country='',$from_port='',$to_country='',$lang=''){
        if(empty($trade_terms) || empty($trans_mode) || empty($from_country) || empty($from_port) || empty($to_country))
            return array();

        $portModel = new PortModel();
        $t_port = $portModel->getTableName();
        $thistable = $this->getTableName();

        $where = array(
            $thistable.'.trade_terms'=>$trade_terms,
            $thistable.'.trans_mode'=>$trans_mode,
            $thistable.'.from_country'=>$from_country,
            $thistable.'.from_port'=>$from_port,
            $thistable.'.to_country'=>$to_country,
            $thistable.'.lang'=>$lang,
            $thistable.'.status'=>self::STATUS_VALID
        );

        if(redisHashExist('Port',md5(json_encode($where)))){
            return json_decode(redisHashGet('Port',md5(json_encode($where))),true);
        }

        $field = "$t_port.bn,$t_port.name";
        try{
            $result = $this->field($field)->group("$t_port.bn")->join($t_port . " On $thistable.to_port = $t_port.bn AND $thistable.lang =$t_port.lang", 'LEFT')->where($where)->select();
            $data = array();
            if($result){
                $data = $result;
                redisHashSet('Port',md5(json_encode($where)),json_encode($data));
            }
            return $data;
        }catch (Exception $e){
            return array();
        }
    }


}