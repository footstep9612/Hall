<?php

class AttachmentController extends Yaf_Controller_Abstract {

    //put your code here
    private $put_data = [];

    public function __construct() {
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
    }

    protected function _upload_init($upload) {
        $file_type = empty($this->put_data['dir']) ? 'image' : trim($this->put_data['dir']);
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        $allow_exts_conf = ['jpg', 'gif', 'png', 'jpeg', "bmp", 'pdf', 'txt', 'doc', 'docx', 'xls', 'xlsx'];
        //和总配置取交集
        $allow_exts = array_intersect($ext_arr[$file_type], $allow_exts_conf);

        $upload->maxSize = 50 * 1024 * 1024;   //文件大小限制
        $upload->allowExts = $allow_exts;  //文件类型限制
        $upload->savePath = DIRECTORY_SEPARATOR . $file_type . DIRECTORY_SEPARATOR;
        $upload->saveRule = 'uniqid';
        $upload->autoSub = true;
        $upload->subType = 'date';
        $upload->dateFormat = 'Y' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'd' . DIRECTORY_SEPARATOR;
        return $upload;
    }

    /**
     * 编辑器上传
     */
    public function uploadAction() {
        $file_type = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        $result = $this->_upload($_FILES['imgFile']);
        if ($result['error']) {
            $this->jsonReturn(['code' => -1, 'message' => $result['info']]);
        } else {
            json_encode(['code' => 0, 'message' => '成功!',
                'data' => [
                    'url' => '/Uploads/' . $result['info']['savepath'] . $result['info']['savename'], 'result' => $result]]);
        }
        exit;
    }

    public function jsonReturn($data = [], $type = 'JSON') {

        header('Content-Type:application/json; charset=utf-8');
        if (isset($data['code'])) {
            exit(json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            if ($data) {
                $send['data'] = $data;
            }

            $send['code'] = $this->getCode();

            if ($send['code'] == "1" && !$this->getMessage()) {
                $send['message'] = '成功!';
            } elseif (!$this->getMessage()) {
                $send['message'] = '未知错误!';
            } else {
                $send['message'] = $this->getMessage();
            }

            exit(json_encode($send, JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * 上传文件
     */
    protected function _upload($file, $dir = '', $thumb = array(), $save_rule = 'uniqid') {
        $upload = new Upload();
        if ($dir) {
            $upload_path = '/' . $dir;
            $upload->savePath = $upload_path;
        }
        if ($thumb) {
            $upload->thumb = true;
            $upload->thumbMaxWidth = $thumb['width'];
            $upload->thumbMaxHeight = $thumb['height'];
            $upload->thumbPrefix = '';
            $upload->thumbSuffix = isset($thumb['suffix']) ? $thumb['suffix'] : '_thumb';
            $upload->thumbExt = isset($thumb['ext']) ? $thumb['ext'] : '';
            $upload->thumbRemoveOrigin = isset($thumb['remove_origin']) ? true : false;
        }
        //自定义上传规则
        $upload = $this->_upload_init($upload);
        if ($save_rule != 'uniqid') {
            $upload->saveRule = $save_rule;
        }
        if ($result = $upload->uploadOne($file)) {
            return array('error' => 0, 'info' => $result);
        } else {
            return array('error' => 1, 'info' => $upload->getError());
        }
    }

    function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            @mkdir($path, 0777);
        }
    }

    //排序
    private function _cmp_func($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($this->_order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($this->_order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

}
