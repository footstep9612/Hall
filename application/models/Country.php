<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class CountryModel extends PublicModel {

    //put your code here
    protected $dbName='erui_dict';
    protected $tableName = 'country';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data,$limit,$order='id desc') {

        if(!empty($limit)){
            return $this->field('id,lang,bn,name,time_zone,region')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        }else{
            return $this->field('id,lang,bn,name,time_zone,region')
                         ->where($data)
                         ->order($order)
                         ->select();
        }

    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            $row = $this->where($where)
                ->field('id,lang,bn,name,time_zone,region')
                ->find();
            return $row;
        }else{
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if(!empty($where['id'])){
            return $this->where($where)
                ->save(['status' => 'DELETED']);
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data,$where) {
        if(isset($data['lang'])){
            $arr['lang'] = $data['lang'];
        }
        if(isset($data['bn'])){
            $arr['bn'] = $data['bn'];
        }
        if(isset($data['name'])){
            $arr['name'] = $data['name'];
        }
        if(isset($data['time_zone'])){
            $arr['time_zone'] = $data['time_zone'];
        }
        if(isset($data['region'])){
            $arr['region'] = $data['region'];
        }
        if(!empty($where)){
            return $this->where($where)->save($arr);
        }else{
            return false;
        }
    }



    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create= []) {
        if(isset($create['lang'])){
            $arr['lang'] = $create['lang'];
        }
        if(isset($create['bn'])){
            $arr['bn'] = $create['bn'];
        }
        if(isset($create['name'])){
            $arr['name'] = $create['name'];
        }
        if(isset($create['time_zone'])){
            $arr['time_zone'] = $create['time_zone'];
        }
        if(isset($create['region'])){
            $arr['region'] = $create['region'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

    /**
     * 国家地区列表,按首字母分组排序
     * @param  $lang
     * @return array|[]
     * @author klp
     */
    public function getInfoSort($lang)
    {
        $condition = array(
            'lang' => $lang
        );

        /*if(redisExist(md5(json_encode($condition)))){
            $result = json_decode(redisGet(md5(json_encode($condition))),true);
            return $result ? $result : array();
        } else {*/
            $result = $this->field('name')->where($condition)->select();
            if ($result) {
                $data = array();
                foreach ($result as $val) {
                    $sname = $val['name'];
                    $firstChar = $this->getFirstCharter($sname); //取出第一个汉字或者单词的首字母
                    $data[$firstChar][] = $val;//以这个首字母作为key
                }
                ksort($data); //对数据进行ksort排序，以key的值以升序对关联数组进行排序

                //redisSet(md5(json_encode($condition)), $data);
                return $data;
            } else {
                return array();
            }
        //}
    }
    /**
     * 取汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     * @author klp
     */
    public function getFirstCharter($str){
        if(empty($str)){return '';}
        $fchar=ord($str{0});
        if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
        $s1=iconv('UTF-8','gb2312',$str);
        $s2=iconv('gb2312','UTF-8',$s1);
        $s=$s2==$str?$s1:$str;
        $asc=ord($s{0})*256+ord($s{1})-65536;
        if($asc>=-20319&&$asc<=-20284) return 'A';
        if($asc>=-20283&&$asc<=-19776) return 'B';
        if($asc>=-19775&&$asc<=-19219) return 'C';
        if($asc>=-19218&&$asc<=-18711) return 'D';
        if($asc>=-18710&&$asc<=-18527) return 'E';
        if($asc>=-18526&&$asc<=-18240) return 'F';
        if($asc>=-18239&&$asc<=-17923) return 'G';
        if($asc>=-17922&&$asc<=-17418) return 'H';
        if($asc>=-17417&&$asc<=-16475) return 'J';
        if($asc>=-16474&&$asc<=-16213) return 'K';
        if($asc>=-16212&&$asc<=-15641) return 'L';
        if($asc>=-15640&&$asc<=-15166) return 'M';
        if($asc>=-15165&&$asc<=-14923) return 'N';
        if($asc>=-14922&&$asc<=-14915) return 'O';
        if($asc>=-14914&&$asc<=-14631) return 'P';
        if($asc>=-14630&&$asc<=-14150) return 'Q';
        if($asc>=-14149&&$asc<=-14091) return 'R';
        if($asc>=-14090&&$asc<=-13319) return 'S';
        if($asc>=-13318&&$asc<=-12839) return 'T';
        if($asc>=-12838&&$asc<=-12557) return 'W';
        if($asc>=-12556&&$asc<=-11848) return 'X';
        if($asc>=-11847&&$asc<=-11056) return 'Y';
        if($asc>=-11055&&$asc<=-10247) return 'Z';
        return null;
    }

    /**
     * 获取IP地址
     * @author klp
     */
    public function getRealIp()
    {
        if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif ($_SERVER["HTTP_CLIENT_IP"]) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif ($_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = "Unknown";
        }
        return $ip;
    }

    //新浪通过IP地址获取当前地理位置（省份,城市等）的接口   klp
    public function getIpAddress($ip){
        if($ip=="127.0.0.1") jsonReturn('','-1003','当前为本机地址');
        $ipContent   = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip");
        $arr = json_decode($ipContent,true);//解析json
        $country = $arr['country']; //取得国家
        return $country;
    }
    //获取IP地址对应英文国家名称  klp
    public function getName($country)
    {
        $where = array(
            'name' => $country
        );
        $bn = $this->field('bn')->where($where)->find();
        $condition = array(
            'bn' => $bn['bn'],
            'lang' => 'en'
        );
        $nameEn =  $this->field('name')->where($condition)->find();
        if($nameEn){
            return $nameEn;
        } else{
            return false;
        }
    }

    /**
     * 获取国家对应营销区域
     * @author klp
     */
    public function getMarketArea($country,$lang)
    {
        $where = array(
            'name' => $country
        );
        $country_bn = $this->field('bn')->where($where)->find();

        $MarketAreaCountry = new MarketAreaCountryModel();//对应表的营销区域简写bn
        $market_area_bn = $MarketAreaCountry->field('market_area_bn')->where(array('country_bn'=>$country_bn['bn']))->find();
        $MarketArea = new MarketAreaModel();
        $market_area = $MarketArea->field('name')->where(array('bn'=>$market_area_bn['market_area_bn'],'lang'=>$lang))->find();
        if($market_area){
            return $market_area;
        } else{
            return false;
        }
    }
}
