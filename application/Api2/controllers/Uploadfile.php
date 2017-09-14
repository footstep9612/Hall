<?php

/**
  上传Controller
 */
class UploadfileController extends Yaf_Controller_Abstract {
    /*
      上传地址
     */

    public function UploadAction() {

        $file = $this->getRequest()->getFiles();
        if (empty($file)) {
            return false;
        }


        //  $this->Netoyou_img_mark($file['upFile']);
        //  $result = $this->postfile($file['upFile']);
        //上传到fastDFS
        $fastdfs = new FastDFSclient();
        $ret = $fastdfs->uploadAttach($file['upFile']);

        if (!empty($ret['fileId'])) {
            $result = array(
                "code" => '1',
                "url" => $ret['fileId'],
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

//
//    function postfile($data, $timeout = 30) {
//
//        $cfile = new CURLFile($data['tmp_name'], $data['type'], $data['name']);
//        $url = 'http://172.18.18.196/api2/Uploadfile/Upload';
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_HEADER, false);
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, ['upFile' => $cfile]);
//        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
//        $response = curl_exec($ch);
//
//        if (curl_errno($ch)) {
//            print_r(curl_error($ch));
//
//            return [];
//        }
//        curl_close($ch);
//        $cfile = null;
//        unset($cfile);
//        return $response;
//    }

    /*
     * 图片水印功能
     */

    function Netoyou_img_mark($file) {
        $curlFile = new CurlFile($file['tmp_name'], $file['type'], $file['name']);
        $extension = strtolower($this->getSuffix($curlFile->getPostFilename()));
        if ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png' || $extension == 'gif') {
            //载入原图片GD流
            if ($extension == 'jpg' || $extension == 'jpeg') {
                $with_im = @ImageCreateFromJPEG($file['tmp_name']);
            }
            if ($extension == 'gif') {
                $with_im = @ImageCreateFromGIF($file['tmp_name']);
            }
            if ($extension == 'png') {
                $with_im = @ImageCreateFromPNG($file['tmp_name']);
            }
            $with_logo = MYPATH . '/public/images/erui.png'; //水印图片
            $with_lim = @ImageCreateFromPNG($with_logo); //读取水印文件标识

            $imagesize = getimagesize($file['tmp_name']);


            $with_lim_x = ImageSX($with_im) - ImageSX($with_lim); //计算水印的位置
            $with_lim_y = ImageSY($with_im) - ImageSY($with_lim); //计算水印的位置
            //创建新的有水印图像
            imagecopy($with_im, $with_lim, $with_lim_x, $with_lim_y, 0, 0, ImageSX($with_lim), ImageSY($with_lim));
            imagejpeg($with_im, $file['tmp_name'], 100); //创建和覆盖新的图像并保存
            imagedestroy($with_im);
            imagedestroy($with_lim);
        }//水印处理结束
    }

    //获取后缀
    function getSuffix($fileName) {
        preg_match('/\.(\w+)?$/', $fileName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

}
