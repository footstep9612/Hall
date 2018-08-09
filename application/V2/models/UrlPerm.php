<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class UrlPermModel extends PublicModel {

    //put your code here
    protected $tableName = 'func_perm';
    Protected $autoCheckFields = true;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'sort') {
        if (!empty($limit)) {
            //,'false' as check
            return $this->field("id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,remarks,sort,parent_id,grant_flag,created_by,created_at,source")
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            //,'false' as `check`
            return $this->field("id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,url,remarks,sort,parent_id,grant_flag,created_by,created_at,source")
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取详情
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,fn,fn_en,fn_es,fn_ru,fn_group,show_name,show_name_en,show_name_es,show_name_ru,logo_name,logo_url,url,sort,remarks,parent_id,grant_flag,created_by,created_at')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 获取详情
     * @param  int  $url
     * @return array
     * @author jhw
     */
    public function getfnByUrl($url = '') {
        $where['url'] = $url;
        if (!empty($where['url'])) {
            $row = $this->where($where)
                    ->field('id,fn,fn_en,fn_es,fn_ru')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $flag = $this->where($where)
                    ->delete();
            if (!$flag) {
                $this->_delCache();
            }
            return $flag;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        $arr = $this->create($data);
        if (!empty($data['parent_id']) && $data['parent_id'] != $data['id']) {
            $arr['top_parent_id'] = $this->getOneLevelMenuId($data['parent_id']);
        } elseif (!empty($data['parent_id']) && $data['parent_id'] == $data['id']) {
            $arr['top_parent_id'] = $data['id'];
        } elseif (!empty($data['id'])) {
            $arr['top_parent_id'] = $this->getOneLevelMenuId($data['id']);
        }
        if (!empty($where)) {

            $flag = $this->where($where)->save($arr);
            if (!$flag) {
                $this->_delCache();
            }
            return $flag;
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        $data = $this->create($create);
        if (!empty($data['parent_id'])) {
            $data['top_parent_id'] = $this->getOneLevelMenuId($data['parent_id']);
        }

        $data['created_at'] = date("Y-m-d H:i:s");
        $this->startTrans();
        $insertId = $this->add($data);
        if ($insertId && empty($data['parent_id'])) {
            $flag = $this->where(['id' => $insertId])->save(['top_parent_id' => $insertId]);
            if (!$flag) {
                $this->rollback();
                return false;
            }
        } elseif (!$insertId) {
            $this->rollback();
            return false;
        }

        $this->_delCache();
        $this->commit();
        return $insertId;
    }

    /**
     * @desc 获取指定菜单的一级父类菜单ID
     *
     * @param int $menuId 菜单ID
     * @return mixed
     * @author liujf
     * @time 2018-06-25
     */
    public function getOneLevelMenuId($menuId) {
        $parentId = $this->where(['id' => $menuId])->getField('parent_id');
        if ($parentId > 0) {
            return $this->getOneLevelMenuId($parentId);
        } else {
            return $menuId;
        }
    }

    /**
     * @desc 根据菜单名称获取菜单ID
     *
     * @param string $name 菜单名称
     * @return int
     * @author liujf
     * @time 2018-06-25
     */
    public function getMenuIdByName($name) {
        return $this->where(['fn' => $name, 'parent_id' => '0'])->getField('id') ?: 0;
    }

    public function getDefault() {
        if (redisExist('HOME_DEFAULT')) {

            return json_decode(redisGet('HOME_DEFAULT'), true);
        } else {
            $parent_id = redisExist('HOME_ID') ? redisGet('HOME_ID') : null;
            if (!$parent_id) {
                $parent_id = $this->getMenuIdByName('首页');
                redisSet('HOME_ID', $parent_id);
            }
            $data = $this->getlist(['parent_id' => $parent_id], null);
            $res = $this->getUrlpermChildren($data);

            redisSet('HOME_DEFAULT', json_encode($res));
            return $res;
        }
    }

    public function getHome() {
        if (redisExist('HOME')) {

            return json_decode(redisGet('HOME'), true);
        } else {
            $parent_id = redisExist('HOME_ID') ? redisGet('HOME_ID') : null;
            if (!$parent_id) {
                $parent_id = $this->getMenuIdByName('首页');
                redisSet('HOME_ID', $parent_id);
            }
            $data = $this->getMenu(['id' => $parent_id]);

            redisSet('HOME', json_encode($data));
            return $data;
        }
    }

    public function getMenu($data, $order = 'sort') {
        return $this->field('`id` as func_perm_id,`fn`,`parent_id`,`url`,top_parent_id,source,`fn_en`,`fn_es`,`fn_ru`')
                        ->where($data)
                        ->order($order)
                        ->select();
    }

    private function _delCache() {
        redisDel('HOME_DEFAULT');
        redisDel('HOME_ID');
        redisDel('HOME');
    }

    function getUrlpermChildren($list) {

        if (!empty($list)) {
            $parent_ids = [];
            foreach ($list as $item) {
                $parent_ids[] = $item['id'];
            }

            $data = $this->getMenu(['parent_id' => ['in', $parent_ids]]);
            $ret = [];
            if (!empty($data)) {
                $children_parent_ids = [];
                foreach ($data as $child) {
                    $children_parent_ids = $child['id'];
                }

                if (!empty($children_parent_ids)) {
                    $ret_children = [];
                    $childrens = $this->getMenu(['parent_id' => ['in', $children_parent_ids]]);
                    foreach ($childrens as $children) {
                        $ret_children[$children['parent_id']][] = $children;
                    }
                    foreach ($data as $key => $item) {
                        if (!empty($ret_children[$item['id']])) {
                            $data[$key]['children'] = $ret_children[$item['id']];
                        }
                    }
                }
                foreach ($data as $child) {
                    $ret[$child['parent_id']][] = $child;
                }
                foreach ($list as $key => $item) {
                    if (!empty($ret[$item['id']])) {
                        $list[$key]['children'] = $ret[$item['id']];
                    }
                }
            }
            return $list;
        } else {
            return $list;
        }
    }

}
