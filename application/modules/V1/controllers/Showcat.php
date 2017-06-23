<?php
/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 11:09
 */
class ShowcatController extends PublicController{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 展示分类列表
     */
    public function listAction(){
        $condition = array();
        if($this->input['lang']){
            $condition['lang'] = $this->input['lang'];
        }
        if($this->input['parent_cat_no']){
            $condition['parent_cat_no'] = $this->input['parent_cat_no'];
        }

        //$showcat = new ShowCatModel();
        //$result  = $showcat->getList($condition);
        $result = array();
        if($result){
            jsonReturn($result);
        }else{
            jsonReturn(array('code'=>'400','message'=>'失败'));
        }
        exit;
    }
}