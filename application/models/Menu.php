<?php

/**
 * 菜单model
 */
class MenuModel extends ZysModel {

    private $g_table = 'menu';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 获取菜单
     * @param $where string 条件
     * @return array( id name url lang pare )
     */
    public function MenuList($where = NULL) {
        $sql = 'SELECT';
        $sql .= ' `menu_id` AS id';
        $sql .= ', `menu_name` AS name';
        $sql .= ', `menu_url` AS url';
        $sql .= ', `menu_lang` AS lang';
        $sql .= ', `menu_parent_id` AS pare';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' WHERE `menu_status`=0';
        if ($where) {
            $sql .= ' AND ' . $where;
        }
        $sql .= ' ORDER BY `updated_at` DESC';
        echo $sql;
        return $this->query($sql);
    }

    /**
     * 创建菜单
     * @param string $menu_name 菜单名
     * @param string $menu_url 菜单url地址
     * @param string $menu_lang 菜单所属语言
     * @param int $menu_parent_id 父级id
     * @param string $u_name 用户名
     * @return bool true/false
     */
    public function MenuCreate($menu_name, $menu_url, $menu_lang = null, $menu_parent_id, $u_name) {
        if (!$menu_name) {
            return '-011'; // 菜单名不能为空
        }
        $exist = $this->MenuExist($menu_name, $menu_parent_id);
        if ($exist) {
            return '-012'; // 菜单名已经存在了
        }
        $sql = 'INSERT INTO ' . $this->g_table;
        $sql .= ' ( `menu_name`,`menu_url`,`menu_lang`,`menu_status`,`menu_parent_id`,`updated_by`,`updated_at`)';
        $sql .= ' VALUES ( "' . $menu_name . '","' . $menu_url . '","' . $menu_lang . '",0,' . $menu_parent_id . ',"' . $u_name . '","' . date('Y-m-d H:i:s', time()) . '")';
        return $this->execute($sql);
    }

    /**
     * 判断菜单是否存在
     * @param string $menu_name
     * @param string $menu_parent_id
     * @return bool 1/0
     */
    public function MenuExist($menu_name, $menu_parent_id, $menu_id = 0) {
        $sql = 'SELECT * FROM ' . $this->g_table;
        $sql .= ' WHERE `menu_status` = 0 AND `menu_parent_id` = ' . $menu_parent_id . ' AND `menu_name` = "' . $menu_name . '"';
        if ($menu_id > 0) {
            $sql .= " and menu_id !=" . $menu_id;
        }
        return $this->execute($sql);
    }

    /**
     * 获取一条数据
     * @param int $id 菜单id
     * @return array(id name url lang pare)
     * @author Wen
     */
    public function MenuSelOne($id) {
        $sql = 'SELECT';
        $sql .= ' `menu_id` AS id';
        $sql .= ', `menu_name` AS name';
        $sql .= ', `menu_url` AS url';
        $sql .= ', `menu_lang` AS lang';
        $sql .= ', `menu_parent_id` AS pare';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' WHERE `menu_status`=0 AND `menu_id` = ' . $id;
        $res = $this->query($sql);
        return $res['0'];
    }

    /**
     * 修改一条数据
     * @param int $menu_id 菜单id
     * @param string $menu_name 菜单名
     * @param string $menu_url 菜单url地址
     * @param string $menu_lang 菜单所属语言
     * @param int $menu_parent_id 父级菜单id
     * @param string $u_name 用户名
     * @return bool true/false
     * @author Wen
     */
    public function MenuUpdOne($menu_id, $menu_name, $menu_url, $menu_lang = null, $menu_parent_id, $u_name) {
        if (!$menu_name) {
            return '-011'; // 菜单名不能为空
        }
        $exist = $this->MenuExist($menu_name, $menu_parent_id, $menu_id);
        if ($exist) {
            return '-012'; // 菜单名已经存在了
        }
        $sql = 'UPDATE ' . $this->g_table . ' SET';
        $sql .= ' `menu_name` = "' . $menu_name . '"';
        $sql .= ', `menu_url` = "' . $menu_url . '"';
        $sql .= ', `menu_lang` = "' . $menu_lang . '"';
        $sql .= ', `menu_parent_id` = ' . $menu_parent_id;
        $sql .= ', `updated_by` = "' . $u_name . '"';
        $sql .= ', `updated_at` = "' . date('Y-m-d H:i:s', time()) . '"';
        $sql .= ' WHERE `menu_status` = 0 AND `menu_id` = ' . $menu_id;
        return $this->execute($sql);
    }

    /**
     * 删除一条数据 逻辑删除
     * @param int $id 菜单id
     * @param string $u_name 用户名
     * @return true/false
     * @author Wen
     */
    public function MenuDelOne($id, $u_name) {
        $sql_a = 'SELECT `menu_parent_id` AS pare FROM ' . $this->g_table;
        $sql_a .= ' WHERE `menu_status`= 0 AND `menu_id`=' . $id;
        $data_a = $this->query($sql_a);
        $where = NULL;
        if (0 == $data_a['0']['pare']) {
            $where .= ' AND ( `menu_parent_id` =' . $id . ' OR `menu_id` =' . $id . ')';
        } else {
            $where .= ' AND `menu_id`=' . $id;
        }
        $sql = 'UPDATE ' . $this->g_table . ' SET';
        $sql .= ' `menu_status`=1';
        $sql .= ', `updated_by`= "' . $u_name . '"';
        $sql .= ', `updated_at` = "' . date('Y-m-d H:i:s', time()) . '"';
        $sql .= ' WHERE `menu_status`= 0';
        $sql .= $where;
        if ($where) {
            return $this->execute($sql);
        } else {
            return FALSE;
        }
    }

    /**
     * 批量删除数据 逻辑删除
     * @param string $ids 菜单id 的字符串
     * @param string $u_name
     * @return true/false
     * @author Wen
     */
    public function MenuDel($ids, $u_name) {
        $sql_a = 'SELECT `menu_id` AS id,`menu_parent_id` AS pare FROM ' . $this->g_table;
        $sql_a .= ' WHERE `menu_status`= 0 AND `menu_id` in(' . $ids . ')';
        $data_a = $this->query($sql_a);
        $d_p = '';
        $d_o = '';
        foreach ($data_a as $ka => $kv) {
            if (0 == $kv['pare']) {
                $d_p .= ',' . $kv['id'];
            } else {
                $d_o .= ',' . $kv['id'];
            }
        }
        // 操作顶级
        if (strlen($d_p) >= 2) {
            $d_p = substr($d_p, 1);
            $where_d = ' `menu_parent_id` in(' . $d_p . ') OR `menu_id` in(' . $d_p . ')';
        }
        // 操作子级
        if (strlen($d_o) >= 2) {
            $d_o = substr($d_o, 1);
            $where_o = ' `menu_id` in(' . $d_o . ')';
        }
        // 确定where
        $where = 'AND';
        if ($where_d) {
            $where .= '( ';
            if ($where_o) {
                $where .= $where_d;
                $where .= ' OR ' . $where_o;
            } else {
                $where .= $where_d;
            }
            $where .= ' )';
        } else {
            if ($where_o) {
                $where .= $where_o;
            }
        }
        // 确定最终的sql
        $sql = 'UPDATE ' . $this->g_table . ' SET';
        $sql .= ' `menu_status`=1';
        $sql .= ', `updated_by`= "' . $u_name . '"';
        $sql .= ', `updated_at` = "' . date('Y-m-d H:i:s', time()) . '"';
        $sql .= ' WHERE `menu_status`=0 ';
        $sql .= $where;
        if ($where != 'AND') {
            return $this->execute($sql);
        } else {
            return FALSE;
        }
    }

}
