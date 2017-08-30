<?php

use OSS\OssClient;

class AliyunOSSClient {
	private $config;
	//上传附件  
	function uploadAttach($file,$path='')                                                                              
	{
		$ret = array();  
		$ret['errorcode'] = 0;  
		$ret['errormsg'] = '';  
		if(empty($file))  
		{  
			$ret['errorcode'] = 1;  
			$ret['errormsg'] = "ERROR:upFile is not set";  
			return $ret;  
		}  
	  
		//$file = $_FILES["upFile"];  
		if (false == isset($file['tmp_name']) || false == is_file($file['tmp_name']))  
		{  
			$ret['errorcode'] = 2;  
			$ret['errormsg'] = "tmp_name is not file";  
			return $ret;  
		}  
		if (0 == filesize($file['tmp_name']))  
		{  
			$ret['errorcode'] = 3;  
			$ret['errormsg'] = "tmp_name filesize is 0";  
			return $ret;  
		}  
	  
		$ext = substr(strrchr($file['name'], '.'), 1);
        if(!in_array($ext,['jpg','gif','png'])){
			$ret['errorcode'] = 4;  
			$ret['errormsg'] = "file type error";  
			return $ret;  
		}	
        $filename = $this->generateFileName($ext);
		
		$this->config = Yaf_Application::app()->getConfig();
		if(($ossClient = $this->getOssClient()) !== false ){
			$bucket          = $this->config->storage->aliyun_oss->bucket;
			$path = empty($path) ? "portrait/" : $path;
			$ret = $ossClient->uploadFile($bucket, $path.$filename,$file['tmp_name']);
			if(!empty($ret['info']['url'])){					
				$ret['url'] = $ret['info']['url'];
				$ret['size'] = $ret['info']['size_upload'];									
			}else{
				$ret['errorcode'] = 6;  
			    $ret['errormsg'] = "server error";  
			}
		}else{
			$ret['errorcode'] = 5;  
			$ret['errormsg'] = "server error";  
		}                                   
		return $ret;  
	}
	
	private function getOssClient(){
		$accessKeyId     = $this->config->storage->aliyun_oss->accessKeyId;
        $accessKeySecret = $this->config->storage->aliyun_oss->accessKeySecret;
        $endpoint        = $this->config->storage->aliyun_oss->endpoint;
		
        try {
            $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);
			return $ossClient;
        } catch (OssException $e) {
			//Log::save('ERROR',$e->getMessage());
           return false;
        }
	}
	
	private function generateFileName($ext){
		$name =  uniqid('ER_',true);
		return str_replace('.','_',$name).'.'.$ext;
	}
  
  
	//上传文件到fastdfs  
	function uploadToOss(CurlFile $file, $fileSuffix)                                                    
	{
		if(extension_loaded('fastdfs_client')){
			$fdfs = new FastDFS();
			$tracker = $fdfs->tracker_get_connection();
			$fileId = $fdfs->storage_upload_by_filebuff1(file_get_contents($file->getFilename()), $fileSuffix); 
			$fdfs->tracker_close_all_connections();     
			return $fileId;
		}else{
			return array();
		}
	}
}
