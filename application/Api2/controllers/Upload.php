<?php

/**
  上传Controller
 */
class UploadController extends Yaf_Controller_Abstract {
    /*
      上传地址
     */

    public function portraitAction() {        
        //上传到fastDFS
        $fastdfs = new AliyunOSSClient();
        $ret = $fastdfs->uploadAttach($_FILES['upFile'],'portrait/');

        if (!empty($ret['url'])) {
            $result = array(
                "code" => '1',
                "url" => $ret['url'],
            );
        } else {
            $result = array(
                "code" => '-103',
                "message" => "error"
            );
        }
        echo json_encode($result);
        exit;
    }

}
