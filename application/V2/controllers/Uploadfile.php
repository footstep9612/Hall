<?php

/**
 * name: Uploafile
 * desc: 上传附件
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 15:49
 */
class UploadfileController extends PublicController {

    public function init() {

    }

    /*
      上传地址
     */

    public function UploadAction() {
        $file = $this->getRequest()->getFiles();
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
                "name" => $ret['file']['name'],
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

    public function UploadKindeditorAction() {
        $file = $this->getRequest()->getFiles();

        $max_size = 1000000;

        //检查文件大小

        if (!empty($file['imgFile']['error'])) {
            switch ($file['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }
        $file_name = $file['imgFile']['name'];
        //服务器上临时文件名
        $tmp_name = $file['imgFile']['tmp_name'];
        //文件大小
        $file_size = $file['imgFile']['size'];
        if (!$file_name) {
            $this->alert("请选择文件。");
        }
        if (@is_uploaded_file($tmp_name) === false) {
            $this->alert("上传失败。");
        }
        if ($file_size > $max_size) {
            $this->alert("上传文件大小超过限制。");
        }

        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['imgFile']);

        if (!empty($ret['fileId'])) {
            $fastDFSUrl = Yaf_Application::app()->getConfig()->fastDFSUrl;

            header('Content-type: text/html; charset=UTF-8');
            $json = new Services_JSON();
            echo json_encode(array('error' => 0, 'url' => $fastDFSUrl . $ret['fileId']));
            exit;
        } else {
            $this->alert("上传文件失败。");
        }

        exit;
    }

    function alert($msg) {
        header('Content-type: text/html; charset=UTF-8');
        $json = new Services_JSON();
        echo $json->encode(array('error' => 1, 'message' => $msg));
        exit;
    }

}
