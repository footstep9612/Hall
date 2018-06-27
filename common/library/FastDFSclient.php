<?php

class FastDFSclient {

    //上传附件
    function uploadAttach($file, $group_name = 'group1') {
        $ret = array();
        $ret['errorcode'] = 0;
        $ret['errormsg'] = '';
        if (empty($file)) {
            $ret['errorcode'] = 1;
            $ret['errormsg'] = "ERROR:upFile is not set";
            return $ret;
        }

        //$file = $_FILES["upFile"];
        if (false == isset($file['tmp_name']) || false == is_file($file['tmp_name'])) {
            $ret['errorcode'] = 2;
            $ret['errormsg'] = "tmp_name is not file";
            return $ret;
        }
        if (0 == filesize($file['tmp_name'])) {
            $ret['errorcode'] = 3;
            $ret['errormsg'] = "tmp_name filesize is 0";
            return $ret;
        }

        $curlFile = new CurlFile($file['tmp_name'], $file['type'], $file['name']);
        $fileSuffix = $this->getSuffix($curlFile->getPostFilename());

        $ret['file'] = $file;
        $ret['fileId'] = $this->uploadToFastdfs($curlFile, $fileSuffix, $group_name);
        return $ret;
    }

    //获取后缀
    function getSuffix($fileName) {
        preg_match('/\.(\w+)?$/', $fileName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

    //上传文件到fastdfs
    function uploadToFastdfs(CurlFile $file, $fileSuffix, $group_name = 'group1') {
        if (extension_loaded('fastdfs_client')) {
            $fdfs = new FastDFS();
            $tracker = $fdfs->tracker_get_connection();
            $fileId = $fdfs->storage_upload_by_filebuff1(file_get_contents($file->getFilename()), $fileSuffix, [], $group_name);
            $fdfs->tracker_close_all_connections();
            return $fileId;
        } else {
            return array();
        }
    }

}
