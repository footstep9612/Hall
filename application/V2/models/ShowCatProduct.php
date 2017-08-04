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

    //  public function __construct() {
    //    //动态读取配置中的数据库配置   便于后期维护
    //    $config_obj = Yaf_Registry::get("config");
    //    $config_db = $config_obj->database->config->goods->toArray();
    //    $this->dbName = $config_db['name'];
    //    $this->tablePrefix = $config_db['tablePrefix'];
    //    $this->tableName = 'show_cat_product';
    //
  //    parent::__construct();
    //  }

    /**
     * 根据展示分类编号查询sku
     * @param string $show_cat_no 展示分类编号
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示多少条
     * @return array|bool
     */
    public function getSkuByCat($show_cat_no = '', $lang = '', $current_no = 1, $pagesize = 10) {
        if (empty($show_cat_no))
            return false;

        $goods = new GoodsModel();
        $field = 'g.spu,g.show_name,g.sku,g.model';
        $condition = array(
            $this->getTableName() . '.status' => self::STATUS_VALID,
            $this->getTableName() . '.cat_no' => $show_cat_no,
            'g.status' => $goods::STATUS_VALID,
        );
        $condition['g.lang'] = $lang;
        try {
            $return = array(
                'count' => 0,
                'current_no' => $current_no,
                'pagesize' => $pagesize
            );
            $count = $this->field($field)->join($goods->getTableName() . ' g ON ' . $this->getTableName() . '.spu=g.spu', 'LEFT')->where($condition)->count();
            $result = $this->field($field)->join($goods->getTableName() . ' g ON ' . $this->getTableName() . '.spu=g.spu', 'LEFT')->where($condition)->page($current_no, $pagesize)->select();
            if ($result) {
                $return['count'] = $count;
                $return['data'] = $result;
            }
            return $return;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据展示分类编号查询sku
     * @param string $show_cat_no 展示分类编号
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示多少条
     * @return array|bool
     */
    public function getShowCatnosBySpu($spu = '', $lang = '') {
        if (empty($spu))
            return [];
        try {
            $return = array(
                'spu' => $spu,
            );
            $where = ['spu' => $spu, 'status' => 'VALID'];
            $result = $this->field('cat_no')
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
     * 产品上架添加数据
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
        if (empty($condition['cat_no'])) {
            $results['code'] = '-101';
            $results['message'] = '缺少显示分类!';
        }
//      if(empty($condition['show_name'])){
//        $results['code'] = '-101';
//        $results['message'] = '缺少展示名称!';
//      }
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
            $test['lang'] = $condition['lang'];
            $test['spu'] = $condition['spu'];
            $test['cat_no'] = $val;
//        $test['show_name'] = $condition['show_name'];
            $test['status'] = 'VALID';
            $test['onshelf_flag'] = strtoupper($condition['onshelf_flag']);
            $test['created_by'] = $condition['created_by'];
            $test['created_at'] = $this->getTime();
            $linecat[] = $test;
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

    public function getshow_catsbyspus($spus, $lang = 'en') {
        try {
            if ($spus && is_array($spus)) {
                $show_cat_products = $this->table('erui2_goods.show_cat_product scp')
                        ->join('erui2_goods.show_cat sc on scp.cat_no=sc.cat_no', 'left')
                        ->field('scp.cat_no,scp.spu')
                        ->where(['scp.spu' => ['in', $spus],
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
            foreach ($show_cat_products as $item) {

                $ret[$item['spu']] = $item['cat_no'];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
