<?php
/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController{
    public function init(){

    }

    /**
     * 展示分类列表
     */
    public function listAction(){
        $lang = $this->getRequest()->getQuery("lang");
        $parent_cat_no = $this->getRequest()->getQuery('parent_cat_no');

        $condition = array();
        if($lang){
            $condition['lang'] = $lang;
        }
        if($parent_cat_no){
            $condition['parent_cat_no'] = $parent_cat_no;
        }

        $showcat = new ShowCatModel();
        $result  = $showcat->getList($condition);
        //这里注意code与message的后期同步
        if($result){
            $result['code'] = 0;
            $result['message'] = '成功';
            $this->jsonReturn($result);
        }else{
            $this->jsonReturn(array('code'=>'400','message'=>'失败'));
        }
        exit;
    }
}