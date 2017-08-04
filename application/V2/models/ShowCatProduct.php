<?php

/**
 * 展示分类与产品映射
 * User: linkai
 * Date: 2017/6/15
 * Time: 19:24
 */
class ShowCatProductModel extends PublicModel {
    protected $dbName = 'erui2_goods'; //数据库名称
    protected $tableName = 'show_cat_product'; //数据表表名
    //状态
    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除

    const STATUS_ONSHELF = 'Y';    //上架
    const STATUS_UNSHELF = 'N';    //未上架

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'show_cat_product';

        parent::__construct();
    }

    /**
     * 根据展示分类编号查询sku
     * @param string $show_cat_no 展示分类编号
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示多少条
     * @return array|bool
     */
    public function getspusByCatNo($CatNo = '', $lang = '') {
      if (empty($spu))
        return [];
      try {
        $return = array(
            'cat_no' => $spu,
        );
        $where = ['cat_no' => $CatNo, 'status' => 'VALID'];
        $result = $this->field('spu')
                        ->where($where)->select();
        if ($result) {
          return $result;
        } else {
          return [];
        }
      } catch (Exception $ex) {
        LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
        LOG::write($ex->getMessage(), LOG::ERR);
        return [];
      }
    }

    /**
     * 产品上架
     * @param array $spu spu编码 必填
     * @param string $lang 语言  必填
     * @param array $cat_no 展示分类  选填
     *
     * @example: onShelf(array('000001'),'en',array('001','002'));
     */
    public function onShelf($spu='', $lang= '', $cat_no=[]){
        if(empty($lang)) {
            jsonReturn('',ErrorMsg::WRONG_LANG);
        }

        if(empty($spu)){
            jsonReturn('',ErrorMsg::WRONG_SPU);
        }

        $userInfo = getLoinInfo();

        $data = [];
        try{
            if(is_array($spu)){
                $product = new ProductModel();
                foreach($spu as $item){
                    /**
                     * 当没有选择展示分类时根据物料分类查询所有展示分类
                     */
                    if(empty($cat_no)) {
                        /**
                         * 根据spu获取物料分类
                         */
                        $spuInfo = $product->findByCondition(array('spu'=>$item,'lang'=>$lang),'material_cat_no');
                        $mcat_no = $spuInfo[0]['material_cat_no'];

                        /**
                         * 根据物料分类获取展示分类
                         */
                        $showCatProduct = new ShowMaterialCatModel();
                        $cat_no_tmp = $showCatProduct->findByCondition(array('material_cat_no'=>$mcat_no),'show_cat_no');
                    }else{
                        $cat_no_tmp = $cat_no;
                    }

                    if(empty($cat_no_tmp)) {    //当没有对应的展示分类时退出当前循环执行下一条spu
                        continue;
                    }

                    foreach($cat_no_tmp as $r){
                        $data_tmp = [];
                        $data_tmp['spu'] = $item;
                        $data_tmp['lang'] = $lang;
                        $data_tmp['onshelf_flag'] = self::STATUS_ONSHELF;
                        $data_tmp['status'] = self::STATUS_VALID;    //这里上架默认状态是有效的，按常规说应该是审核。
                        $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                        $data_tmp['created_at'] = date('Y-m-d H:i:s',time());
                        if(is_array($r)){
                            $data_tmp['cat_no'] = $r['show_cat_no'];
                        }else{
                            $data_tmp['cat_no'] = $r;
                        }
                        $data[] = $data_tmp;
                    }
                }
            }

            if(empty($data)) {
                return false;
            }else {
                $result = $this->addAll($data);
                return $result ? true : false;
            }
        }catch(Exception $e){
            return false;
        }
    }

    /**
     * 下架
     * @param array $spu spu编码 必填
     * @param string $lang 语言 选填
     * @param string $cat_no 展示分类  选填
     * @return bool
     *
     * @example: downShelf(array('000001'));    #下架000001
     *            downShelf(array('000001'),'zh');    #下架000001的中文
     *            downShelf(array('000001'),'zh',array('0011','0022'));    #下架展示分类为0011，0022下000001为中文的
     */
    public function downShelf($spu = '', $lang='', $cat_no=''){
        if(empty($spu) || !is_array($spu)) {
            jsonReturn('',ErrorMsg::WRONG_SPU);
        }

        $where = array(
            'spu'=>array('in', $spu),
        );

        if(!empty($lang)) {
            $where['lang'] = $lang;
        }

        if(!empty($cat_no) && is_array($cat_no)) {
            $where['cat_no'] = array('in', $cat_no);
        }

        try{
            $result = $this->where($where)->delete();
            return $result ? true : false;
        }catch (Exception $e){
            return false;
        }
    }


}
