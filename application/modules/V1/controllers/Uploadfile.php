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
		$data = $this->getRequest()->getPost();
		$file = $this->getRequest()->getFiles();
		if(empty($file)){
			return false;
		}
		//上传到fastDFS
		$fastdfs = new FastDFSclient();
		$ret = $fastdfs->uploadAttach($file['upFile']);
		var_dump($ret);
		echo "<br >";
		if(!empty($ret['fileId'])){
			$retimgarr = pathinfo($ret['fileId']);
			$retimgpre = Yaf_Application::app()->getConfig()->fastDFSUrl.$retimgarr['dirname'].'/'.$retimgarr['filename'];
			$retimgext = $retimgarr['extension'];
			$url = $retimgpre.'.'.$retimgext;
			//存入附件表
			/*$attData = array(
				'attachment_type' 	=> 	1,
				'original_filename' =>	$file['image']['name'],
				'attachment_state'	=> 	0,
				'mime_type'			=>	$retimgext,
				'uri_relativel'		=>	$retimgarr['dirname'].'/'.$retimgarr['filename'].'.'.$retimgext,
				'uri_site'			=>	Yaf_Application::app()->getConfig()->fastDFSUrl,
				'size'				=>	$file['image']['size'],
				'owner_type'		=>	2,//图文内容中图片
				'token'				=>  $this->user['token']
			);
			PostData(Yaf_Application::app()->getConfig()->myhost."Attachment/AttCreate",$attData);//存库，scoket在这不能用了
			//得到上传文件所对应的各个参数,数组结构
			$result = array(
				'code'	=> 0,
				'msg'	=> '上传成功',
				'data'	=> array(
					'src' => $url,
					'title' => $retimgarr['filename']
				)
			);*/
			$result = array(
				"ok" => true,
				"data" => $url,
			);
		}else{
			$result = array(
				"ok" => true,
				"data" => "error"
			);
		}
		echo json_encode($result);
		exit;
	}

	/**
     * upload
     * @param
     * @return json
     * @author Wen
     */

	public function UpFileAction()
    {
        $file = $this->getRequest()->getFiles();
        if( empty($file) ){
            return false;
        }
        //上传到fastDFS
        $_FastDFS = new FastDFSclient();
        $ret = $_FastDFS->uploadAttach($file['upFile']);
        var_dump($ret);
        if(!empty($ret['fileId'])){
            $retimgarr = pathinfo($ret['fileId']);
            $retimgpre = Yaf_Application::app()->getConfig()->fastDFSUrl.$retimgarr['dirname'].'/'.$retimgarr['filename'];
            $retimgext = $retimgarr['extension'];
            $url = $retimgpre.'.'.$retimgext;
            // 确定存入附件表的数据
            $attData = array(
                'attachment_type' 	=> 	1,
                'original_filename' =>	$file['upFile']['name'],
                'attachment_state'	=> 	0,
                'mime_type'			=>	$retimgext,
                'uri_relativel'		=>	$retimgarr['dirname'].'/'.$retimgarr['filename'].'.'.$retimgext,
                'uri_site'			=>	Yaf_Application::app()->getConfig()->fastDFSUrl,
                'size'				=>	$file['upFile']['size'],
            );
            $res['url']  = $url;
            $res['name'] = $file['upFile']['name'];
            $res['data'] = json_encode($attData);
            $arr = array('code' => '0', 'message' => 'success', 'data' => $res );
        }else{
            $arr = array('code' => '-103', 'message' => 'error', 'data' => '上传失败' );
        }
        echo json_encode( $arr );
        die();
    }

}
