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

    $str = htmlspecialchars($str);
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
 * @param    string     $time   间隔时间
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
function randStr($len)
{
    $chars='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz'; // characters to build the password from
    $string='';
    for(;$len>=1;$len--)
    {
        $position=rand()%strlen($chars);
        $string.=substr($chars,$position,1);
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
function isEmail($email)
{
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
function isMobile($mobile){
    if(preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
        return true;
    }else{
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
function redisHashSet($name,$key,$value){

    $reids = new phpredis();
    if(empty($name) && !is_string($name)){
        return false;
    }
    if(empty($key) && !is_string($name)){
        return false;
    }
    if(empty($value) && !is_string($value)){
        return false;
    }

    if($reids->hashExists($name,$key)){
        $reids->hashDel($name,$key);
    }

    $data[$key] = $value;
    if($reids->hashSet($name,$data)){
        return true;
    }else{
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
function redisHashGet($name,$key){
    $reids = new phpredis();
    $string = $reids->hashGet($name,$key);
    if($string){
        return $string;
    }else{
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
function redisHashExist($name,$key){
    $reids = new phpredis();
    if($reids->hashExists($name,$key)){
        return true;
    }else{
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
function redisSet($name,$value,$second = 0){
    $reids = new phpredis();
    if(empty($name) && !is_string($name)){
        return false;
    }
    if(empty($value) && !is_string($value)){
        return false;
    }
    if($reids->exists($name)){
        $reids->delete($name);
    }
    if(is_int($second) && $second>0){
        $result = $reids->set($name,$value,$second);
    }else{
        $result = $reids->set($name,$value);
    }
    if($result){
        return true;
    }else{
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
function redisGet($name){
    $reids = new phpredis();
    $result = $reids->get($name);
    if($result){
        return $result;
    }else{
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
function redisExist($name){
    $reids = new phpredis();
    if($reids->exists($name)){
        return true;
    }else{
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
function exw($data,$gross,$exchange_rate = 1){
    if(empty($data)){
        $arr['code'] = 0;
        $arr['msg'] = '采购信息不能为空';
        return $arr;
    }
    if(empty($gross)){
        $arr['code'] = 0;
        $arr['msg'] = '毛利率不能为空';
        return $arr;
    }

    $count=count($data);
    $data['code'] = 1;
    $data['total_exw_price']=0;
    for($i = 0;$i<$count;$i++){
        $data[$i]['exw_unit_price'] = round($data[$i]['busyer_unit_price']* $gross/$exchange_rate,8);
        $data[$i]['total_exw_price'] = round($data[$i]['exw_unit_price'] * $data[$i]['num'],8);
        $data['total_exw_price'] = $data['total_exw_price']+$data[$i]['total_exw_price'];
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
function logistics($data){
    $arr = [];
    if(empty($data['trade_terms'])){
        $arr['code'] = 0;
        $arr['msg'] = '贸易方式不能为空';
        return $arr;
    }
    if(empty($data['bank_interest'])){
        $arr['code'] = 0;
        $arr['msg'] = '银行利息不能为空';
        return $arr;
    }
    if(empty($data['fund_occupation_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '资金占用比例不能为空';
        return $arr;
    }
    if(empty($data['payment_received_days'])){
        $arr['code'] = 0;
        $arr['msg'] = '回款周期不能为空';
        return $arr;
    }
    if(empty($data['premium_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '保险税率不能为空';
        return $arr;
    }
    if(empty($data['total_exw_price'])){
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    if(empty($data['inspection_fee'])){
        $arr['code'] = 0;
        $arr['msg'] = '商检费不能为空';
        return $arr;
    }
    if($data['trade_terms'] == 'EXW'){
        $arr['code'] = 1;
        $arr['total_logi_fee'] = $data['inspection_fee'];
        $arr['total_quote_price'] = round(($data['inspection_fee'] + $data['total_exw_price'])/(1-$data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365),8);
        return $arr;
    }
    if(empty($data['land_freight'])){
        $arr['code'] = 0;
        $arr['msg'] = '陆运费不能为空';
        return $arr;
    }
    if(empty($data['overland_insu_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '陆运险率不能为空';
        return $arr;
    }
    if($data['trade_terms'] == 'FCA' || $data['trade_terms'] == 'FAS'){
        $arr['code'] = 1;
        $arr['inland_marine_insurance'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insurance'] +  $data['land_freight'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee'])/(1-$data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365),8);
        return $arr;
    }
    if(empty($data['port_surcharge'])){
        $arr['code'] = 0;
        $arr['msg'] = '港杂费不能为空';
        return $arr;
    }
    if($data['trade_terms'] == 'FOB'){
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] +  $data['port_surcharge'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee'])/(1-$data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365),8);
        return $arr;
    }
    if(empty($data['inter_shipping'])){
        $arr['code'] = 0;
        $arr['msg'] = '国际运输费不能为空';
        return $arr;
    }
    if($data['trade_terms'] == 'CPT' || $data['trade_terms'] == 'CFR'){
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $arr['total_quote_price'] = round(($data['total_exw_price'] + $arr['total_logi_fee'])/(1-$data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365),8);
        return $arr;
    }
    if(empty($data['cargo_insurance_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '货物运输险率不能为空';
        return $arr;
    }
    if($data['trade_terms'] == 'CIF' || $data['trade_terms'] == 'CIP'){
        $arr['code'] = 1;
        $numerator = $data['total_exw_price'] +$data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $denominator = (1 - 1.1 * $data['cargo_insurance_rate'] - $data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365);
        $arr['inland_marine_insu'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        if($numerator*1.1 * $data['cargo_insurance_rate']/$denominator < 8 && $numerator*1.1 * $data['cargo_insurance_rate']/$denominator !=0){
            $arr['total_quote_price'] = round(($numerator +8) / (1 - $data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365),8);
        }else{
            $arr['total_quote_price'] = round($numerator/$denominator,8);
        }
        $arr['freightage_insu'] = $arr['total_quote_price'] *1.1*$data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping']+$arr['freightage_insu'];
        return $arr;
    }
    if(empty($data['dest_delivery_charge'])){
        $arr['code'] = 0;
        $arr['msg'] = '目的地送货费不能为空';
        return $arr;
    }
    if($data['trade_terms'] == ' DAP' || $data['trade_terms'] == 'DAT'){
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        $numerator = $data['total_exw_price'] +$data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] + $data['dest_delivery_charge'];
        $denominator = round(1 - 1.1 * $data['cargo_insurance_rate'] -  $data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365 ,8);
        if($numerator * 1.1 * $data['cargo_insurance_rate']/$denominator <8 && $numerator * 1.1 * $data['cargo_insurance_rate']/$denominator != 0){
            $arr['total_quote_price'] = round(($numerator+8)/$denominator,8);
        }else{
            $arr['total_quote_price'] = round($numerator/$denominator,8);
        }
        $arr['freightage_insu'] = $arr['total_quote_price'] *1.1*$data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping']+$arr['freightage_insu']+$data['dest_delivery_charge'];
        return $arr;
    }
    if(empty($data['dest_delivery_charge'])){
        $arr['code'] = 0;
        $arr['msg'] = '目的地送货费不能为空';
        return $arr;
    }
    if(empty($data['dest_tariff_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '目的地关税税率不能为空';
        return $arr;
    }
    if(empty($data['dest_va_tax_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '目的地增值税税率不能为空';
        return $arr;
    }
    if(empty($data['dest_clearance_fee'])){
        $arr['code'] = 0;
        $arr['msg'] = '目的地清关费不能为空';
        return $arr;
    }
    if($data['trade_terms'] == ' DDP' ){
        $arr['code'] = 1;
        $arr['inland_marine_insu'] = inlandMarineInsurance([ 'overland_insu_rate' => $data['overland_insu_rate'], 'total_exw_price' => $data['total_exw_price'] ]);
        $numerator = $data['total_exw_price'] +$data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'] ;
        $numerator = $numerator * (1+$data['dest_tariff_rate']) * (1+$data['dest_va_tax_rate']) + $data['dest_delivery_charge'] + $data['dest_clearance_fee'];
        $denominator = round(1 - 1.1 * $data['cargo_insurance_rate'] -  $data['premium_rate']-$data['payment_received_days'] * $data['bank_interest'] * $data['fund_occupation_rate']/365 ,8);
        if($numerator * 1.1 * $data['cargo_insurance_rate']/$denominator <8 && $numerator * 1.1 * $data['cargo_insurance_rate']/$denominator != 0){
            $arr['total_quote_price'] = round(($numerator+8)/$denominator,8);
        }else{
            $arr['total_quote_price'] = round($numerator/$denominator,8);
        }
        //目的地关税
        $arr['dest_tariff'] = $arr['total_exw_price'] + $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping'];
        $arr['dest_va_tax'] = $arr['dest_tariff'];
        $arr['dest_tariff'] = round($arr['dest_tariff'] * $data['dest_tariff_rate'] ,8);
        //  目的地增值税
        $arr['dest_va_tax'] =round($arr['dest_va_tax'] * (1 + $data['dest_tariff_rate']) * $data['dest_va_tax_rate'], 8);
        $arr['freightage_insu'] = $arr['total_quote_price'] *1.1*$data['cargo_insurance_rate'];
        $arr['total_logi_fee'] = $data['inspection_fee'] + $arr['inland_marine_insu'] +  $data['land_freight'] + $data['port_surcharge'] + $data['inter_shipping']+$arr['freightage_insu']+$data['dest_delivery_charge'];
        $arr['total_logi_fee'] = $arr['total_logi_fee'] + $arr['dest_tariff'] + $arr['dest_va_tax'] + $data['dest_clearance_fee'];
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
function quoteUnitPrice($data){
    if(empty($data['total_exw_price'])){
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    if(empty($data['exw_unit_price'])){
        $arr['code'] = 0;
        $arr['msg'] = 'EXW单价不能为空';
        return $arr;
    }
    if(empty($data['total_quote_price'])){
        $arr['code'] = 0;
        $arr['msg'] = '报出总价不能为空';
        return $arr;
    }
    $arr['code'] = 1;
    $arr['quote_unit_price'] = round($data['total_quote_price'] * $data['exw_unit_price'] /$data['total_exw_price'],8);
    return $arr;
}

/*
 * 陆运险计算
 *
 * @param float $data[ total_exw_price ] EXW合计 -- 必填
 * @param float $data[ overland_insu_rate ] 陆运险率
 * @author jhw
 */
function inlandMarineInsurance($data){
    if(empty($data['overland_insu_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '陆运险率不能为空';
        return $arr;
    }
    if(empty($data['total_exw_price'])){
        $arr['code'] = 0;
        $arr['msg'] = 'EXW合计不能为空';
        return $arr;
    }
    return round($data['overland_insu_rate'] * 1.1 * $data['total_exw_price'],8);
}

/*
 * 货物运输保险计算
 *
 * @param float $data[ total_price ] 报价合计 -- 必填
 * @param float $data[ cargo_insurance_rate ] 货物运输险率
 * @author jhw
 */
function freightage_insurance($data){
    if(empty($data['cargo_insurance_rate'])){
        $arr['code'] = 0;
        $arr['msg'] = '货物运输险率不能为空';
        return $arr;
    }
    if(empty($data['total_price'])){
        $arr['code'] = 0;
        $arr['msg'] = '报价合计不能为空';
        return $arr;
    }
    return round($data['cargo_insurance_rate'] * 1.1 * $data['total_price'],8);
}

/**
 * 浏览器语言
 * 目前只处理中(zn)英(en)俄(ru)西班牙(es)语
 * @return string
 */
function browser_lang(){
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
 * @param array $data    返回值
 * @param int $code    错误编码
 * @param string $message    错误提示
 * @param string $type
 */
function jsonReturn($data,$code=0,$message='', $type = 'JSON') {
    header('Content-Type:application/json; charset=utf-8');
    if($code !=0){
        exit(json_encode(array('code'=>$code,'message'=>$message)));
    }
    $data['code']=0;
    $data['message'] = '成功';
    exit(json_encode($data));
}
