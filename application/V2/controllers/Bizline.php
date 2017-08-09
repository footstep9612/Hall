<?php
/**
 * Name: Bizline
 * Desc: 产品线管理
 * User: 张玉良
 * Date: 2017/8/1
 * Time: 10:00
 */
class BizlineController extends PublicController {
    public function __init() {
        parent::__init();
    }

    //产品线列表
    public function getListAction() {
        $bizline = new BizlineModel();
        $bizlinecat = new BizlineCatModel();
        $bizlinegroup = new BizlineGroupModel();
        $createcondition = $this->put_data;

        $results = $bizline->getlist($createcondition);

        foreach($results['data'] as $key=>$val){
            //查找物料分类是否选择
            $where['bizline_id'] = $val['id'];

            $results['data'][$key]['cat_count'] = $bizlinecat->getCount($where);

            //产品线负责人组名称
            $where['group_role'] = 'BIZLINE_MANAGER';
            $grouplist = $bizlinegroup->getList($where);
            $results['data'][$key]['group_name'] = $grouplist['data'][0]['group_id'];
        }

        $this->jsonReturn($results);
    }

    //产品线详情
    public function getInfoAction() {
        $bizline = new BizlineModel();
        $bizlinecat = new BizlineCatModel();
        $materialcat = new MaterialcatModel();
        $createcondition = $this->put_data;

        $results = $bizline->getInfo($createcondition);
        $where['bizline_id'] = $results['data']['id'];
        $catno = $bizlinecat->getlist($where);

        if($catno){
            $catlist = [];
            foreach($catno['data'] as $val){
                $test = $materialcat->getinfo($val['material_cat_no'],'zh');
                $cat_name = $test['cat_name1'].','.$test['cat_name2'].','.$test['cat_name3'];
                $catlist[] = ['cat_no'=>$val['material_cat_no'],'cat_name'=>$cat_name];

            }
        }

        $results['data']['material_cat'] = $catlist;

        $this->jsonReturn($results);
    }

    //添加产品线
    public function createAction() {
        $bizline = new BizlineModel();
        $bizlinecat = new BizlineCatModel();
        $createcondition = $this->put_data;
        $createcondition['userid'] = $this->user['id'];

        $bizline->startTrans();
        $results = $bizline->addData($createcondition);
        if($results['code'] == 1){
            if(!empty($createcondition['material_cat'])){
                $createcondition['bizline_id'] = $results['data'];

                $catid = $bizlinecat->addData($createcondition);

                if($catid['code']==1){
                    $bizline->commit();
                }else{
                    $bizline->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '添加失败!';
                }
            }else{
                $bizline->commit();
            }
        }else{
            $bizline->rollback();
            $results['code'] = '-101';
            $results['message'] = '添加失败!';
        }

        $this->jsonReturn($results);
    }

    //修改产品线信息
    public function updateAction() {
        $bizline = new BizlineModel();
        $bizlinecat = new BizlineCatModel();
        $createcondition =  $this->put_data;
        $createcondition['userid'] = $this->user['id'];

        $bizline->startTrans();
        $results = $bizline->updateData($createcondition);
        if($results['code'] == 1){
            if(!empty($createcondition['material_cat'])){
                $createcondition['bizline_id'] = $createcondition['id'];

                $delcat = $bizlinecat->deleteBizlineCat($createcondition);
                if($delcat['code'] == 1){
                    $catid = $bizlinecat->addData($createcondition);

                    if($catid['code'] == 1){
                        $bizline->commit();
                    }else{
                        $bizline->rollback();
                        $results['code'] = '-101';
                        $results['message'] = '添加失败!';
                    }
                }else{
                    $bizline->rollback();
                    $results['code'] = '-101';
                    $results['message'] = '添加失败!';
                }
            }else{
                $bizline->commit();
            }
        }else{
            $bizline->rollback();
            $results['code'] = '-101';
            $results['message'] = '添加失败!';
        }

        $this->jsonReturn($results);
    }

    //删除产品线
    public function deleteAction() {
        $bizline = new BizlineModel();
        $createcondition =  $this->put_data;

        $results = $bizline->deleteData($createcondition);

        $this->jsonReturn($results);
    }

    //添加产品线负责人
    public function createManagerAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;
        $createcondition['group_role'] = 'BIZLINE_MANAGER';
        $createcondition['userid'] = $this->user['id'];

        $results = $bizlinegroup->addData($createcondition);

        $this->jsonReturn($results);
    }

    //修改产品线负责人
    public function updateManagerAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;

        $results = $bizlinegroup->updateData($createcondition);

        $this->jsonReturn($results);
    }

    //产品线负责人列表
    public function getManagerAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;
        $createcondition['group_role'] = 'BIZLINE_MANAGER';

        $results = $bizlinegroup->getList($createcondition);

        $this->jsonReturn($results);
    }

    //添加产品线报价人
    public function createQuoterAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;
        $createcondition['group_role'] = 'SKU_QUOTER';
        $createcondition['userid'] = $this->user['id'];

        $results = $bizlinegroup->addData($createcondition);

        $this->jsonReturn($results);
    }

    //修改产品线报价人
    public function updateQuoterAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;
        $createcondition['group_role'] = 'SKU_QUOTER';
        $createcondition['userid'] = $this->user['id'];

        $bizlinegroup->startTrans();
        $results = $bizlinegroup->deleteBizlineGroup($createcondition);

        if($results['code'] == 1){
            $resdata = $bizlinegroup->addData($createcondition);
            if($resdata['code'] == 1){
                $bizlinegroup->commit();
            }else{
                $bizlinegroup->rollback();
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
        }else{
            $bizlinegroup->rollback();
            $results['code'] = '-101';
            $results['message'] = '添加失败!';
        }


        $this->jsonReturn($results);
    }

    //删除产品线报价人
    public function deleteQuoterAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;

        $results = $bizlinegroup->deleteBizlineGroup($createcondition);

        $this->jsonReturn($results);
    }

    //产品线报价人列表
    public function getQuoterAction() {
        $bizlinegroup = new BizlineGroupModel();
        $createcondition =  $this->put_data;
        $createcondition['group_role'] = 'SKU_QUOTER';

        $results = $bizlinegroup->getList($createcondition);

        $this->jsonReturn($results);
    }
}