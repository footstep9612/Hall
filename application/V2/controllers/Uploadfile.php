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
        $upload_type = $this->getPost('upload_type', '');
        $max_size = $this->getPost('max_size', '20');

        if (empty($file)) {
            return false;
        }
        if ($upload_type && in_array($upload_type, ['spu', 'sku'])) {
            $max_size = 1048576;
            $file_size = $file['upFile']['size'];
            if ($file_size > $max_size) {
                $this->setCode(MSG::FILE_SIZE_ERR_1);
                $this->jsonReturn();
            }
        } elseif (intval($max_size)) {
            $file_size = $file['upFile']['size'];
            if ($file_size > intval($max_size) * 1048576) {
                $this->setCode(MSG::FILE_SIZE_ERR_5);
                $this->setMessage('您上传的文件大于 ' . intval($max_size) . ' M!');
                $this->jsonReturn();
            }
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
        header('Content-Type:application/json; charset=utf-8');
        header('P3P:CP=\'IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT\'');
        header('X-Frame-Options:*');


        $file = $this->getRequest()->getFiles();
        $max_size = 1048576;


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
            $this->alert("您上传的文件大于1M。");
        }

        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['imgFile']);

        if (!empty($ret['fileId'])) {
            $fastDFSUrl = Yaf_Application::app()->getConfig()->fastDFSUrl;

            header('Content-Type:application/json; charset=utf-8');
            header('P3P:CP=CAO PSA OUR');

            echo json_encode(array('error' => 0, 'url' => $fastDFSUrl . $ret['fileId']));
            exit;
        } else {
            $this->alert("上传文件失败。");
        }

        exit;
    }

    function alert($msg) {
        header('Content-Type:application/json; charset=utf-8');
        header('P3P:CP=CAO PSA OUR');
        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }

    /*
     * 删除文件
     */

    public function deletedAction() {
        $file_id = $this->getPut('file_id', '');
        $fdfs = new FastDFS();
        $fdfs->tracker_get_connection();
        $falg = $fdfs->storage_delete_file1($file_id);
        $fdfs->tracker_close_all_connections();
        if ($falg === true) {
            $result = array(
                "code" => '1',
                "message" => "成功!"
            );
        } else {
            $result = array(
                "code" => '-1',
                "message" => "失败!"
            );
        }
        echo json_encode($result);
        exit;
    }

    /*
     * 获取详情
     */

    public function GetFileInfoAction() {
        $file_id = $this->getPut('file_id', '');
        $fdfs = new FastDFS();
        $fdfs->tracker_get_connection();
        $data = $fdfs->get_file_info1($file_id);
        $fdfs->tracker_close_all_connections();
        if ($falg === true) {
            $result = array(
                'data' => $data,
                "code" => '1',
                "message" => "成功!"
            );
        } else {
            $result = array(
                "code" => '-1',
                "message" => "失败!"
            );
        }
        echo json_encode($result);
        exit;
    }

    public function DownFIleToFIleAction() {

        $file_id = $this->getPut('file_id', '');
        $fdfs = new FastDFS();
        $fdfs->tracker_get_connection();
        $falg = $fdfs->storage_download_file_to_file1($file_id, MYPATH . DS . 'public' . DS . 'temp' . DS . date('YmdHis') . '.log');
        $fdfs->tracker_close_all_connections();
        if ($falg === true) {
            $result = array(
                "code" => '1',
                "message" => "成功!"
            );
        } else {
            $result = array(
                "code" => '-1',
                "message" => "失败!"
            );
        }
        echo json_encode($result);
        exit;
    }

    public function GetMetadataAction() {

        $file_id = $this->getPut('file_id', '');
        $fdfs = new FastDFS();
        $fdfs->tracker_get_connection();
        $falg = $fdfs->storage_get_metadata1($file_id);
        $fdfs->tracker_close_all_connections();
        if ($falg === true) {
            $result = array(
                "code" => '1',
                "message" => "成功!"
            );
        } else {
            $result = array(
                "code" => '-1',
                "message" => "失败!"
            );
        }
        echo json_encode($result);
        exit;
    }

}
