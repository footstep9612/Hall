<?php
/**
 * name: Uploafile
 * desc: 上传附件
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 15:49
 */
class UploadfileController extends PublicController{
    public function init() {

    }
    /*
        上传地址
    */
    public function UploadAction(){
        $file = $this->getRequest()->getFiles();
        if(empty($file)){
            return false;
        }
        //上传到fastDFS
        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['upFile']);

        if(!empty($ret['fileId'])){
            $result = array(
                "code" => '1',
                "url" => $ret['fileId'],
                "name" => $ret['file']['name'],
            );
        }else{
            $result = array(
                "code" => '-103',
                "message" => "error"
            );
        }
        echo json_encode($result);
        exit;
    }

}
