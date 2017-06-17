<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */
class GoodsAttrModel extends PublicModel
{
    //protected $dbName = 'erui_goods'; //数据库名称
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取商品属性值
     * @param null $where string 条件
     * @return mixed
     */
    public function WhereList($where)
    {
        $result = $this->field('attr_group, attr_no, attr_name, attr_value, attr_value_type, goods_flag, logistics_flag, hs_flag, spec_flag, required_flag, search_flag, sort_order, status')
                       ->where($where)
                       ->select();
        return $result;
    }
    /**
     * 编辑商品属性查询
     * @param null $where string 条件
     * @return mixed
     */
    public function getAttrBySku($where, $lang)
    {
        $where['lang'] = $lang;
        $result = $this->field('id, spu, attr_group, attr_no, attr_name, attr_value, attr_value_type, sort_order, status')
                       ->where($where)
                       ->select();//return $result;exit;
        if($result){
            $res = array();
            foreach($result as $val){
                /* 属性分组: 适用范围scope、技术参数tech、执行标准exe、产品优势advantage、图标ico、产品图片images、附件attach,其他　*/
                switch($val['attr_group']){
                    case 'scope':
                        $group = 'scope';
                        break;
                    case 'tech':
                        $group = 'tech';
                        break;
                    case 'exe':
                        $group = 'exe';
                        break;
                    case 'advantage':
                        $group = 'advantage';
                        break;
                    case 'ico':
                        $group = 'ico';
                        break;
                    case 'images':
                        $group = 'images';
                        break;
                    case 'attach':
                        $group = 'attach';
                        break;
                    default:
                        $group = 'other';
                        break;
                }
                $res[$lang][$group][] = $val;
            }
            $result = $res;
        }
        if($result){
            return $result;
        } else {
            return array();
        }
    }

    /**
     * 根据条件sku详情和
     * @param null $where string 条件
     * @return mixed
     */
    public function getInfoBySku($where, $lang)
    {
        $where['lang'] = $lang;
        $result = $this->field('qrcode, name, show_name, model, description, purchase_price1, purchase_price2, purchase_price_cur, purchase_unit, created_by, created_at')
            ->where($where)
            ->select();
        if($result){
            return $result;
        } else {
            return array();
        }
    }

    /**
     * 添加数据
     * @param $data
     * @return mixed
     */

    public function CreateInfo($data){
        $sta = $this->add($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 修改数据
     * @param $where
     * @param $data
     * @return bool
     */

    public function UpdatedInfo($where, $data)
    {
        $sta = $this->where($where)
                     ->save($data);
        if($sta){
            return true;
        }else{
            return false;
        }
    }


}