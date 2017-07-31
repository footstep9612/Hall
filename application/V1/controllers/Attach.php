<?php
/**
 * 附件控制器
 * User: linkai
 * Date: 2017/6/24
 * Time: 15:20
 */
class AttachController extends PublicController{
    private $input;
    public function init(){
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 获取商品附件
     */
    public function bySkuAction(){
        $gAttach = new GoodsAttachModel();
        $attachs = $gAttach->getAttach($this->input);
        if($attachs){
            jsonReturn(array('data'=>$attachs));
        }else{
            json_encode('',400,'');
        }
    }

    /**
     * 获取商品附件
     */
    public function bySpuAction(){
        $pAttach = new ProductAttachModel();
        $attachs = $pAttach->getAttach($this->input);
        if($attachs){
            jsonReturn(array('data'=>$attachs));
        }else{
            json_encode('',400,'');
        }
    }
}