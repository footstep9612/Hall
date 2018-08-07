<?php

/*
  |---------------------------------------------------------------
  |  Copyright (c) 2016
  |---------------------------------------------------------------
  | 文件名称：全局公共函数
  | 功能 :功能函数
  | 作者：qieangel2013
  | 联系：qieangel2013@gmail.com
  | 版本：V1.0
  | 日期：2016/2/24 10:42 星期四
  |---------------------------------------------------------------
 */

/**
 * 一天中12小时时间数组
 */
function DayHour() {
    return array(
        ' 00:00:01',
        ' 01:00:01',
        ' 02:00:01',
        ' 03:00:01',
        ' 04:00:01',
        ' 05:00:01',
        ' 06:00:01',
        ' 07:00:01',
        ' 08:00:01',
        ' 09:00:01',
        ' 10:00:01',
        ' 11:00:01',
        ' 12:00:01',
        ' 13:00:01',
        ' 14:00:01',
        ' 15:00:01',
        ' 16:00:01',
        ' 17:00:01',
        ' 18:00:01',
        ' 19:00:01',
        ' 20:00:01',
        ' 21:00:01',
        ' 22:00:01',
        ' 23:00:01'
    );
}

/*
 * 分词器
 * @$title 需要分词的变量
 * @$num 分词后的最大词组数
 */

function getTagsByTitle($title, $num = 10) {
    $pscws = new PSCWS4();
    $pscws->set_dict(MYPATH . '/conf/dict.utf8.xdb');
    $pscws->set_rule(MYPATH . '/conf/rules.utf8.ini');
    $pscws->set_ignore(true);
    $pscws->send_text($title);
    $words = $pscws->get_tops($num);
    $pscws->close();
    $tags = array();
    foreach ($words as $val) {
        $tags[] = $val['word'];
    }
    return $tags;
}

/**
 * [DayHourPart description]
 */
function DayHourPart() {
    return array(
        array('00:00:01', '01:00:00'),
        array('01:00:01', '02:00:00'),
        array('02:00:01', '03:00:00'),
        array('03:00:01', '04:00:00'),
        array('04:00:01', '05:00:00'),
        array('05:00:01', '06:00:00'),
        array('06:00:01', '07:00:00'),
        array('07:00:01', '08:00:00'),
        array('08:00:01', '09:00:00'),
        array('09:00:01', '10:00:00'),
        array('10:00:01', '11:00:00'),
        array('11:00:01', '12:00:00'),
        array('12:00:01', '13:00:00'),
        array('13:00:01', '14:00:00'),
        array('14:00:01', '15:00:00'),
        array('15:00:01', '16:00:00'),
        array('16:00:01', '17:00:00'),
        array('17:00:01', '18:00:00'),
        array('18:00:01', '19:00:00'),
        array('19:00:01', '20:00:00'),
        array('20:00:01', '21:00:00'),
        array('21:00:01', '22:00:00'),
        array('22:00:01', '23:00:00'),
        array('23:00:01', '23:59:59')
    );
}

/**
 * 数字添加前导零
 * @param [type] $num [description]
 */
function NumTransform($num) {
    if (strlen($num) == 2 && $num < 10) {
        $num = substr($num, 1);
    }
    return $num;
}

/**
 * [cutstr 汉字切割]
 * @param  [string] $string [需要切割的字符串]
 * @param  [string] $length [显示的长度]
 * @param  string $dot    [切割后面显示的字符]
 * @return [string]         [切割后的字符串]
 */
function cutstr($string, $length, $dot = '...') {
    if (strlen($string) <= $length) {
        return $string;
    }
    $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
    $strcut = '';
    $n = $tn = $noc = 0;
    while ($n < strlen($string)) {
        $t = ord($string[$n]);
        if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
            $tn = 1;
            $n++;
            $noc++;
        } elseif (194 <= $t && $t <= 223) {
            $tn = 2;
            $n += 2;
            $noc += 2;
        } elseif (224 <= $t && $t < 239) {
            $tn = 3;
            $n += 3;
            $noc += 2;
        } elseif (240 <= $t && $t <= 247) {
            $tn = 4;
            $n += 4;
            $noc += 2;
        } elseif (248 <= $t && $t <= 251) {
            $tn = 5;
            $n += 5;
            $noc += 2;
        } elseif ($t == 252 || $t == 253) {
            $tn = 6;
            $n += 6;
            $noc += 2;
        } else {
            $n++;
        }
        if ($noc >= $length) {
            break;
        }
    }
    if ($noc > $length) {
        $n -= $tn;
    }
    $strcut = substr($string, 0, $n);
    $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}

/**
 * [getPassedHours 某时间戳到现在所经过的时间]
 * @param  [int] $distence [时间戳]
 * @return [string]           [秒/分钟/小时]
 */
function getPassedHours($distence) {
    $passed = "";
    switch ($distence) {
        case ($distence < 60 ): {
                $passed = $distence . "秒";
                break;
            }
        case ($distence > 60 && $distence < 60 * 60): {
                $passed = intval($distence / 60) . "分钟";
                break;
            }
        case ($distence > 60 * 60): {
                $passed = sprintf("%.1f", $distence / (60 * 60)) . "小时";
                break;
            }
    }

    return $passed;
}

/**
 * loadClass 类对象生成器，自动载入类定义文件，实例化并返回对象句柄
 * @param <type> $sClass 类名称
 * @param <type> $aParam 类初始化时使用的参数，数组形式
 * @param <type> $bForceInst 是否强制重新实例化对象
 * @return sClass
 */
function loadClass($sClass, $aParam = "", $bForceInst = FALSE) {
    if (empty($aParam)) {
        $object = new $sClass();
    } else {
        $object = new $sClass($aParam);
    }
    return $object;
}

/**
 * 清除危险信息
 *
 * @param mixed $info
 * @return mixed
 */
function escapeInfo($info) {
    if (is_array($info)) {
        foreach ($info as $key => $value) {
            $info[$key] = escapeInfo($value);
        }
    } else {
        return htmlspecialcharsUni($info);
    }
    return $info;
}

/**
 * 针对Unicode不安全改进的安全版htmlspecialchars()
 *
 * @param	string	Text to be made html-safe
 *
 * @return	string
 */
function htmlspecialcharsUni($text, $entities = true) {
    return str_replace(
            // replace special html characters
            array('<', '>', '"', '\''), array('&lt;', '&gt;', '&quot;', '&apos;'), preg_replace(
                    // translates all non-unicode entities
                    '/&(?!' . ($entities ? '#[0-9]+|shy' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $text
            )
    );
}

/**
 * 高级搜索代码
 *
 * @param array $keyword 关键字数组
 * @param string $con 关系，and 或 or
 * @param string $method 模糊或者精确搜索
 * @param array $field 要搜索的字段数组
 * @return string
 */
function searchString($keyword, $con, $method, $field) {
    $tmp = null;
    $method = strtoupper($method);

    // 搜索中对 "_" 的过滤
    $keyword = str_replace("_", "\\_", trim($keyword));
    $keyword = split("[ \t\r\n,]+", $keyword);

    /*
      foreach ($field as $k => $v) {

      }
     */

    $num = count($field);
    if ($con == "OR") {
        $con = "OR";
    } else {
        $con = "AND";
    }

    // 模糊查找
    if ($method == "LIKE") {
        for ($i = 0; $i < $num; $i++) {
            $i < $num - 1 ? $condition = "OR" : $condition = null;
            $tmp .= " {$field[$i]} $method '%" . join("%' $con {$field[$i]} $method '%", $keyword) . "%' $condition";
        }
    } else { // 精确查找
        for ($i = 0; $i < $num; $i++) {
            $i < $num - 1 ? $condition = $con : $condition = null;
            $tmp .= " INSTR({$field[$i]}, \"" . join("\") != 0 $con INSTR({$field[$i]}, \"", $keyword) . "\") != 0 $condition";
        }
    }
    return "(" . $tmp . ")";
}

/**
 * 增加了全角转半角的trim
 *
 * @param	string  $str    原字符串
 * @return  string  $str    转换后的字符串
 */
function wtrim($str) {
    return trim(sbc2abc($str));
}

/**
 * 全角转半角
 *
 * @param	string  $str    原字符串
 * @return  string  $str    转换后的字符串
 */
function sbc2abc($str) {
    $f = array('　', '０', '１', '２', '３', '４', '５', '６', '７', '８', '９', 'ａ', 'ｂ', 'ｃ', 'ｄ', 'ｅ', 'ｆ', 'ｇ', 'ｈ', 'ｉ', 'ｊ', 'ｋ', 'ｌ', 'ｍ', 'ｎ', 'ｏ', 'ｐ', 'ｑ', 'ｒ', 'ｓ', 'ｔ', 'ｕ', 'ｖ', 'ｗ', 'ｘ', 'ｙ', 'ｚ', 'Ａ', 'Ｂ', 'Ｃ', 'Ｄ', 'Ｅ', 'Ｆ', 'Ｇ', 'Ｈ', 'Ｉ', 'Ｊ', 'Ｋ', 'Ｌ', 'Ｍ', 'Ｎ', 'Ｏ', 'Ｐ', 'Ｑ', 'Ｒ', 'Ｓ', 'Ｔ', 'Ｕ', 'Ｖ', 'Ｗ', 'Ｘ', 'Ｙ', 'Ｚ', '．', '－', '＿', '＠');
    $t = array(' ', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '.', '-', '_', '@');
    $str = str_replace($f, $t, $str);
    return $str;
}

/**
 * 输出顶部错误提示
 *
 */
function errorTip($str, $exit = true, $url = '') {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo "<script>top.window.alert('" . $str . "');</script>";
    if ($url) {
        echo '<script language="javascript">window.location.href="' . $url . '";</script>';
    }
    $exit && exit();
}

/**
 * 输出顶部成功提示
 *
 * @param unknown_type $str
 * @param unknown_type $exit
 */
function successTip($str, $exit = false, $url = '') {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo "<script>top.window.alert('" . $str . "');</script>";
    if ($url) {
        echo '<script language="javascript">window.location.href="' . $url . '";</script>';
    }
    $exit && exit();
}

/**
 * 弹出警告
 */
function alert($str, $exit = false) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<script language="javascript">window.alert("' . $str . '");</script>';
    $exit && exit();
}

/**
 *
 * @description:该函数仅输出字符串，并提供了是否退出功能
 *
 * @param: $var,    string 需要输出的字符串
 * @param: $isexit, string 如果是 "exit" 字符串时退出调用该函数的脚本文件
 * @return: 无
 *
 */
function output($var, $isexit = "exit") {
    echo $var;
    if ($isexit == "exit") {
        exit;
    }
}

/*
 * 过滤
 */

function removeXSS($str) {
    $str = str_replace('<!--  -->', '', $str);
    $str = preg_replace('~/\*[ ]+\*/~i', '', $str);
    $str = preg_replace('/\\\0{0,4}4[0-9a-f]/is', '', $str);
    $str = preg_replace('/\\\0{0,4}5[0-9a]/is', '', $str);
    $str = preg_replace('/\\\0{0,4}6[0-9a-f]/is', '', $str);
    $str = preg_replace('/\\\0{0,4}7[0-9a]/is', '', $str);
    $str = preg_replace('/&#x0{0,8}[0-9a-f]{2};/is', '', $str);
    $str = preg_replace('/&#0{0,8}[0-9]{2,3};/is', '', $str);
    $str = preg_replace('/&#0{0,8}[0-9]{2,3};/is', '', $str);

    //$str = preg_replace('/&lt;/i', '<', $str);
    //$str = preg_replace('/&gt;/i', '>', $str);
    // 非成对标签
    $lone_tags = array("img", "param", "br", "hr");
    foreach ($lone_tags as $key => $val) {
        $val = preg_quote($val);
        $str = preg_replace('/&lt;' . $val . '(.*)(\/?)&gt;/isU', '<' . $val . "\\1\\2>", $str);
        $str = transCase($str);
        $str = preg_replace_callback(
                '/<' . $val . '(.+?)>/i', create_function('$temp', 'return str_replace("&quot;","\"",$temp[0]);'), $str
        );
    }
    $str = preg_replace('/&amp;/i', '&', $str);

    // 成对标签
    $double_tags = array("table", "tr", "td", "font", "a", "object", "embed", "p", "strong", "em", "u", "ol", "ul", "li", "div", "tbody", "span", "blockquote", "pre", "b", "font");
    foreach ($double_tags as $key => $val) {
        $val = preg_quote($val);
        $str = preg_replace('/&lt;' . $val . '(.*)&gt;/isU', '<' . $val . "\\1>", $str);
        $str = transCase($str);
        $str = preg_replace_callback(
                '/<' . $val . '(.+?)>/i', create_function('$temp', 'return str_replace("&quot;","\"",$temp[0]);'), $str
        );
        $str = preg_replace('/&lt;\/' . $val . '&gt;/is', '</' . $val . ">", $str);
    }
    // 清理js
    $tags = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'behaviour', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base', 'font');

    foreach ($tags as $tag) {
        $tag = preg_quote($tag);
        $str = preg_replace('/' . $tag . '\(.*\)/isU', '\\1', $str);
        $str = preg_replace('/' . $tag . '\s*:/isU', $tag . '\:', $str);
    }

    $str = preg_replace('/[\s]+on[\w]+[\s]*=/is', '', $str);

    Return $str;
}

function transCase($str) {
    $str = preg_replace('/(e|ｅ|Ｅ)(x|ｘ|Ｘ)(p|ｐ|Ｐ)(r|ｒ|Ｒ)(e|ｅ|Ｅ)(s|ｓ|Ｓ)(s|ｓ|Ｓ)(i|ｉ|Ｉ)(o|ｏ|Ｏ)(n|ｎ|Ｎ)/is', 'expression', $str);
    Return $str;
}

function scriptAlert($var, $exit = 1) {
    if (!empty($var)) {
        $content = "";
        if (is_array($var)) {
            foreach ($var as $value) {
                $content .= $value . "\\n";
            }
        } else {
            $content = $var;
        }
    } else {
        $content = "运行出现错误！";
    }
    header('Content-Type: text/html;Charset=UTF-8');
    echo "<script>top.window.alert('" . $content . "');</script>";
    if ($exit) {
        exit;
    }
}

//获取本周的开始与结束日期
function GetWeeks() {
    $nowDate = getdate();
    $dq_date = mktime(0, 0, 0, date('m'), date('d'), date('Y')); //得到今天第一秒
    $nowWeek = $nowDate['wday']; //今天周几

    $bz_time_a = $dq_date - ( ($nowWeek - 1) * 86400 );  //本周第一秒
    $bz_time_b = $bz_time_a + 86400 * 7 - 1;
    return date("Y-m-d", $bz_time_a) . "--" . date("Y-m-d", $bz_time_b);
}

//获取当前月的上一个月或者下一个月
function GetMonth($sign) {
    //得到系统的年月
    $tmp_date = date("Ym");
    //切割出年份
    $tmp_year = substr($tmp_date, 0, 4);
    //切割出月份
    $tmp_mon = substr($tmp_date, 4, 2);
    //$tmp_nextmonth=mktime(0,0,0,$tmp_mon+1,1,$tmp_year);
    $tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - $sign, 1, $tmp_year);

    //得到当前月的上一个月
    return $fm_forward_month = date("Ym", $tmp_forwardmonth);
}

/**
 * 输出顶部错误提示并返回
 *
 */
function errorrTipReturn($str) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    echo '<script language="javascript">top.window.alert("' . $str . '");history.back(-1);</script>';
    exit();
}

/**
 * 跳转至父页面
 *
 * @param    string     $url    跳转地址
 * @param    string     $mode   间隔时间
 */
function refresh($url = '', $mode = '') {
    switch ($mode) {
        case 'top' :
            $mode = 'top';
            break;

        case 'parent' :
            $mode = 'parent';
            break;

        default :
            $mode = 'window';
    }
    echo '<script language="javascript">' . $mode . '.location.href="' . $url . '";</script>';
    exit();
}

function enableUploadAttach($upload_file, $max_size = 512000, $enable_type = array("gif", "jpg", "png", "zip", "rar")) {
    if (empty($upload_file["name"])) {
        return "";
    }

    $image_types = array(
        "image/gif",
        "image/png",
        "image/x-png",
        "image/jpg",
        "image/jpeg",
        "image/pjpeg",
    );

    if (in_array($upload_file["type"], $image_types)) {
        if ($upload_file["type"] == "image/gif") {
            $ext = "gif";
        } elseif ($upload_file["type"] == "image/png" || $upload_file["type"] == "image/x-png") {
            $ext = "png";
        } else {
            $ext = "jpg";
        }
    } else {
        $ext = explode(".", $upload_file["name"]);
        $ext = end($ext);
    }

    if ($upload_file["size"] > $max_size || !in_array($ext, $enable_type)) {
        return "";
    } else {
        return $ext;
    }
}

/**
 * [mkFolders 递归创建文件夹]
 * @param  [type] $folders    [description]
 * @param  [type] $cache_path [description]
 * @return [type]             [description]
 */
function mkFolders($folders, $cache_path) {
    if (is_array($folders)) {
        foreach ($folders as $folder) {
            $cache_path .= "/" . $folder;
            if (!file_exists($cache_path)) {
                mkdir($cache_path);
                chmod($cache_path, 0777);
            }
        }
    }
}

/**
 * RecursiveMkdir 递归创建目录
 * @param  [type] $path    目录地址
 * @param  [type] $cache_path [description]
 * @return [type]             [description]
 */
function RecursiveMkdir($path) {
    if (!file_exists($path)) {
        RecursiveMkdir(dirname($path));
        @mkdir($path, 0777);
    }
}

/**
 * 得到PHP错误，并报告一个系统错误
 *
 * @param integer   $errorNo
 * @param string    $message
 * @param string    $filename
 * @param integer   $lineNo
 */
function handleError($errorNo, $message, $filename, $lineNo) {
    if (error_reporting() != 0) {
        $type = 'error';
        switch ($errorNo) {
            case 2 :
                $type = 'warning';
                break;
            case 8 :
                $type = 'notice';
                break;
        }
        throw new Exception('PHP ' . $type . ' in file ' . $filename . ' (' . $lineNo . '): ' . $message, 0);
    }
}

function encrypt($str, $toBase64 = false, $key = "www.smesauz.com20380201") {
    $r = md5($key);
    $c = 0;
    $v = "";
    $len = strlen($str);
    $l = strlen($r);
    for ($i = 0; $i < $len; $i++) {
        if ($c == $l)
            $c = 0;
        $v .= substr($r, $c, 1) .
                (substr($str, $i, 1) ^ substr($r, $c, 1));
        $c++;
    }
    if ($toBase64) {
        return base64_encode(ed($v, $key));
    } else {
        return ed($v, $key);
    }
}

function decrypt($str, $toBase64 = false, $key = "www.smesauz.com20380201") {
    if ($toBase64) {
        $str = ed(base64_decode($str), $key);
    } else {
        $str = ed($str, $key);
    }
    $v = "";
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $md5 = substr($str, $i, 1);
        $i++;
        $v .= (substr($str, $i, 1) ^ $md5);
    }
    return $v;
}

function ed($str, $key = "www.smesauz.com20380201") {
    $r = md5($key);
    $c = 0;
    $v = "";
    $len = strlen($str);
    $l = strlen($r);
    for ($i = 0; $i < $len; $i++) {
        if ($c == $l)
            $c = 0;
        $v .= substr($str, $i, 1) ^ substr($r, $c, 1);
        $c++;
    }
    return $v;
}

/**
 * 名称:  请求接口获取数据
 * 参数:  string $key     接口地址
 * 返回值: array   数据;
 */
function GetData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    if (empty($output)) {
        return;
    }
    $result = json_decode($output, true);
    return $result;
}

/**
 * 名称:  请求接口提交数据
 * 参数:  string $key     接口地址
 * 参数:  array $data     提交数据
  参数： bool  $json	 是否json提交
 * 返回值: array   数据;
 */
function PostData($url, $data, $json = false) {
    $datastring = $json ? json_encode($data) : http_build_query($data);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datastring);
    if ($json) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($datastring))
        );
    }
    $output = curl_exec($ch);
    if (curl_errno($ch))
        print_r(curl_error($ch));
    curl_close($ch);
    if (empty($output)) {
        return;
    }
    $result = json_decode($output, true);
    return $result;
}

//微信表情处理
function uicode_z($str, $method = 'en') {
    if ($method == 'en') {
        return preg_replace_callback('/[\xf0-\xf7].{3}/', function($r) {
            return '@E' . base64_encode($r[0]);
        }, $str);
    } else {
        return preg_replace_callback('/@E(.{6}==)/', function($r) {
            return base64_decode($r[1]);
        }, $str);
    }
}

//trance
function trace($value = '[yaf]', $label = '', $level = 'DEBUG', $record = false) {
    static $_trace = array();
    $config_obj = Yaf_Registry::get("config");
    $log_config = $config_obj->log->toArray();
    $record = isset($log_config['record']) ? $log_config['record'] : FALSE;
    if ('[yaf]' === $value) { // 获取trace信息
        return $_trace;
    } else {
        $info = ($label ? $label . ':' : '') . print_r($value, true);
        if ($record) {
            Log::write($info, $level);
        }
        if ('ERR' == $level) {// 抛出异常
            throw new Exception($info);
        }
        $level = strtoupper($level);
        if (!isset($_trace[$level])) {
            $_trace[$level] = array();
        }
        $_trace[$level][] = $info;
    }
}

//log
//第一个参数是要打印的内容
//第二各参数是生成日志文件名
//第三个参数$level分为：EMERG，ALERT，CRIT，ERR，WARN，NOTIC，INFO，DEBUG，SQL
function logs($message, $destination = '', $level = 'DEBUG') {
    Log::trance($message, $destination, $level);
}

/**
 * 实例化一个没有模型文件的Model
 * @param string $name Model名称 支持指定基础模型 例如 MongoModel:User
 * @param string $tablePrefix 表前缀
 * @param mixed $connection 数据库连接信息
 * @return Model
 */
function Z($name = '', $tablePrefix = '', $connection = '') {
    static $_model = array();
    if (strpos($name, ':')) {
        list($class, $name) = explode(':', $name);
    } else {
        $class = 'ZysModel';
    }
    $guid = (is_array($connection) ? implode('', $connection) : $connection) . $tablePrefix . $name . '_' . $class;
    if (!isset($_model[$guid]))
        $_model[$guid] = new $class($name, $tablePrefix, $connection);
    return $_model[$guid];
}

/*
  jwt-token
  @param	$jwt string	用户信息密文
  @return array
 */

function JwtInfo($jwt) {
    $jwtclient = new JWTClient();
    $decoded = $jwtclient->decode($jwt); //解密
    return (array) $decoded;
}

//添加异步任务
function addtask($data) {
    $task = new swoole_taskclient();
    $task->connect(json_encode($data));
}

/**
 *
 * @return
 */
function mongo() {
    return Mongodb::getInstance();
}

/*
 * 格式化文件大小显示
 *
 * @param int $size
 * @return string
 */

function format_size($size) {
    $prec = 3;
    $size = round(abs($size));
    $units = array(
        0 => " B ",
        1 => " KB",
        2 => " MB",
        3 => " GB",
        4 => " TB"
    );
    if ($size == 0) {
        return str_repeat(" ", $prec) . "0$units[0]";
    }
    $unit = min(4, floor(log($size) / log(2) / 10));
    $size = $size * pow(2, -10 * $unit);
    $digi = $prec - 1 - floor(log($size) / log(10));
    $size = round($size * pow(10, $digi)) * pow(10, -$digi);

    return $size . $units[$unit];
}

/*
 * 随机生成字符串
 *
 * @param int $size
 * @return string
 * @author jhw
 */

function randStr($len) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'; // characters to build the password from
    $string = '';
    for (; $len >= 1; $len--) {
        $position = rand() % strlen($chars);
        $string .= substr($chars, $position, 1);
    }
    return $string;
}

/*
 * 判断邮箱
 *
 * @param int $size
 * @return string
 * @author jhw
 */

function isEmail($email) {
    $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
    if (preg_match($mode, $email)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 判断邮箱
 *
 * @param int $size
 * @return string
 * @author jhw
 */

function isMobile($mobile) {
    if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 缓存Hash存放
 *
 * @param str $name
 * @param str $key
 * @param str $value
 * @param string $Expire 设置过期时间
 * @return string
 * @author jhw
 */

function redisHashSet($name, $key, $value, $Expire = null) {

    $reids = new phpredis();
    if (empty($name) && !is_string($name)) {
        return false;
    }
    if (empty($key) && !is_string($name)) {
        return false;
    }
    if (empty($value) && !is_string($value)) {
        return false;
    }


    if ($reids->hashExists($name, $key)) {
        $reids->hashDel($name, $key);
    }

    $data[$key] = $value;
    if ($reids->hashSet($name, $data)) {
        if ($Expire) {
            $reids->setKeyExpire($name, $Expire);
        }
        return true;
    } else {
        return false;
    }
}

/*
 * 删除缓存
 *
 * @param str $name 带*字 批量删除缓存
 * @param str $key
 * @param str $value
 * @return string
 * @author jhw
 */

function redisDel($name) {
    $reids = new phpredis();

    if (strpos('*', $name) !== false) {
        $keys = $reids->getKeys($name);
    } else {
        $keys = [$name];
    }
    if ($reids->delete($keys)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 缓存Hash存放
 *
 * @param str $name
 * @param str $key
 * @param str $value
 * @return string
 * @author jhw
 */

function redisHashDel($name, $key) {
    $reids = new phpredis();

    if ($reids->hashDel($name, $key)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 缓存Hash获取
 *
 * @param str $name
 * @param str $key
 * @return string
 * @author jhw
 */

function redisHashGet($name, $key) {
    $reids = new phpredis();

    $string = $reids->hashGet($name, $key);
    if ($string) {
        return $string;
    } else {
        return false;
    }
}

/*
 * 缓存判断是否存在
 *
 * @param str $name
 * @param str $key
 * @return string
 * @author jhw
 */

function redisHashExist($name, $key) {
    $reids = new phpredis();

    if ($reids->hashExists($name, $key)) {
        return true;
    } else {
        return false;
    }
}

/*
 * 缓存存放
 *
 * @param str $name
 * @param str $key
 * @param str $value
 * @return string
 * @author jhw
 */

function redisSet($name, $value, $second = 0) {
    $reids = new phpredis();
    if (empty($name) && !is_string($name)) {
        return false;
    }
    if (empty($value) && !is_string($value)) {
        return false;
    }

    if ($reids->exists($name)) {
        $reids->delete($name);
    }
    $second = intval($second);
    if (is_int($second) && $second > 0) {
        $result = $reids->set($name, $value, 0, 0, $second);
    } else {
        $result = $reids->set($name, $value);
    }
    if ($result) {
        return true;
    } else {
        return false;
    }
}

/*
 * 缓存获取
 *
 * @param str $name
 * @param str $key
 * @param str $value
 * @return string
 * @author jhw
 */

function redisGet($name) {
    $reids = new phpredis();

    $result = $reids->get($name);
    if ($result) {
        return $result;
    } else {
        return false;
    }
}

/*
 * 缓存判断是否存在
 *
 * @param str $name
 * @param str $key
 * @return string
 * @author jhw
 */

function redisExist($name) {
    $reids = new phpredis();

    if ($reids->exists($name)) {
        return true;
    } else {
        return false;
    }
}

/*
 * EXW合计
 *
 * @param str $data 采购单价[busyer_unit_price] ,数量[num]
 * @param float $gross 毛利率
 * @param float $exchange_rate 汇率
 * @author jhw
 */

function exw($data, $gross, $exchange_rate = 1) {
    if (empty($data)) {
        $arr['code'] = 0;
        $arr['msg'] = '采购信息不能为空';
        return $arr;
    }
    if (empty($gross)) {
        $arr['code'] = 0;
        $arr['msg'] = '毛利率不能为空';
        return $arr;
    }

    $count = count($data);
    $data['code'] = 1;
    $data['total_exw_price'] = 0;
    for ($i = 0; $i < $count; $i++) {
        $data[$i]['exw_unit_price'] = round($data[$i]['busyer_unit_price'] * $gross / $exchange_rate, 8);
        $data[$i]['total_exw_price'] = round($data[$i]['exw_unit_price'] * $data[$i]['num'], 8);
        $data['total_exw_price'] = $data['total_exw_price'] + $data[$i]['total_exw_price'];
    }
    return $data;
}

/*
 * 物流报价
 *
 * @param str $data[ trade_terms ] 贸易方式 -- 必填  EXW、FCA、FAS、FOB、CPT、CFR、CIF、CIP、DAP、DAT、DDP
 * @param float $data[ total_exw_price ] EXW合计 -- 必填
 * @param float $data[ inspection_fee ] 商检费 -- 必填
 * @param float $data[ premium_rate ] 保险税率 -- 必填
 * @param float $data[ payment_received_days ] 回款周期 -- 必填
 * @param float $data[ bank_interest ] 银行利息 -- 必填
 * @param float $data[ fund_occupation_rate ] 资金占用比例-- 必填
 * @param float $data[ land_freight ] 陆运费
 * @param float $data[ overland_insu_rate ] 陆运险率
 * @param float $data[ dest_delivery_charge ] 目的地送货费
 * @param float $data[ dest_tariff_rate ] 目的地送货费
 * @param float $data[ dest_va_tax_rate ] 目的地送货费
 * @param float $data[ dest_clearance_fee ] 目的地送货费
 * @author jhw
 */

function logistics($data) {
    $arr = [];
    if (empty($data['trade_terms'])) {
        $arr['code'] = 0;
        $arr['msg'] = '贸易方式不能为空';
        return $arr;
    }
    if (empty($data['bank_interest'])) {
        $arr['code'] = 0;
        $arr['msg'] = '银行利息不能为空';
        return $arr;
    }
    if (empty($data['fund_occupation_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '资金占用比例不能为空';
        return $arr;
    }
    if (empty($data['payment_received_days'])) {
        $arr['code'] = 0;
        $arr['msg'] = '回款周期不能为空';
        return $arr;
    }
    if (empty($data['premium_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '保险税率不能为空';
        return $arr;
    }
    if (empty($data['total_exw_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    if (empty($data['inspection_fee'])) {
        $arr['code'] = 0;
        $arr['msg'] = '商检费不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == 'EXW') {
        $arr['code'] = 1;
        $arr['total_logi_fee'] = $data['inspection_fee'];
        $arr['total_quote_price'] = round(($data['inspection_fee'] + $data['total_exw_price']) / (1 - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365), 8);
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['land_freight'])) {
        $arr['code'] = 0;
        $arr['msg'] = '陆运费不能为空';
        return $arr;
    }
    if (empty($data['overland_insu_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '陆运险率不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == 'FCA' || $data['trade_terms'] == 'FAS') {
        $arr['code'] = 1;
        $arr['inland_marine_insurance'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insurance'] + $data['land_freight'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee']) / (1 - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365), 8);
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['port_surcharge'])) {
        $arr['code'] = 0;
        $arr['msg'] = '港杂费不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == 'FOB') {
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee']) / (1 - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365), 8);
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['inter_shipping'])) {
        $arr['code'] = 0;
        $arr['msg'] = '国际运输费不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == 'CPT' || $data['trade_terms'] == 'CFR') {
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee']) / (1 - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365), 8);
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['cargo_insurance_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '货物运输险率不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == 'CIF' || $data['trade_terms'] == 'CIP') {
        $arr['code'] = 1;
        $numerator = $data['total_exw_price'] + $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $denominator = (1 - 1.1 * $data['cargo_insurance_rate'] - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365);
        $arr['inland_marine_insu'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        if ($numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator < 8 && $numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator != 0) {
            $arr['total_quote_price'] = round(($numerator + 8) / (1 - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365), 8);
        } else {
            $arr['total_quote_price'] = round($numerator / $denominator, 8);
        }
        $arr['freightage_insu'] = $arr['total_quote_price'] * 1.1 * $data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] + $arr['freightage_insu'];
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['dest_delivery_charge'])) {
        $arr['code'] = 0;
        $arr['msg'] = '目的地送货费不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == ' DAP' || $data['trade_terms'] == 'DAT') {
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        $numerator = $data['total_exw_price'] + $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] + $data['dest_delivery_charge'];
        $denominator = round(1 - 1.1 * $data['cargo_insurance_rate'] - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365, 8);
        if ($numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator < 8 && $numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator != 0) {
            $arr['total_quote_price'] = round(($numerator + 8) / $denominator, 8);
        } else {
            $arr['total_quote_price'] = round($numerator / $denominator, 8);
        }
        $arr['freightage_insu'] = $arr['total_quote_price'] * 1.1 * $data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] + $arr['freightage_insu'] + $data['dest_delivery_charge'];
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
    if (empty($data['dest_delivery_charge'])) {
        $arr['code'] = 0;
        $arr['msg'] = '目的地送货费不能为空';
        return $arr;
    }
    if (empty($data['dest_tariff_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '目的地关税税率不能为空';
        return $arr;
    }
    if (empty($data['dest_va_tax_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '目的地增值税税率不能为空';
        return $arr;
    }
    if (empty($data['dest_clearance_fee'])) {
        $arr['code'] = 0;
        $arr['msg'] = '目的地清关费不能为空';
        return $arr;
    }
    if ($data['trade_terms'] == ' DDP') {
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance(['overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price']]);
        $numerator = $data['total_exw_price'] + $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $numerator = $numerator * (1 + $data['dest_tariff_rate']) * (1 + $data['dest_va_tax_rate']) + $data['dest_delivery_charge'] + $data['dest_clearance_fee'];
        $denominator = round(1 - 1.1 * $data['cargo_insurance_rate'] - $data['premium_rate'] - $data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365, 8);
        if ($numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator < 8 && $numerator * 1.1 * $data['cargo_insurance_rate'] / $denominator != 0) {
            $arr['total_quote_price'] = round(($numerator + 8) / $denominator, 8);
        } else {
            $arr['total_quote_price'] = round($numerator / $denominator, 8);
        }
        //目的地关税
        $arr['dest_tariff'] = $arr['total_exw_price'] + $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $arr['dest_va_tax'] = $arr['dest_tariff'];
        $arr['dest_tariff'] = round($arr['dest_tariff'] * $data['dest_tariff_rate'], 8);
        //  目的地增值税
        $arr['dest_va_tax'] = round($arr['dest_va_tax'] * (1 + $data['dest_tariff_rate']) * $data['dest_va_tax_rate'], 8);
        $arr['freightage_insu'] = $arr['total_quote_price'] * 1.1 * $data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] + $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] + $arr['freightage_insu'] + $data['dest_delivery_charge'];
        $arr['total_logi_fee'] = $arr['total_logi_fee'] + $arr['dest_tariff'] + $arr['dest_va_tax'] + $data['dest_clearance_fee'];
        $arr['total_bank_fee'] = round($arr['total_quote_price'] * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_received_days'] / 365, 8);
        return $arr;
    }
}

/*
 * 报出单价
 *
 * @param str $data[ total_quote_price ] 报出总价
 * @param float $data[ total_exw_price ] EXW合计 -- 必填
 * @param float $data[ exw_unit_price ] EXW单价 -- 必填
 * @author jhw
 */

function quoteUnitPrice($data) {
    if (empty($data['total_exw_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    if (empty($data['exw_unit_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = 'EXW单价不能为空';
        return $arr;
    }
    if (empty($data['total_quote_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = '报出总价不能为空';
        return $arr;
    }
    $arr['code'] = 1;
    $arr['quote_unit_price'] = round($data['total_quote_price'] * $data['exw_unit_price'] / $data['total_exw_price'], 8);
    return $arr;
}

/*
 * 陆运险计算
 *
 * @param float $data[ total_exw_price ] EXW合计 -- 必填
 * @param float $data[ overland_insu_rate ] 陆运险率
 * @author jhw
 */

function inlandMarineInsurance($data) {
    if (empty($data['overland_insu_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '陆运险率不能为空';
        return $arr;
    }
    if (empty($data['total_exw_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    return round($data['overland_insu_rate'] * 1.1 * $data['total_exw_price'], 8);
}

/*
 * 货物运输保险计算
 *
 * @param float $data[ total_price ] 报价合计 -- 必填
 * @param float $data[ cargo_insurance_rate ] 货物运输险率
 * @author jhw
 */

function freightage_insurance($data) {
    if (empty($data['cargo_insurance_rate'])) {
        $arr['code'] = 0;
        $arr['msg'] = '货物运输险率不能为空';
        return $arr;
    }
    if (empty($data['total_price'])) {
        $arr['code'] = 0;
        $arr['msg'] = '报价合计不能为空';
        return $arr;
    }
    return round($data['cargo_insurance_rate'] * 1.1 * $data['total_price'], 8);
}

/**
 * 浏览器语言
 * 目前只处理中(zn)英(en)俄(ru)西班牙(es)语
 * @author link
 * @return string
 */
function browser_lang() {
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
    $language = '';
    if (preg_match("/zh-c|zh/i", $lang)) {
        $language = 'zh';
    } else if (preg_match("/en/i", $lang)) {
        $language = 'en';
    } else if (preg_match("/es/i", $lang)) {
        $language = 'es';
    } else if (preg_match("/ru/i", $lang)) {
        $language = 'ru';
    }
    return $language;
}

/**
 * json输出
 * @author link
 * @param array $data    返回值
 * @param int $code    错误编码
 * @param string $message    错误提示
 * @param string $lang
 */
function jsonReturn($data, $code = 1, $message = '', $lang = 'zh') {
    @header('Content-Type:application/json; charset=utf-8');
    $returnData = [];
    if (isset($data['data'])) {
        $returnData = $data;
    }else{
        $returnData['data'] =  $code == 1 ? $data : false;
    }
    $returnData['code'] = $code;
    $returnData['message'] = ErrorMsg::getMessage($code, $message, $lang);
    exit(json_encode($returnData, JSON_UNESCAPED_UNICODE));
}

/**
 * 生成spu编码
 * 这里临时生成七位数字，后期根据具体需求改动
 * @author link  2017-06-22
 */
function createSpu() {
    $rand = rand(0, 9999999);
    return str_pad($rand, 7, "0", STR_PAD_LEFT);
}

/**
 * 生成二维码
 * 这里事先预留了个二维码扩展，方便后期使用与维护（待实现）
 * @author link  2017-06-22
 * @param string $url
 * @param string $logo logo图地址
 * @param int $msize 二维码大小
 * @param char $error_level 容错级别
 * @param string 二维码地址
 */
function createQrcode($url = '', $logo = '', $msize = 6, $error_level = 'L') {
    if (empty($url))
        return '';

    return '';
}

/**
 * 获取当前用户信息
 * @author link    2017-06-22
 * @return array|bool
 */
function getLoinInfo() {

    return $GLOBALS['SSO_USER'];
}

/**
 * 获取自定义header数据
 * @author link 2017-08-09
 */
function getHeaders() {
    $ignore = array('host', 'accept', 'content-length', 'content-type'); // 忽略数据
    $headers = array();
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $key = substr($key, 5);
            $key = strtolower($key);
            if (!in_array($key, $ignore)) {
                $headers[$key] = $value;
            }
        }
    }

    return $headers;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0, $adv = true) {
    $type = $type ? 1 : 0;
    static $ip = NULL;
    if ($ip !== NULL)
        return $ip[$type];
    if ($adv) {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_IP'])) {
            $ip = $_SERVER['HTTP_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos)
                unset($arr[$pos]);
            $ip = trim($arr[0]);
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/*
 * $ip string IP 地址
 *
 *
 */

function getIpAddress($ip) {
    if ($ip == "127.0.0.1")
        return '中国';
    $ipContent = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=$ip");
    $arr = json_decode($ipContent, true); //解析json

    return $arr['country'];
}

/*
 * 发送邮件
 *
 * @param float $data[ total_price ] 报价合计 -- 必填
 * @param float $data[ cargo_insurance_rate ] 货物运输险率
 * @author jhw
 *
 */

function send_Mail($to, $title, $body, $name = null) {
    $mail = new PHPMailer(true);
    $mail->IsSMTP(); // 使用SMTP
    $config_obj = Yaf_Registry::get("config");
    $config_db = $config_obj->mail->toArray();
    try {
        $mail->CharSet = "UTF-8"; //设定邮件编码
        $mail->Host = $config_db['host']; // SMTP server
        $mail->SMTPDebug = 1;                     // 启用SMTP调试 1 = errors  2 =  messages
        $mail->SMTPAuth = true;                  // 服务器需要验证
        $mail->Port = $config_db['port'];    //默认端口
        $mail->Username = $config_db['username']; //SMTP服务器的用户帐号
        $mail->Password = $config_db['password'];        //SMTP服务器的用户密码
        $mail->AddAddress($to, $name); //收件人如果多人发送循环执行AddAddress()方法即可 还有一个方法时清除收件人邮箱ClearAddresses()
        $mail->SetFrom($config_db['setfrom'], 'ERUI'); //发件人的邮箱
        //$mail->AddAttachment('./img/bloglogo.png');      // 添加附件,如果有多个附件则重复执行该方法
        $mail->Subject = $title;
        //以下是邮件内容
        $mail->Body = $body;
        $mail->IsHTML(true);

        //$body = file_get_contents('tpl.html'); //获取html网页内容
        //$mail->MsgHTML(str_replace('\\','',$body));
        $mail->Send();
        return ['code' => 1];
    } catch (phpmailerException $e) {
        return ['code' => -1, 'msg' => $e->errorMessage()];
    } catch (Exception $e) {
        return ['code' => -1, 'msg' => $e->errorMessage()];
    }
}

/**
 * 格式化打印函数
 * @author 买买提
 * @param $var
 */
function p($var) {
    header('Content-type:text/html;charset=utf8');
    echo "<pre style='background: #f3f3f3;padding:15px;'>";
    print_r($var);
    echo "</pre>";
    die;
}

function Pinyin($_String, $_Code = 'utf8') {
    $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" .
            "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" .
            "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" .
            "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" .
            "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" .
            "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" .
            "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" .
            "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" .
            "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" .
            "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" .
            "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" .
            "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" .
            "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" .
            "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" .
            "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" .
            "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";
    $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" .
            "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" .
            "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" .
            "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" .
            "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" .
            "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" .
            "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" .
            "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" .
            "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" .
            "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" .
            "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" .
            "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" .
            "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" .
            "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" .
            "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" .
            "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" .
            "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" .
            "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" .
            "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" .
            "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" .
            "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" .
            "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" .
            "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" .
            "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" .
            "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" .
            "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" .
            "|-10270|-10262|-10260|-10256|-10254";
    $_TDataKey = explode('|', $_DataKey);
    $_TDataValue = explode('|', $_DataValue);
    $_Data = (PHP_VERSION >= '5.0') ? array_combine($_TDataKey, $_TDataValue) : _Array_Combine($_TDataKey, $_TDataValue);
    arsort($_Data);
    reset($_Data);
    if ($_Code != 'gb2312')
        $_String = _U2_Utf8_Gb($_String);
    $_Res = '';
    for ($i = 0; $i < strlen($_String); $i++) {
        $_P = ord(substr($_String, $i, 1));
        if ($_P > 160) {
            $_Q = ord(substr($_String, ++$i, 1));
            $_P = $_P * 256 + $_Q - 65536;
        }
        $_Res .= _Pinyin($_P, $_Data);
    }
    return preg_replace("/[^a-z0-9]*/", '', $_Res);
}

function _Pinyin($_Num, $_Data) {
    if ($_Num > 0 && $_Num < 160)
        return chr($_Num);
    elseif ($_Num < -20319 || $_Num > -10247)
        return '';
    else {
        foreach ($_Data as $k => $v) {
            if ($v <= $_Num)
                break;
        }
        return $k;
    }
}

function _U2_Utf8_Gb($_C) {
    $_String = '';
    if ($_C < 0x80)
        $_String .= $_C;
    elseif ($_C < 0x800) {
        $_String .= chr(0xC0 | $_C >> 6);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x10000) {
        $_String .= chr(0xE0 | $_C >> 12);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    } elseif ($_C < 0x200000) {
        $_String .= chr(0xF0 | $_C >> 18);
        $_String .= chr(0x80 | $_C >> 12 & 0x3F);
        $_String .= chr(0x80 | $_C >> 6 & 0x3F);
        $_String .= chr(0x80 | $_C & 0x3F);
    }
    return iconv('UTF-8', 'GB2312', $_String);
}

function _Array_Combine($_Arr1, $_Arr2) {
    for ($i = 0; $i < count($_Arr1); $i++)
        $_Res[$_Arr1[$i]] = $_Arr2[$i];
    return $_Res;
}

/* 查询条件判断
 * @param array $where // 搜索条件
 * @param array $condition // 搜索条件
 * @param string $name // 查询的字段
 * @param string $type // 默认值 string bool  like array
 * @param string $field // 组合条件的字段
 */

function getValue(&$where, &$condition, $name, $type = 'string', $field = null) {
    if (!$field) {
        $field = $name;
    }
    if ($type === 'string') {
        if (isset($condition[$name]) && trim($condition[$name])) {
            $where[$field] = trim($condition[$name]);
        }
    } elseif ($type === 'bool') {
        if (isset($condition[$name]) && trim($condition[$name])) {
            $flag = trim($condition[$name]) == 'Y' ? 'Y' : 'N';
            $where[$field] = $flag;
        }
    } elseif ($type === 'like') {
        if (isset($condition[$name]) && trim($condition[$name])) {
            $where[$field] = ['like', '%' . trim($condition[$name]) . '%'];
        }
    } elseif ($type === 'array') {
        if (isset($condition[$name]) && is_array($condition[$name])) {
            $where[$field] = ['in', $condition[$name]];
        }
    }
}

/**
 * 生产随机数
 * @param int $len
 * @param string $prefix    前缀
 * @return string
 */
function randNumber($len = 6, $prefix = '') {
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= rand(0, 9);
    }
    return $prefix . str_pad($str, 6, '0', STR_PAD_LEFT);
}

/**
 * 获取和设置配置参数 支持批量定义
 * @param string|array $name 配置变量
 * @param mixed $value 配置值
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name = null, $value = null, $default = null) {
    static $_config = array();
    // 无参数时获取所有
    if (empty($name)) {
        return $_config;
    }
    // 优先执行设置获取或赋值
    if (is_string($name)) {
        if (!strpos($name, '.')) {
            $name = strtoupper($name);
            if (is_null($value)) {
                return isset($_config[$name]) ? $_config[$name] : $default;
            }
            $_config[$name] = $value;
            return null;
        }
        // 二维数组设置和获取支持
        $name = explode('.', $name);
        $name[0] = strtoupper($name[0]);
        if (is_null($value)) {
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
        }
        $_config[$name[0]][$name[1]] = $value;
        return null;
    }
    // 批量设置
    if (is_array($name)) {
        $_config = array_merge($_config, array_change_key_case($name, CASE_UPPER));
        return null;
    }
    return null; // 避免非法参数
}

/**
 * 获取和设置语言定义(不区分大小写)
 * @param string|array $name 语言变量
 * @param mixed $value 语言值或者变量
 * @return mixed
 */
function L($name=null, $value=null) {
    static $_lang = array();
    // 空参数返回所有定义
    if (empty($name))
        return $_lang;
        // 判断语言获取(或设置)
        // 若不存在,直接返回全大写$name
        if (is_string($name)) {
            $name   =   strtoupper($name);
            if (is_null($value)){
                return isset($_lang[$name]) ? $_lang[$name] : $name;
            }elseif(is_array($value)){
                // 支持变量
                $replace = array_keys($value);
                foreach($replace as &$v){
                    $v = '{$'.$v.'}';
                }
                return str_replace($replace,$value,isset($_lang[$name]) ? $_lang[$name] : $name);
            }
            $_lang[$name] = $value; // 语言定义
            return null;
        }
        // 批量定义
        if (is_array($name))
            $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
        return null;
}

/**
 * 上传文件至FastDFS
 * @param string $file 本地文件信息
 * @param string $url  上传接口地址
 * */
function postfile($data, $url, $timeout = 30) {
    $cfile = new \CURLFile($data['tmp_name'], $data['type'], $data['name']);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    $cookies = "eruitoken=" . $GLOBALS['SSO_TOKEN'];
    curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['upFile' => $cfile]);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        print_r(curl_error($ch));
        Log::write('Curl error: ' . curl_error($ch), LOG_ERR);
        return [];
    }
    curl_close($ch);
    $cfile = null;
    unset($cfile);
    return json_decode($response, true);
}

/**
 * 编码转换成gbk
 * @param $data 数据数组
 */
function toGbk($data = []) {
    $return = [];
    foreach ($data as $k => $r) {
        $return[$k] = iconv('UTF-8', 'GBK', $r);
    }
    return $return;
}

/**
 * 判断sku编码
 * @param $bool
 */
function isNum($str) {
    $mode = '/^[0-9]{16}$/';
    if (preg_match($mode, $str)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 检测是否含有中文
 * @author link    2017-11-01
 * @param $str
 * @return string
 */
function haveZh($str) {
    if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $str)) {
        return true;
    } else {
        return false;
    }
}

/**
 * @desc 搜索目录下所有文件
 *
 * @param string $path 搜索路径
 * @param array $files 文件路径
 * @author liujf
 * @time 2018-01-25
 */
function searchDir($path, &$files) {
    if (is_dir($path)) {
        $dp = dir($path);
        while($file = $dp->read()) {
            if($file != '.' && $file != '..') searchDir(addSlash($path) . $file, $files);
        }
        $dp->close();
    }
    if(is_file($path)) $files[] = $path;
}

/**
 * @desc 加上目录连接斜线
 *
 * @param string $dir 需处理的目录
 * @return string
 * @author liujf
 * @time 2018-01-25
 */
function addSlash($dir) {
    if (!preg_match('/.*[\\\\\/]$/s', $dir)) $dir .= DS;
    return $dir;
}

/**
 * @desc 去掉数据两侧的空格
 *
 * @param mixed $data
 * @return mixed
 * @author liujf
 * @time 2018-02-02
 */
function dataTrim($data) {
    if (is_array($data)) {
        foreach ($data as $k => $v) $data[$k] = dataTrim($v);
        return $data;
    } else if (is_object($data)) {
        foreach ($data as $k => $v) $data->$k = dataTrim($v);
        return $data;
    } else if (is_string($data)) {
        return trim($data);
    } else {
        return $data;
    }
}

/**
 * @desc 获取UTF-8编码字符串长度
 *
 * @param string $str
 * @return int
 * @author liujf
 * @time 2018-02-08
 */ 
function strlenUtf8($str) {
    // 将字符串分解为单元
    preg_match_all('/./us', $str, $match);
    // 返回单元个数
    return count($match[0]);
}

/**
 * @desc 时间戳转日期
 *
 * @param int $num
 * @param string $format
 * @return string
 * @author liujf
 * @time 2018-03-15
 */
function timeStampToDate($num, $format = 'Y-m-d H:i:s') {
    $d = new DateTime('@' . $num);
    $d->setTimezone(new DateTimeZone('PRC'));
    return $d->format($format);
}

/**
 * @desc 日期转时间戳
 *
 * @param string $str
 * @return int
 * @author liujf
 * @time 2018-03-15
 */
function dateToTimeStamp($str) {
    $d = new DateTime($str);
    return  $d->format('U');
}

/**
 * @desc 判断参数是否为数字或小数
 *
 * @param mixed $param
 * @return bool
 * @author liujf
 * @time 2018-03-28
 */
function isDecimal($param) {
    return !!preg_match('/^\s*((\d+\.\d*)|(\d*\.\d+)|(\d+))\s*$/', $param);
}

/**
 * 多维数组排序
 * @param $data
 * @param $sort_order_field
 * @param int $sort_order
 * @param int $sort_type
 */
function my_array_multisort($data,$sort_order_field,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC){
    if(is_array($data)){
        foreach($data as $val){
            $key_arrays[]=$val[$sort_order_field];
        }
        array_multisort($key_arrays,$sort_order,$sort_type,$data);
        return $data;
    }
    return [];
}

/**
 * json解析
 * @author link
 * @param $arr
 * @param string $field
 */
function jsonDecode(&$arr, $field = ''){
    if(!empty($field)){
        $arr[$field] = json_decode($arr[$field],true);
    }
}

/**
 * YN转true false
 * @author link
 * @param $arr
 * @param string $field
 */
function  ynTotruefalse(&$arr, $field = ''){
    if(!empty($field)){
        $arr[$field] = ( $arr[$field]=='1' || $arr[$field]=='Y') ? true : false;
    }
}