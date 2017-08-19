<?php

/**
  上传Controller
 */
class UploadfileController extends Yaf_Controller_Abstract {
    /*
      上传地址
     */

    public function UploadAction() {
        $file = json_decode(file_get_contents("php://input"), true);
        if (empty($file)) {
            return false;
        }
        //上传到fastDFS
        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['upFile']);

        if (!empty($ret['fileId'])) {
            $result = array(
                "code" => '1',
                "url" => $ret['fileId'],
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