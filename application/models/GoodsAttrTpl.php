<?php
/**
* Description of GoodsAttrTplModel
*
 * @author  klp
*/
class GoodsAttrTplModel extends PublicModel
{

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attr_tpl'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID';    //有效的
    const STATUS_INVALID = 'INVALID';    //无效
    const STATUS_DELETED = 'DELETED';    //删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'goods_attr_tpl';

        parent::__construct();
    }

    /**
     * 根据条件获取商品模板属性
     * @param null $where string 条件
     * @return
     */
    public function getlist($type='',$cat_no,$spu,$sku)
    {
        $where = array(
            'attr_type' => ''
        );
        $field = "lang,attr_group,attr_no,attr_name,goods_flag,spec_flag,logi_flag,hs_flag";
        $common = $this->field($field)->where($where)->select();
       if($type == 'CATEGORY'){
           $category = $this->field('attr_no')->where(array('cat_no' => $cat_no, 'attr_type' => 'CATEGORY'))->select();
           $catModel = new AttrModel();
           if($category){
               $groups = $catModel->field($field)->where(array('attr_no' => $category))->select();
               $cate = $groups? $groups : array();
           }
       } elseif($type == 'PRODUCT'){
           $spuModel = new ProductAttrModel();
           $products = $spuModel->field($field)->where(array('spu' => $spu))->select();
           $product = $products ? $products : array();
       } elseif($type == 'GOODS'){
           $skuModel = new GoodsAttrModel();
            $goods = $skuModel->field($field)->where(array('sku' => $sku))->select();
           $good = $goods ? $goods : array();
       }
        $result = array_merge($common,$cate,$product,$good);
        if ($result) {
            //按语言树形结构
            /**
             * 属性分类:一级
             *   goods_flag - 商品属性
             *   spec_flag - 规格型号
             *   logi_flag  - 物流属性
             *   hs_flag  - 申报要素
             *   Others - 其他　
             */
            $ListTpl = array();
            foreach ($result as $item) {
                $group1 = '';
                if ($item['goods_flag'] == 'Y') {
                    $group1 = 'goods_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['logi_flag'] == 'Y') {
                    $group1 = 'logi_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['hs_flag'] == 'Y') {
                    $group1 = 'hs_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['spec_flag'] == 'Y') {
                    $group1 = 'spec_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($group1 == '') {
                    $group1 = 'others';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
            }
            return $ListTpl;
        } else {
            return array();
        }
    }


    /**
     * 获取属性模板
     * @author link 2017-06-22
     * @param $input
     * @return array
     */
    public function getAttrTpl($input){
        $lang = isset($input['lang']) ? strtolower($input['lang']) : (browser_lang() ? browser_lang() : 'en');
        $attrTpl = $common = $categoryAtpl = $productAtpl = $goodsAtpl = [];    //初始化

        //获取公共属性模板
        $common = $this->getCommonAttrTpl($lang);
        if(empty($input)) {
            $attrTpl = $common;
        }elseif(isset($input['sku'])){
            $goodsAtpl = $this->getGoodsAttrTpl($input['sku'],$lang);
            $goodsModel = new GoodsModel();
            $spu = $goodsModel->getSpubySku($input['sku'],$lang);
            if($spu){
                $productAtpl = $this->getProductAttrTpl($spu,$lang);
            }
            $spuModel = new ProductModel();
            $meterial_cat_no = $spuModel->getMcatBySpu($spu,$lang);
            if($meterial_cat_no){
                $categoryAtpl = $this->getCategoryAttrTpl($meterial_cat_no,$lang);
            }
            $attrTpl = array_merge($common,$goodsAtpl,$productAtpl,$categoryAtpl);
        }elseif(isset($input['spu'])){
            $productAtpl = $this->getProductAttrTpl($input['spu'],$lang);
            $spuModel = new ProductModel();
            $meterial_cat_no = $spuModel->getMcatBySpu($input['spu'],$lang);
            if($meterial_cat_no){
                $categoryAtpl = $this->getCategoryAttrTpl($meterial_cat_no,$lang);
            }
            $attrTpl = array_merge($common,$productAtpl,$categoryAtpl);
        }elseif(isset($input['meterial_cat_no'])){
            $categoryAtpl = $this->getCategoryAttrTpl($input['meterial_cat_no'],$lang);
            $attrTpl = array_merge($common,$categoryAtpl);
        }

        //属性分组
        if($attrTpl){
            $data = array();
            foreach($attrTpl as $item){
                if($item['goods_flag']=='Y'){    //商品属性
                    $data['goods_flag'][] = $item;
                }
                if($item['spec_flag']=='Y'){    //规格型号
                    $data['spec_flag'][] = $item;
                }
                if($item['logi_flag']=='Y'){    //物流属性
                    $data['logi_flag'][] = $item;
                }
                if($item['hs_flag']=='Y'){    //申报要素
                    $data['hs_flag'][] = $item;
                }
            }
            return $data;
        }
        return array();
    }

    /**
     * 获取公共属性模板
     * @author link 2017-06-22
     * @param string $lang 语言
     * @return array
     */
    public function getCommonAttrTpl($lang=''){

        //判断redis缓存
        if(redisHashExist('AttrTpl','common_'.$lang)){
            $redisInfo =redisHashGet('AttrTpl','common_'.$lang);
            return json_decode($redisInfo);
        }

        $attrModel = new AttrModel();
        $attrTable = $attrModel->getTableName();
        $thisTable = $this->getTableName();


        try{
            $field = "$thisTable.lang,$thisTable.attr_no,$thisTable.attr_name,$thisTable.goods_flag,$thisTable.spec_flag,$thisTable.logi_flag,$thisTable.hs_flag,$thisTable.required_flag,$thisTable.search_flag,$thisTable.attr_group,$attrTable.input_type,$attrTable.value_type,$attrTable.value_unit,$attrTable.options,$attrTable.input_hint";
            $where = array(
                "$thisTable.attr_type" => '',
                "$thisTable.lang" =>$lang,
                "$thisTable.status" => self::STATUS_VALID,
                "$attrTable.status"=> $attrModel::STATUS_VALID,
                "$attrTable.lang" => $lang,
            );
            $result = $this->field($field)->join($attrTable." ON $thisTable.attr_no = $attrTable.attr_no" , 'LEFT')->where($where)->select();
            if($result){
                //redis缓存  这里后期可以考虑通过队列缓存以减少等待。
                redisHashSet('AttrTpl','common_'.$lang,json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();

    }

    /**
     * 获取分类属性模板
     * @author link 2017-06-22
     * @param string $cat_no    物料分类编号
     * @param string $lang    语言
     * @return array
     */
    public function getCategoryAttrTpl($cat_no,$lang){
        if(empty($cat_no) || empty($lang))
            return array();

        //判断redis缓存
        if(redisHashExist('AttrTpl',$cat_no.'_'.$lang)){
            $redisInfo =redisHashGet('AttrTpl',$cat_no.'_'.$lang);
            return json_decode($redisInfo);
        }

        $attrModel = new AttrModel();
        $attrTable = $attrModel->getTableName();
        $thisTable = $this->getTableName();

        try{
            $field = "$thisTable.lang,$thisTable.attr_no,$thisTable.attr_name,$thisTable.goods_flag,$thisTable.spec_flag,$thisTable.logi_flag,$thisTable.hs_flag,$thisTable.required_flag,$thisTable.search_flag,$thisTable.attr_group,$attrTable.input_type,$attrTable.value_type,$attrTable.value_unit,$attrTable.options,$attrTable.input_hint";
            $where = array(
                "$thisTable.attr_type" => 'CATEGORY',
                "$thisTable.cat_no" =>strtolower($cat_no),
                "$thisTable.lang" =>$lang,
                "$thisTable.status" => self::STATUS_VALID,
                "$attrTable.status"=> $attrModel::STATUS_VALID,
                "$attrTable.lang" => $lang,
            );
            $result = $this->find($field)
                ->join("$attrTable ON $thisTable.attr_no = $attrTable.attr_no",'LEFT')
                ->where($where)->select();
            if($result){
                //redis缓存
                redisHashSet('AttrTpl', $cat_no.'_'.$lang, json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }

    /**
     * 获取产品属性模板
     * @author link 2017-06-22
     * @param string $spu  spu编码
     * @param string $lang  语言
     * @return array
     */
    public function getProductAttrTpl($spu,$lang){
        if(empty($spu) || empty($lang))
            return array();

        //判断redis缓存
        if(redisHashExist('AttrTpl',$spu.'_'.$lang)){
            $redisInfo =redisHashGet('AttrTpl',$spu.'_'.$lang);
            return json_decode($redisInfo);
        }

        $attrModel = new AttrModel();
        $attrTable = $attrModel->getTableName();
        $thisTable = $this->getTableName();

        try{
            $field = "$thisTable.lang,$thisTable.attr_no,$thisTable.attr_name,$thisTable.goods_flag,$thisTable.spec_flag,$thisTable.logi_flag,$thisTable.hs_flag,$thisTable.required_flag,$thisTable.search_flag,$thisTable.attr_group,$attrTable.input_type,$attrTable.value_type,$attrTable.value_unit,$attrTable.options,$attrTable.input_hint";
            $where = array(
                "$thisTable.attr_type" => 'PRODUCT',
                "$thisTable.spu" =>$spu,
                "$thisTable.lang" =>$lang,
                "$thisTable.status" => self::STATUS_VALID,
                "$attrTable.status"=> $attrModel::STATUS_VALID,
                "$attrTable.lang" => $lang,
            );
            $result = $this->find($field)
                ->join("$attrTable ON $thisTable.attr_no = $attrTable.attr_no",'LEFT')
                ->where($where)->select();
            if($result){
                //redis缓存
                redisHashSet('AttrTpl', $spu.'_'.$lang, json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }

    /**
     * 获取商品属性模板
     * @author link 2017-06-22
     * @param string $sku  sku编码
     * @param string $lang  语言
     * @return array
     */
    public function getGoodsAttrTpl($sku,$lang){
        if(empty($sku) || empty($lang))
            return array();

        //判断redis缓存
        if(redisHashExist('AttrTpl',$sku.'_'.$lang)){
            $redisInfo =redisHashGet('AttrTpl',$sku.'_'.$lang);
            return json_decode($redisInfo);
        }

        $attrModel = new AttrModel();
        $attrTable = $attrModel->getTableName();
        $thisTable = $this->getTableName();

        try{
            $field = "$thisTable.lang,$thisTable.attr_no,$thisTable.attr_name,$thisTable.goods_flag,$thisTable.spec_flag,$thisTable.logi_flag,$thisTable.hs_flag,$thisTable.required_flag,$thisTable.search_flag,$thisTable.attr_group,$attrTable.input_type,$attrTable.value_type,$attrTable.value_unit,$attrTable.options,$attrTable.input_hint";
            $where = array(
                "$thisTable.attr_type" => 'GOODS',
                "$thisTable.sku" =>$sku,
                "$thisTable.lang" =>$lang,
                "$thisTable.status" => self::STATUS_VALID,
                "$attrTable.status"=> $attrModel::STATUS_VALID,
                "$attrTable.lang" => $lang,
            );
            $result = $this->find($field)
                ->join("$attrTable ON $thisTable.attr_no = $attrTable.attr_no",'LEFT')
                ->where($where)->select();
            if($result){
                //redis缓存
                redisHashSet('AttrTpl', $sku.'_'.$lang, json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }


}