<?php
/**
 * Name: Productline
 * Desc: 产品线管理
 * User: zhngyuliang
 * Date: 2017/7/19
 * Time: 11:20
 */
class ProductlineController extends PublicController {
    public function __init() {
        parent::__init();
    }

    //返回新的产品线编码
    public function getLineNoAction(){
        $productline = new ProductLineModel();
        $lineno = $productline->getLineNo();

        return $lineno;
    }

    //产品线列表
    public function getListAction(){
        $productline = new ProductLineModel();
        $productlinecat = new ProductLineCatModel();
        $createcondition = $this->put_data;

        $results = $productline->getlist($createcondition);

        foreach($results['data'] as $key=>$val){
            $catcount = $productlinecat->getCount($val);
            if($catcount>0){
                $results['data'][$key]['cat_select'] = '已选择';
            }else{
                $results['data'][$key]['cat_select'] = '未选择';
            }
        }

        $this->jsonReturn($results);
    }

    //产品线详情
    public function getInfoAction(){
        $productline = new ProductLineModel();
        $productlinecat = new ProductLineCatModel();
        $createcondition = $this->put_data;

        $results = $productline->getInfo($createcondition);
        $materialcat = $productlinecat->getlist($results['data']);
        $catlist = [];
        foreach($materialcat as $val){
            $catlist[] = $val['cat_no'];
        }
        $results['data']['material_cat'] = implode(',',$catlist);

        $this->jsonReturn($results);
    }

    //添加产品线
    public function createAction(){
        $productline = new ProductLineModel();
        $productlinecat = new ProductLineCatModel();
        $createcondition = $this->put_data;

        $productline->startTrans();
        $results = $productline->addData($createcondition);
        if($results['code']==1){
            if(!empty($createcondition['material_cat'])){

                $catid = $productlinecat->addData($createcondition);

                if($catid){
                    $productline->commit();
                }else{
                    $productline->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '添加失败!';
                }
            }else{
                $productline->commit();
            }
        }else{
            $productline->rollback();
            $results['code'] = '-101';
            $results['message'] = '添加失败!';
        }

        $this->jsonReturn($results);
    }

    //修改产品线信息
    public function updateAction(){
        $productline = new ProductLineModel();
        $productlinecat = new ProductLineCatModel();
        $createcondition =  $this->put_data;

        $productline->startTrans();
        $results = $productline->updateData($createcondition);
        if($results['code']==1){
            if(!empty($createcondition['material_cat'])){

                $productlinecat->deleteDataAll($createcondition);
                $catid = $productlinecat->addData($createcondition);

                if($catid){
                    $productline->commit();
                }else{
                    $productline->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '添加失败!';
                }
            }else{
                $productline->commit();
            }
        }else{
            $productline->rollback();
            $results['code'] = '-101';
            $results['message'] = '添加失败!';
        }

        $this->jsonReturn($results);
    }

    //删除产品线
    public function deleteLine(){
        $productline = new ProductLineModel();
        $createcondition =  $this->put_data;

        $results = $productline->deleteData($createcondition);

        $this->jsonReturn($results);
    }

    //添加和修改产品线负责人
    public function createUserNoAction(){
        $productline = new ProductLineModel();
        $createcondition =  $this->put_data;

        $results = $productline->updateData($createcondition);

        $this->jsonReturn($results);
    }

    //产品线报价人分组列表
    public function getLinebidderAction(){
        $productlinebidder = new ProductLinebidderModel();
        $createcondition =  $this->put_data;

        $results = $productlinebidder->getList($createcondition);

        $this->jsonReturn($results);
    }

    //添加产品线报价人分组
    public function createLinebidderAction(){
        $productlinebidder = new ProductLinebidderModel();
        $createcondition =  $this->put_data;

        $results = $productlinebidder->addData($createcondition);

        $this->jsonReturn($results);
    }
}