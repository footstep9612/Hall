<?php
/**
	上传Controller
*/
class UploadfileController extends PublicController{
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
		//上传到fastDFS
		$fastdfs = new FastDFSclient();
		$ret = $fastdfs->uploadAttach($file['upFile']);

		if(!empty($ret['fileId'])){
			$result = array(
				"ok" => '1',
				"data" => $ret['fileId'],
			);
		}else{
			$result = array(
				"ok" => '-103',
				"data" => "error"
			);
		}
		echo json_encode($result);
		exit;
	}

}
