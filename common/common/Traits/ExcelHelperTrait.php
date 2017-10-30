<?php

/**
 * Excel相关操作处理
 * Trait ExcelHelperTrait
 * @author 买买提
 */
trait ExcelHelperTrait {

    /**
     * 测试
     * @param $filename
     *
     * @return string
     */
    static public function read($filename) {
        return ucfirst($filename);
    }

    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    static public function download2local($remoteFile) {
        //设置本地临时保存目录
        $tmpSavePath = MYPATH . '/public/tmp/';
        if (!is_dir($tmpSavePath))
            mkdir($tmpSavePath, 0777, true);

        $localFullFileName = $tmpSavePath . iconv("UTF-8", "GB2312", urldecode(basename($remoteFile)));

        $file = fopen($remoteFile, "rb");

        if ($file) {
            $newf = fopen($localFullFileName, "wb");
            if ($newf)
                while (!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
        }

        if ($file)
            fclose($file);
        if ($newf)
            fclose($newf);

        return $localFullFileName;
    }

    /**
     * 读取excel文件内容并以数组形式返回
     * @param $localFile 本地文件
     *
     * @return array 文件内容数组
     */
    static public function ready2import($localFile) {
        //获取文件类型
        $fileType = PHPExcel_IOFactory::identify($localFile);
        //创建PHPExcel读取对象
        $objReader = PHPExcel_IOFactory::createReader($fileType);
        //加载文件并读取
        $data = $objReader->load($localFile)->getSheet(0)->toArray();

        return $data;
    }

    /**
     * 创建Excel文件(临时文件)
     * @param $PHPExcelWriterObj PHPExcel处理对象
     * @param $fileName 文件名
     *
     * @return string 本地文件路径
     */
    static public function createExcelToLocalDir($PHPExcelWriterObj, $fileName) {
        $excel_tmp_dir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        $fullFileName = $excel_tmp_dir . $fileName;
        RecursiveMkdir($fullFileName);
        $PHPExcelWriterObj->save($fullFileName);
        return $fullFileName;
    }

    /**
     * 本地文件上传到文件服务器
     * @param $localFile    本地文件路径
     *
     * @return mixed
     */
    static public function uploadToFileServer($localFile, $type = 'application/octet-stream') {
        //TODO 这里添加上传到文件服务器的逻辑

        $client = new FastDFSclient();
        $file = [
            'name' => $localFile,
            'type' => self::getFileType($localFile),
            'size' => filesize($localFile),
            'tmp_name' => $localFile
        ];
        $ret = $client->uploadAttach($file);
        return $ret;
    }

    /**
     * 根据文件名获取Mime类型
     * @param string $file 文件名称
     * @return string 默认返回application/octet-stream
     * */
    static public function getFileType($file) {
        if (strpos($file, '.') < 1)
            return 'application/octet-stream';
        $ext = substr($file, strrpos($file, '.') + 1);
        $ext = strtolower($ext);
        //后期改为配置文件
        $mimes = [
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'html' => 'text/html',
            'zip' => 'application/zip'
        ];
        return isset($mimes[$ext]) ? $mimes[$ext] : 'application/octet-stream';
    }

    /**
     * 打包文件并且上传至FastDFS服务器
     * @param string $filename 压缩包名称
     * @param array $files  需要打包的文件列表
     * @return mixed
     * */
    static public function packAndUpload($filename, $files) {
        //创建临时目录
        $tmpdir = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/" . uniqid() . '/';
        @mkdir($tmpdir, 0777, true);
        if (!is_dir($tmpdir)) {
            return false;
        }
        //复制文件到临时目录
        foreach ($files as $file) {
            if (!is_readable($file['url'])) {
                $error_files[] = $file;
                continue;
            }
            $name = $file['name'];
            //如果文件存在则重命名
            if (file_exists($tmpdir . $name)) {
                //循环100次修改文件名
                for ($i = 1; $i < 100; $i++) {
                    $name = preg_replace("/(\.\w+)/i", "($i)$1", $name);
                    if (!file_exists($tmpdir . $name)) {
                        break;
                    }
                }
            }

            //目标文件仍然存在，则写入错误文件
            if (file_exists($tmpdir . $name)) {
                $error_files[] = $file;
            }
            @copy($file['url'], $tmpdir . $name);
        }
        //如果有文件无法复制到本目录
        if (!empty($error_files)) {
            //return false;
        }
        //生成压缩文件
        $zip = new ZipArchive();
        $filepath = dirname($tmpdir) . '/' . $filename;
        $res = $zip->open($filepath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
        if ($res !== true) {
            echo __LINE__;
            die();
            return false;
        }

        $files = scandir($tmpdir);
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                $zip->addFile($tmpdir . $item, $item);
            }
        }
        $zip->close();
        //清理临时目录
        foreach ($files as $item) {
            if ($item != '.' && $item != '..') {
                unlink($tmpdir . $item);
            }
        }
        @rmdir($tmpdir);
        //上传至FastDFS
        $ret = self::uploadToFileServer($filepath);
        //删除临时压缩文件
        @unlink($filepath);
        return $ret;
    }

}
