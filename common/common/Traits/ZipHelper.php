<?php
/**
 * Zip操作住手
 * User: linkai
 * Date: 2017/9/13
 * Time: 10:11
 */
trait ZipHelper{
    /**
     * 压缩目录
     * Usage:
     *     ZipHelper::zipDir('/path/to/sourceDir' , '/path/to/out.zip');
     * @param string $sourcePath 要压缩的目录/文件
     * @param string $outZipPath 压缩后的zip文件
     */
    public static function zipDir($sourcePath,$outZipPath){
        $zip = new ZipArchive();
        if($zip->open($outZipPath,ZipArchive::CREATE)===true){
            if(is_file($sourcePath)){
                $pathInfo = pathinfo($sourcePath);
                $zip->addFile($sourcePath,$pathInfo['basename']);
            }
            if(is_dir($sourcePath)){
                $exclusiveLength = (substr($sourcePath,-1) == '/') ? strlen($sourcePath) : strlen("$sourcePath/");
                self::folderToZip($sourcePath, $zip ,$exclusiveLength);
            }
            $zip->close();
        }
    }

    /**
     * 添加文件和目录到zip
     * @param string $folder 要压缩的目录
     * @param Ziparchive $zip
     */
    public static function folderToZip($folder,&$zip,$exclusiveLength,$newDir=''){
        $handle = opendir($folder);
        while(false !== $f = readdir($handle)) {
            if($f != '.' && $f != '..'){
                $filePath = "$folder/$f";
                if(is_file($filePath)){
                    $f = $newDir=='' ? $f : $newDir.$f;
                    $zip->addFile($filePath,$f);
                }elseif(is_dir($filePath)){
                    $zip->addEmptyDir(substr($filePath, $exclusiveLength));
                    self::folderToZip($filePath,$zip,$exclusiveLength,$newDir.substr($filePath, $exclusiveLength).'/');
                }
            }
        }
        closedir($handle);
    }

    /**
     * 删除目录（递归）
     * @param string $directory 目录
     */
    public static function removeDir($directory){
        if(file_exists($directory)){
            if(is_file($directory)){
                unlink($directory);
            }else {
                if ( $dir_handle = @opendir( $directory ) ) {
                    while ( $filename = readdir( $dir_handle ) ) {
                        if ( $filename != '.' && $filename != '..' ) {    //排除两个特殊的目录
                            $subFile = $directory . "/" . $filename;
                            if ( is_dir( $subFile ) ) {
                                delDir( $subFile );
                            }
                            if ( is_file( $subFile ) ) {
                                unlink( $subFile );
                            }
                        }
                    }
                    closedir( $dir_handle );
                    rmdir( $directory );
                }
            }
        }
    }

    /**
     * 上传至fastDfs
     */
    public static function upload2FastDFS($file,$ext){
        if(extension_loaded('fastdfs_client')){
            $fileSuffix = '';
            $fdfs = new FastDFS();
            $tracker = $fdfs->tracker_get_connection();
            $fileId = $fdfs->storage_upload_by_filebuff1(file_get_contents($file), $ext);
            $fdfs->tracker_close_all_connections();
            return $fileId;
        }else{
            return array();
        }
    }




}
