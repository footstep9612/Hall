<?php
/**
 * 服务条款内容
 * User: linkai
 * Date: 2017/8/21
 * Time: 10:08
 */
class ServiceitemController extends publicController{
    public function init() {
        parent::init();
    }

    /**
     * 删除id根据
     * @author link 2017-08-21
     */
    public function deleteAction(){
        $id = isset($this->put_data['service_item_id']) ? $this->put_data['service_item_id'] : '';

        if(empty($id)) {
            jsonReturn('');
        }

        $itemMoel = new ServiceItemModel();
        $return = $itemMoel ->deleteById($id);
        if($return!==false){
            jsonReturn($return);
        }else{
            jsonReturn('',ErrorMsg::FAILED);
        }
    }
}
