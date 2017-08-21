<?php
/**
 * 服务条款
 * User: linkai
 * Date: 2017/8/21
 * Time: 9:41
 */
class ServiceTermController extends publicController{
    public function init() {
        parent::init();
    }

    /**
     * 删除id根据
     * @author link 2017-08-21
     */
    public function deleteAction(){
        $id = isset($this->put_data['service_term_id']) ? $this->put_data['service_term_id'] : '';

        if(empty($id)) {
            jsonReturn('');
        }

        $termMoel = new ServiceTermModel();
        $return = $termMoel ->deleteById($id);
        if($return!==false){
            jsonReturn($return);
        }else{
            jsonReturn('',ErrorMsg::FAILED);
        }
    }
}