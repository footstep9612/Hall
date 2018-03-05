<?php
/**
 * 专题
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:43
 */
class SpecialController extends PublicController{
    public function init() {
        $this->token = false;
    }

    /**
     * 专题详情
     */
    public function infoAction(){
        $input = $this->getPut();
        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', 'special_id不能为空');
        }

        $sModel = new SpecialModel();
        $result = $sModel->getInfo(intval($input['special_id']));
        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 推荐商品列表
     */
    public function pglistAction(){
        $input = $this->getPut();
        if (!isset($input['special_id']) || empty($input['special_id'])) {
            jsonReturn('', 'special_id不能为空');
        }
        if (!isset($input['position_id']) || empty($input['position_id'])) {
            jsonReturn('', 'position_id不能为空');
        }
        $spgModel = new SpecialPositionGoodsModel();
        $result = $spgModel->getList(intval($input['special_id']), intval($input['position_id']), isset($input['size']) ? intval($input['size']): 0);
        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
}