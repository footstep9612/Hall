<?php
/**
	上传Controller
*/
class UploadfileController extends Yaf_Controller_Abstract{
	public function init() {

	}
	/*
		上传地址
	*/
	public function UploadAction(){
		$file = $this->getRequest()->getFiles();
		if(empty($file)){
			return false;
		}
		var_dump($file);die;
		//上传到fastDFS
		$fastdfs = new FastDFSclient();
		$ret = $fastdfs->uploadAttach($file['upFile']);

		if(!empty($ret['fileId'])){
			$result = array(
				"code" => '1',
				"url" => $ret['fileId'],
			);
		}else{
			$result = array(
				"code" => '-103',
				"message" => "error"
			);
		}
		echo json_encode($result);
		exit;
	}

}
