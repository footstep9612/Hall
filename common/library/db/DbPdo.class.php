<?php

class DbPdo {

    static private $mInstance = null;
    static private $mConnection = null;
    static public $mstmt = null;
    static public $mDebug = false;
    static public $mError = null;
    static public $mCount = 0;
    static public $sql = '';
    static public $bind = '';
    static public $debugMsg = array();
    static private $pdo_Instance = null;
    static private $stmt = null;
    static private $comparison = array('eq' => '=', 'neq' => '<>', 'gt' => '>', 'egt' => '>=', 'lt' => '<', 'elt' => '<=', 'notlike' => 'NOT LIKE', 'like' => 'LIKE', 'in' => 'IN', 'not in' => 'NOT IN');

    static public function &Instance() {
        if (null == self::$mInstance) {
            $class = __CLASS__;
            self::$mInstance = new $class;
        }
        return self::$mInstance;
    }

    public static function exception_handler($e) {
        set_exception_handler(array(__CLASS__, 'exception_handler'));
        Logs::Write('Uncaught exception:' . $e->getMessage(), 'pdoerror');
    }

    function __construct() {
        global $config;

        if (isset($config['dbhost']) && $config['dbhost']) {
            $host = (string) $config['dbhost'];
            $user = (string) $config['dbuser'];
            $pass = (string) $config['dbpass'];
            $name = (string) $config['dbname'];
        } else {
            //$config = Config::Instance('php');
            require(DIR_CONFIGURE . DS . 'config.inc.php');
            $host = (string) $config['dbhost'];
            $user = (string) $config['dbuser'];
            $pass = (string) $config['dbpass'];
            $name = (string) $config['dbname'];
        }
        try {



            self::$mConnection = new PDO('mysql:host=' . $host . ';dbname=' . $name, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8 ;"));
            self::$mConnection->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        } catch (PDOException $ex) {
            
        }
    }

    static function GetLinkId() {
        self::Instance();
        return self::$mConnection;
    }

    function __destruct() {
        self::Close();
    }

    static public function Debug() {
        self::$mDebug = !self::$mDebug;
    }

    static public function getErrorInfo() {
        return self::$mConnection->errorInfo();
    }

    /*
     * 数据绑定
     * @param string $bind_item 要绑定的字段
     * @param string $value 绑定的数据
     * 
     */

    static public function Close() {

        self::$mConnection = null;
        self::$mInstance = null;
        self::$mstmt = null;
        self::$stmt = null;
    }

    static public function setmstmt($mstmt = NULL) {


        self::$mstmt = $mstmt;
    }

    static public function EscapeString($string) {
        self::Instance();
        return $string;
    }

    static public function getMicTime() {
        $tmp = explode(' ', microtime());

        return (double) $tmp[1] + (double) $tmp[0];
    }

    static public function Query($sql, $bind = array()) {


        self::Instance();

        if (self::$mDebug) {
            $startTime = self::getMicTime();
        }


        try {
            if ($bind && !empty($bind)) {
                $sql = str_replace("'?'", "?", $sql);
            }
            $stmt = self::$mConnection->prepare($sql);

            $stmt->execute($bind);
            self::$stmt = $stmt;
            self::$sql = $sql;
            return $stmt;

            self::$mCount++;
        } catch (PDOException $ex) {

            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function prepare($sql) {
        self::Instance();
        try {



            self::$mstmt = self::$mConnection->prepare($sql);
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function RollBack() {
        self::Instance();
        try {



            return self::$mConnection->RollBack();
        } catch (PDOException $ex) {


            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function Commit() {
        self::Instance();
        try {



            return self::$mConnection->commit();
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function autoCommit() {
        self::Instance();
        self::$mConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, 1);
    }

    static public function Begin() {
        self::Instance();
        try {
            return self::$mConnection->beginTransaction();
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function bindValue($bind_item, $value) {
        try {
            return self::$mstmt->bindValue($bind_item, $value);
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function bindValues($bind_array) {


        foreach ($bind_array as $key => $item) {

            $key = strpos($key, ':') === FALSE ? ":$key" : $key;


            $flag = self::bindValue($key, $item);
            if (!$flag) {

                return $flag;
            }
        }
        return true;
    }

    static public function runing() {
        try {
            return self::$mstmt->execute();
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function fetchAll($type = PDO::FETCH_ASSOC) {
        try {
            self::runing();
            return self::$mstmt->fetchAll($type);
            self::$mstmt = NULL;
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function PDO_fetch($type = PDO::FETCH_ASSOC) {
        try {

            self::runing();
            return self::$mstmt->fetch($type);
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    static public function execute($sql, $bind = array()) {


        self::Instance();

        if (self::$mDebug) {
            $startTime = self::getMicTime();
        }


        try {
            if ($bind && !empty($bind)) {
                $sql = str_replace("'?'", "?", $sql);
                self::$bind = $bind;
            }



            $stmt = self::$mConnection->prepare($sql);
            self::$sql = $sql;
            $flag = $stmt->execute($bind);
            self::$mError = self::$mConnection->errorInfo();


            return $flag;



            self::$mCount++;
        } catch (PDOException $ex) {
            self::$mError = $ex->getMessage();
            return FALSE;
        }
    }

    public static function Count($n = null, $condition = null, $sum = null, $isdebug = false, $cache = 0,$groupby=NULL) {
        $condition = DbPdo::BuildCondition($condition);
        $condition = null == $condition ? null : "WHERE $condition";
        $zone = $sum ? "SUM({$sum})" : "COUNT(1)";


        $n = strpos($n, '`') === FALSE ? "`$n`" : $n;
        $sql = "SELECT {$zone} AS count FROM {$n} $condition";

        if($groupby){
            
          $sql.=$sql.' GROUP by ' .$groupby;
            
        }

        $row = DbPdo::GetQueryResult($sql, true, $cache);
        return $sum ? (0 + $row['count']) : intval($row['count']);
    }

    static public function num_rows($sql, $cache = 0) {

        self::Instance();
        $mkey = RedisCache::GetStringKey($sql);

        if ($cache > 0) {
            $ret = RedisCache::Get($mkey);

            if ($ret)
                return $ret;
        }
        $stm = self::$mConnection->query($sql);
//        $count =$stm-> ;
        if ($count && $cache > 0)
            RedisCache::Set($mkey, $count, 0, $cache);
        return $count;
    }

    static public function NextRecord($query) {
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $query->nextRowset();
        return $result;
    }

    static public function GetTableRow($table, $condition, $cache = 0) {
        return self::LimitQuery($table, array(
                    'condition' => $condition,
                    'one' => true,
                    'cache' => $cache,
        ));
    }

    static public function GetDbRowById($table, $ids = array(), $cache = 0) {
        $one = is_array($ids) ? false : true;
        
        settype($ids, 'array');
        $idstring = join('\',\'', $ids);
        if (preg_match('/[\s]/', $idstring))
            return array();
        $table = strpos($table, '`') === FALSE ? "`$table`" : $table;
        $q = "SELECT * FROM {$table} WHERE id IN ('{$idstring}')";
        $r = self::GetQueryResult($q, $one, $cache);
        if ($one)
            return $r;
        return Utility::AssColumn($r, 'id');
    }

    /**
     * 数据库常用查询函数
     * @param string $table 表名
     * @param array $options 查询条件
     *    <code>
     *    $options = array(
     *          'select' => 'id,name,title', // 返回字段（ * 为全部字段 慎用）
     *          'condition' => array('id'=>'1',....), // where 下的条件
     *          'one' => true, // 只查询一条
     *          'offset' => 1, // 结果期偏移起始位置
     *          'size' => 5,   // 查询偏移量后的几条数据
     *          'order' => 'id desc,time asc', // 排序
     *          'cache' => 0 // 缓存，目前没用
     *    );
     *    </code>
     * @return array $data
     * @access public
     */
    static public function LimitQuery($table, $options = array(), $isDebug = false, $bind = array()) {

        return self::Select($table, $options, $bind);
    }

    /**
     * 数据库常用查询函数
     * @param string $table 表名
     * @param array $options 查询条件
     *    <code>
     *    $options = array(
     *          'select' => 'id,name,title', // 返回字段（ * 为全部字段 慎用）
     *          'condition' => array('id'=>'1',....), // where 下的条件
     *          'one' => true, // 只查询一条
     *          'offset' => 1, // 结果期偏移起始位置
     *          'size' => 5,   // 查询偏移量后的几条数据
     *          'order' => 'id desc,time asc', // 排序
     *          'cache' => 0 // 缓存，目前没用
     *    );
     *    </code>
     * @return array $data
     * @access public
     */
    static public function Select($table, $options = array(), $bind = array()) {
        $condition = isset($options['condition']) ? $options['condition'] : null;
        $one = isset($options['one']) ? $options['one'] : false;
        $offset = isset($options['offset']) ? abs(intval($options['offset'])) : 0;
        if ($one) {
            $size = 1;
        } else {
            $size = isset($options['size']) ? abs(intval($options['size'])) : null;
        }
        $select = isset($options['select']) ? $options['select'] : '*';
        $select = is_array($select) ? implode(',', $select) : $select;
        $order = isset($options['order']) ? $options['order'] : null;
        $cache = isset($options['cache']) ? abs(intval($options['cache'])) : 0;

        $condition = self::BuildCondition($condition);
        $condition = (null == $condition) ? null : "WHERE $condition";

        $limitation = $size ? "LIMIT $offset,$size" : null;

        $table = strpos($table, '`') === FALSE ? "`$table`" : $table;

        $sql = "SELECT {$select} FROM $table $condition $order $limitation";
        return self::GetQueryResult($sql, $one, $cache, $bind);
    }

    /**
     * 取查询记录并以主键值为返回数组键名
     * @param string $table 表名
     * @param array $ids 查询条件（主键值）
     * @param string|array $fields 查询字段
     * <code>
     *      $fields = '`id`,`name`'; // 字符串形式
     *      $fields = array('`id`','`name`','sum(`num`) as total'); // 数组形式
     * </code>
     * @return array $data
     * @access public
     */
    static public function Fetch($table, $ids = array(), $fields = '*', $cache = 0) {
        $one = is_array($ids) ? false : true;
        settype($ids, 'array');
        $idstring = implode('\',\'', $ids);
        $fields = is_array($fields) ? implode(',', $fields) : $fields;
        $r = self::Select($table, array('select' => $fields, 'condition' => "id IN ('{$idstring}')", 'cache' => $cache, 'one' => $one));
        if ($one)
            return $r;
        return Utility::AssColumn($r, 'id');
    }

    static public function GetQueryResult($sql, $one = true, $cache = 0, $bind = array(), $fetch_type = PDO::FETCH_ASSOC) {

        $mkey = RedisCache::GetStringKey($sql);


        if ($cache > 0) {
            $ret = RedisCache::Get($mkey);

            if ($ret)
                return $ret;
        }

        $ret = array();


        if ($result = self::Query($sql, $bind)) {
            while ($row = $result->fetch($fetch_type)) {

                $row = array_change_key_case($row, CASE_LOWER);

                if ($one) {
                    $ret = $row;
                    break;
                } else {
                    array_push($ret, $row);
                }
            }

            //  @mysql_free_result($result);
        }

        if ($ret && $cache > 0)
            RedisCache::Set($mkey, $ret, 0, $cache);
        return $ret;
    }

    static public function SaveTableRow($table, $condition) {
        return self::Insert($table, $condition);
    }

    static public function Insert($table, $condition, $bind = array()) {
        self::Instance();
        $table = strpos($table, '`') === FALSE ? "`$table`" : $table;
        $sql = "INSERT INTO $table SET ";
        $content = null;

        foreach ($condition as $k => $v) {
            $v_str = null;
            if (is_numeric($v))
                $v_str = "'{$v}'";
            else if (is_null($v))
                $v_str = 'NULL';
            else
                $v_str = "'" . self::EscapeString($v) . "'";

            $content .= "`$k`=$v_str,";
        }



        $content = trim($content, ',');
        $sql .= $content;

        self::execute($sql, $bind);
        $insert_id = self::$mConnection->lastInsertId();
        return $insert_id;
    }

    static public function GetInsertId() {
        self::Instance();

        $insert_id = self::$mConnection->lastInsertId();
        return intval($insert_id);
    }

    static public function DelTableRow($table = null, $condition = array()) {
        return self::Delete($table, $condition);
    }

    static public function Delete($table = null, $condition = array(), $bind = array()) {
        if (null == $table || empty($condition))
            return false;
        self::Instance();

        $condition = self::BuildCondition($condition);
        $condition = (null == $condition) ? null : "WHERE $condition";
        $table = strpos($table, '`') === FALSE ? "`$table`" : $table;
        $sql = "DELETE FROM $table $condition";
        return self::execute($sql, $bind);
    }

    static public function Update($table = null, $id = 1, $updaterow = array(), $pkname = 'id', $bind = array()) {

        if (null == $table || empty($updaterow) || null == $id)
            return false;

        if (is_array($id))
            $condition = self::BuildCondition($id);
        else
            $condition = "`$pkname`='" . self::EscapeString($id) . "'";

        self::Instance();
        $table = strpos($table, '`') === FALSE ? "`$table`" : $table;
        $sql = "UPDATE $table SET ";
        $content = null;

        foreach ($updaterow as $k => $v) {
            $v_str = null;
            if (is_numeric($v))
                $v_str = "'{$v}'";
            else if (is_null($v))
                $v_str = 'NULL';
            else if (is_array($v))
                $v_str = $v[0]; //for plus/sub/multi; 
            else
                $v_str = "'" . self::EscapeString($v) . "'";

            $content .= "`$k`=$v_str,";
        }

        $content = trim($content, ',');
        $sql .= $content;
        $sql .= " WHERE $condition";
        $result = self::execute($sql, $bind);

        if (false == $result) {
            self::Close();
            return false;
        }

        return true;
    }

    static public function GetField($table, $select_map = array()) {
        $fields = array();
        $q = self::Query("DESC `$table`");

        while ($r = mysql_fetch_assoc($q)) {
            $Field = $r['Field'];
            $Type = $r['Type'];

            $type = 'varchar';
            $cate = 'other';
            $extra = null;

            if (preg_match('/^id$/i', $Field))
                $cate = 'id';
            else if (preg_match('/^_time/i', $Field))
                $cate = 'integer';
            else if (preg_match('/^_number/i', $Field))
                $cate = 'integer';
            else if (preg_match('/_id$/i', $Field))
                $cate = 'fkey';


            if (preg_match('/text/i', $Type)) {
                $type = 'text';
                $cate = 'text';
            }
            if (preg_match('/date/i', $Type)) {
                $type = 'date';
                $cate = 'time';
            } else if (preg_match('/int/i', $Type)) {
                $type = 'int';
            } else if (preg_match('/(enum|set)\((.+)\)/i', $Type, $matches)) {
                $type = strtolower($matches[1]);
                eval("\$extra=array($matches[2]);");
                $extra = array_combine($extra, $extra);

                foreach ($extra AS $k => $v) {
                    $extra[$k] = isset($select_map[$k]) ? $select_map[$k] : $v;
                }
                $cate = 'select';
            }

            $fields[] = array(
                'name' => $Field,
                'type' => $type,
                'extra' => $extra,
                'cate' => $cate,
            );
        }
        return $fields;
    }

    static public function Exist($table, $condition = array(), $bind = array()) {
        $row = self::LimitQuery($table, array(
                    'condition' => $condition,
                    'one' => true,
        ));

        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
    }

    /**
     * value分析
     * @access protected
     * @param mixed $value
     * @return string
     */
    static protected function parseValue($value) {
        if (is_string($value)) {
            $value = '\'' . self::EscapeString($value) . '\'';
        } elseif (isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp') {
            $value = self::EscapeString($value[1]);
        } elseif (is_array($value)) {
            $value = array_map(self::parseValue, $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_null($value)) {
            $value = 'null';
        }
        return $value;
    }

    static public function BuildCondition($condition = array(), $logic = 'AND') {
        if (is_string($condition) || is_null($condition))
            return $condition;

        $logic = strtoupper($logic);
        $content = null;
        foreach ($condition as $k => $v) {
            $v_str = null;
            $v_connect = '=';
            if ('BETWEEN' == strtoupper($v[0])) {
                $data = is_string($v[1]) ? explode(',', $v[1]) : $v[1];
                $content .= $logic . ' (' . $k . ' BETWEEN  ' . self::EscapeString($data[0]) . '  AND ' . self::EscapeString($data[1]) . ' )';
                continue;
            }



            if (is_numeric($k)) {
                $content .= $logic . ' (' . self::BuildCondition($v, $logic) . ')';
                continue;
            }


            $k = preg_replace('/[\#\;\=\s]+/', '', $k);
            $maybe_logic = strtoupper($k);
            if (in_array($maybe_logic, array('AND', 'OR'))) {
                $content .= $logic . ' (' . self::BuildCondition($v, $maybe_logic) . ')';
                continue;
            }


            if (is_numeric($v)) {
                $v_str = "'{$v}'";
            } else if (is_null($v)) {
                $v_connect = ' IS ';
                $v_str = ' NULL';
            } else if (is_array($v)) {
                if (isset($v[0])) {
                    $v_str = null;
                    foreach ($v AS $one) {
                        if (is_numeric($one)) {
                            $v_str .= ',' . $one;
                        } else {
                            $v_str .= ',\'' . self::EscapeString($one) . '\'';
                        }
                    }
                    $v_str = '(' . trim($v_str, ',') . ')';
                    $v_connect = 'IN';
                } else if (empty($v)) {
                    $v_str = $k;
                    $v_connect = '<>';
                } else {
                    $v_connect = array_shift(array_keys($v));
                    $v_connect = preg_replace('/[\#\;\=\s]+/', '', $v_connect);
                    $v_s = array_shift(array_values($v));
                    $v_str = "'" . self::EscapeString($v_s) . "'";
                    $v_str = is_numeric($v_s) ? "'{$v_s}'" : $v_str;
                }
            } else {



                $v_str = "'" . self::EscapeString($v) . "'";
            }

            $k = strpos($k, '`') === FALSE ? "`$k`" : $k;
            $content .= " $logic $k $v_connect $v_str ";
        }

        $content = preg_replace('/^\s*' . $logic . '\s*/', '', $content);
        $content = preg_replace('/\s*' . $logic . '\s*$/', '', $content);
        $content = trim($content);

        return $content;
    }

    static public function CheckInt($id) {
        $id = intval($id);

        if (0 >= $id)
            throw new Exception('must int!');

        return $id;
    }

    static public function debugstr() {
        $totalProcessTime = 0;
        $totalSQL = 0;

        $Str = "
        <table id=debugtable width=100% border=0 cellspacing=1 style='background:#828284;word-break: break-all'>";

        $Str .= "<tr style='background:Darkred;height:30;Color:White'>
                    <th width=80>Type</th>
                    <th>Query</th>
                    <th width=100>Result</th>
                    <th width=50>Error</th>
                    <th width=100>ProcessTime</th>
                 </tr>\n";

        for ($i = 0, $cnt = Count(self::$debugMsg); $i < $cnt; $i++) {
            $Str .= "<tr style='background:#EEEEEE;Height:25;Text-Align:center'>
                        <td>" . self::$debugMsg[$i]["Type"] . "</td>
                        <td align=left>" . HtmlSpecialChars(self::$debugMsg[$i]["Sentence"]) . "</td>
                        <td>" . self::$debugMsg[$i]["Result"] . "</td>
                        <td>" . self::$debugMsg[$i]["Error"] . "</td>
                        <td>" . sprintf("%.4f", self::$debugMsg[$i]["ProcessTime"]) . "</td>
                     </tr>\n";
            $totalProcessTime += (double) self::$debugMsg[$i]["ProcessTime"];
            $totalSQL++;
        }

        $Str .= "<tr style='background:#EEEEEE;Height:30;text-align:center'>
                    <td colspan=5>
                        Total execute queries: " . $totalSQL
                . "&nbsp;Total ProcessTime:"
                . sprintf('%.4f', $totalProcessTime)
                . "</td>
                 </tr>\n";

        $Str .= "</table>";
        //$Str .= '<pre>';
        //$Str .= print_r($_GET['_db']);
        //$Str .= '</pre>';
        return $Str;
    }

    static public function debugsql() {
        $Str = '';

        for ($i = 0; $i < Count(self::$debugMsg); $i++) {
            $Str .= self::$debugMsg[$i]['Sentence'] . "\n";
        }

        return $Str;
    }

}

?>
