<?php

/**
 * name: ShowCatGoods
 * desc: 展示分类与商品映射
 * User: zhangyuliang
 * Date: 2017/7/21
 * Time: 16:58
 */
class ShowCatGoodsModel extends PublicModel {

    protected $dbName = 'erui2_goods'; //数据库名称
    protected $tableName = 'show_cat_goods'; //数据表表名

    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除

    const STATUS_ONSHELF = 'Y';    //上架
    const STATUS_UNSHELF = 'N';    //未上架

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据spu上架sku
     * @param array $data_spu  spu上架信息
     * @author link
     */
    public function onShelf($data_spu=[]) {
        if(empty($data_spu)) {
            return false;
        }

        $data = [];
        $goodsModel = new GoodsModel();
        $sku_temp = [];
        $userInfo = getLoinInfo();
        foreach($data_spu as $item){
            /**
             * 根据spu lang获取sku
             */
            if(!isset($sku_temp[$item['lang'].'_'.$item['spu']])){
                $result = $goodsModel->field('sku')->where(array('spu'=>$item['spu'],'lang'=>$item['lang']))->select();
                if(empty($result)) {
                    continue;
                }else{
                    $sku_temp[$item['lang'].'_'.$item['spu']] = $result;
                }
            }
            foreach($sku_temp[$item['lang'].'_'.$item['spu']] as $r) {
                $data_temp =[];
                $data_temp['lang'] = $item['lang'];
                $data_temp['cat_no'] = $item['cat_no'];
                $data_temp['spu'] = $item['spu'];
                $data_temp['sku'] = $r['sku'];
                $data_temp['onshelf_flag'] = self::STATUS_ONSHELF;
                $data_temp['status'] = self::STATUS_VALID;
                $data_temp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                $data_temp['created_at'] = date('Y-m-d H:i:s',time());
                $data[] = $data_temp;
            }
        }
        try{
            return $this->addAll($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 下架
     * @param array $spu spu编码 必填
     * @param string $lang 语言 选填
     * @param string $cat_no 展示分类  选填
     * @return bool
     * @author link
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

        /**
     * 商品上架添加数据
     * @param array $condition
     * @return array
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        if (empty($condition['lang'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少lang!';
        }
        if (empty($condition['spu'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少SPU!';
        }
        if (empty($condition['skus'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少SKU!';
        }
        if (empty($condition['cat_no'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少显示分类!';
        }
        if (empty($condition['onshelf_flag'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少上架状态!';
        }
        if (empty($condition['created_by'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少添加人!';
        }

        $showcat = explode(',', $condition['cat_no']);
        $linecat = [];
        foreach ($showcat as $val) {
            foreach ($condition['skus'] as $sku) {
                $test['lang'] = $condition['lang'];
                $test['spu'] = $condition['spu'];
                $test['sku'] = $sku['sku'];
                $test['cat_no'] = $val;
                $test['status'] = 'VALID';
                $test['onshelf_flag'] = strtoupper($condition['onshelf_flag']);
                $test['created_by'] = $condition['created_by'];
                $test['created_at'] = $this->getTime();
                $linecat[] = $test;
            }
        }
        try {
            $id = $this->addAll($linecat);
            if (isset($id)) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 产品下架删除数据
     * @param array $condition
     * @return array
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if (!empty($condition['lang'])) {
            $where['lang'] = $condition['lang'];
        } else {
            $results['code'] = '-101';
            $results['message'] = '缺少lang!';
        }
        if (empty($condition['spu'])) {
            $where['spu'] = $condition['spu'];
        } else {
            $results['code'] = '-101';
            $results['message'] = '缺少SPU!';
        }

        try {
            $id = $this->where($where)->delete();
            if (isset($id)) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s', time());
    }

    /*
     * 根据SPUS 获取产品展示分类信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es 
     * @return mix  展示分类信息列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getshow_catsbyskus($skus, $lang = 'en') {
        try {
            if ($skus && is_array($skus)) {
                $show_catgoods = $this->alias('scp')
                        ->join('erui2_goods.show_cat sc on scp.cat_no=sc.cat_no', 'left')
                        ->field('scp.cat_no,scp.spu')
                        ->where(['scp.sku' => ['in', $skus],
                            'scp.status' => 'VALID',
                            'sc.status' => 'VALID',
                            'sc.lang' => $lang,
                            'sc.id>0',
                        ])
                        ->select();
            } else {
                return [];
            }
            $ret = [];
            $show_cat_nos = [];
            foreach ($show_catgoods as $item) {              
                $show_cat_nos[] = $item['cat_no'];
            }
            $show_cat_model = new ShowCatModel();
            $scats = $show_cat_model->getshow_cats($show_cat_nos, $lang);

            foreach ($show_catgoods as $item) {
                $show_cat_no = $item['cat_no'];
                if (isset($scats[$show_cat_no])) {
                    $ret[$item['sku']][$show_cat_no] = $scats[$show_cat_no];
                    $ret[$item['sku']][$show_cat_no]['onshelf_flag'] = $item['onshelf_flag'];
                }
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
