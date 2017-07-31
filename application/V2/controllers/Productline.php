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
        $materialcat = new MaterialcatModel();
        $createcondition = $this->put_data;

        $results = $productline->getInfo($createcondition);
        $catno = $productlinecat->getlist($results['data']);

        if($catno){
            $catlist = [];
            foreach($catno['data'] as $val){
                $test = $materialcat->getinfo($val['cat_no'],'zh');
                $cat_name = $test['cat_name1'].','.$test['cat_name2'].','.$test['cat_name3'];
                $catlist[] = ['cat_no'=>$val['cat_no'],'cat_name'=>$cat_name];

            }
        }

        $results['data']['material_cat'] = $catlist;

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
                $createcondition['line_no'] = $results['data']['line_no'];

                $catid = $productlinecat->addData($createcondition);

                if($catid['code']==1){
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

                $delcat = $productlinecat->deleteDataAll($createcondition);
                if($delcat['code']==1){
                    $catid = $productlinecat->addData($createcondition);

                    if($catid['code']==1){
                        $productline->commit();
                    }else{
                        $productline->rollback();
                        $results['code'] = '-101';
                        $results['message'] = '添加失败!';
                    }
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
    public function deleteAction(){
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

    //产品线报价人列表
    public function getLinebidderAction(){
        $productlinebidder = new ProductLinebidderModel();
        $createcondition =  $this->put_data;

        $results = $productlinebidder->getList($createcondition);

        $this->jsonReturn($results);
    }

    //添加产品线报价人
    public function createLinebidderAction(){
        $productlinebidder = new ProductLinebidderModel();
        $createcondition =  $this->put_data;

        $results = $productlinebidder->addData($createcondition);

        $this->jsonReturn($results);
    }

    //删除产品线报价人
    public function deleteLinebidderAction(){
        $productlinebidder = new ProductLinebidderModel();
        $createcondition =  $this->put_data;

        $results = $productlinebidder->deleteData($createcondition);

        $this->jsonReturn($results);
    }
}