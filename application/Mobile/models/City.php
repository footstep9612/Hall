<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/10
 * Time: 10:03
 */
class CityModel extends PublicModel
{

    const STATUS_VALID = 'VALID';    //有效的


    protected $dbName = 'erui_dict';
    protected $tableName = 'city';

    public function __construct($str = '')
    {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author klp
     */
    public function getlist($where, $limit, $order = 'id desc') {

        if (!empty($limit)) {
            return $this->field('id,lang,bn,name,time_zone,region_bn,country_bn,status')
                ->where($where)
                ->limit($limit['page'] . ',' . $limit['num'])
                ->order($order)
                ->select();
        } else {
            return $this->field('id,lang,bn,name,time_zone,region_bn,country_bn,status')
                ->where($where)
                ->order($order)
                ->select();
        }
    }


}