<?php

/**
 * Excel相关操作处理
 * Trait ExcelHelperTrait
 * @author 买买提
 */
trait ExcelHelperTrait
{
    /**
     * 测试
     * @param $filename
     *
     * @return string
     */
    static public function read($filename)
    {
        return ucfirst($filename);
    }


    /**
     * 远程文件现在到本地临时目录处理完毕后自动删除)
     * @param $remoteFile 远程文件地址
     *
     * @return string 本地的临时地址
     */
    static public function download2local($remoteFile)
    {
        //设置本地临时保存目录
        $tmpSavePath = MYPATH . '/public/tmp/' ;
        if ( !is_dir($tmpSavePath) )    mkdir($tmpSavePath,0777,true) ;

        $localFullFileName = $tmpSavePath . iconv("UTF-8","GB2312",urldecode(basename($remoteFile)));

        $file = fopen ($remoteFile, "rb");

        if ($file) {
            $newf = fopen ($localFullFileName, "wb");
            if ($newf)
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
                }
        }

        if ($file) fclose($file);
        if ($newf) fclose($newf);

        return $localFullFileName;
    }

    /**
     * 读取excel文件内容并以数组形式返回
     * @param $localFile 本地文件
     *
     * @return array 文件内容数组
     */
    static public function ready2import($localFile)
    {
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
    static public function createExcelToLocalDir($PHPExcelWriterObj,$fileName)
    {
        $fullFileName = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/".$fileName;
        $PHPExcelWriterObj->save($fullFileName);

        return $fullFileName;
    }

    /**
     * 本地文件上传到文件服务器
     * @param $localFile    本地文件路径
     *
     * @return mixed
     */
    static public function uploadToFileServer($localFile)
    {
        //TODO 这里添加上传到文件服务器的逻辑
        return $localFile;
    }
}
