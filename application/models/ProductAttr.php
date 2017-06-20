<?php
/**
 * Sｐu属性
 * User: linkai
 * Date: 2017/6/17
 * Time: 15:58
 */
class ProductAttrModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_db_ddl_goods';
    protected $tableName = 'product_attr';

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 根据spu条件获取属性值a
     * @param $data
     * @return mixed
     */

    public function AttrInfoBy($spu='',$lang='')
    {
        if($spu=='')
            return false;
        if($lang=='')
            return false;
        $field = 'attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );
        try{
            $result = $this->field($field)->where($condition)->select();
            if($result){
                return $result;
            } else{
                return false;
            }
        } catch(Exception $e) {
            return false;
        }
    }

    /**k
     * 根据spu获取属性
     * @param string $spu
     * @param string $lang
     * @return array|bool|mixed
     */
    public function getAttrBySpu($spu='',$lang=''){
        if($spu=='')
            return false;

        $field = 'lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'status' => self::STATUS_VALID
        );
        if($lang!=''){
            $condition['lang'] = $lang;
        }
        $result = $this->field($field)->where($condition)->order('created_at DESC')->select();

        if($result){
            //按语言树形结构
            $data = array();
            foreach($result as $item){
                /**
                 * 属性组: 适用范围、技术参数、执行标准、产品优势、图标、产品图片、附件,其他　
                 * !!!!!! 注意：这里的属性组定死了，如果后期改动了，要及时修改ｓｗｉｔｃｈ对应
                 **/
                $group = 'other';
                switch($item['attr_group']){
                    case '适用范围':
                        $group = 'scope';
                        break;
                    case '技术参数':
                        $group = 'tech';
                        break;
                    case '执行标准':
                        $group = 'exe';
                        break;
                    case '产品优势':
                        $group = 'advantage';
                        break;
                    case '图标':
                        $group = 'ico';
                        break;
                    case '产品图片':
                        $group = 'images';
                        break;
                    case '附件':
                        $group = 'attach';
                        break;
                }
                $data[$item['lang']][$group][] = $item;
            }
            $result = $data;
        }
        return $result ? $result : array();
    }
}