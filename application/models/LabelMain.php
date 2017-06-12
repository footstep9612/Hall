<?php
/**
 * 标签model
 */
class LabelMainModel extends ZysModel {

    private $g_table = 'label_main';

    public function __construct()
    {
        parent::__construct($this->g_table);
    }

    /**
     * 获取标签分类
     * @return array( cate )
     */

    public function LabCate(){
        $sql = 'SELECT DISTINCT label_main.label_category AS cate FROM '.$this->g_table;
		$sql .= ' WHERE `label_state` = 0 ';
        $res = $this->query( $sql );
        return $res;
    }
	//获取分类下的标签
	public function Catelablist($category){
		$sql = 'SELECT DISTINCT label_main.label_name, label_main_id FROM '.$this->g_table;
		$sql .= ' WHERE `label_state` = 0 and label_category="'.$category.'"';
        $res = $this->query( $sql );
        return $res;
	}
    /**
     * 获取总条数
     * @param $where string
     * @return int
     */

    public function LabCount( $where = null ){
        $sql = 'SELECT COUNT(*) as num FROM '.$this->g_table;
        $sql .= ' WHERE `label_state` = 0 ';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 创建标签
     * @param string $label_name 标签名
     * @param string $label_category 分类
     * @param string $label_lang 语言
     * @param string $recommand_level 排序
     * @param string $label_desc 描述
     * @param string $name 用户名
     * @return true/false
     */

    public function LabCreate( $label_name, $label_category, $label_lang = null,$recommand_level, $label_desc, $name ){
        if ( !$label_name ){
            return '-011'; // 标签名不能为空
        }
        $exist = $this->LabExist( $label_name,$label_category );
        if ( $exist ){
            return '-012'; // 标签名已经存在了
        }
        $sql = 'INSERT INTO '.$this->g_table;
        $sql .= ' ( `label_name`,`label_category`,`recommand_level`,`label_desc`,`label_lang`,`label_state`,`updated_by`,`updated_at`)';
        $sql .= ' VALUES("'.$label_name.'","'.$label_category.'","'.$recommand_level.'","'.$label_desc.'","'.$label_lang.'",0,"'.$name.'","'.date( 'Y-m-d H:i:s', time() ).'" )';
        return $this->execute( $sql );
   }

    /**
     * 判断标签是否存在
     * @param string $label_name;
     * @param string $label_category
     * @return bool true/false
     * @author Wen
     */

    public function LabExist( $label_name,$label_category, $labelid = 0)
    {
        $sql = 'SELECT * FROM '.$this->g_table;
        $sql .= ' WHERE `label_state` = 0 AND `label_name` = "'.$label_name.'"';
        if ( $label_category ){
            $sql .= ' AND `label_category` ="'. $label_category .'"';
        }
		if($labelid > 0){
			$sql .=" and label_main_id !=".$labelid;
		}
        return $this->execute( $sql );
    }

    /**
     * 获取标签
     * @param int $start 开始位置
     * @param int $limit 每页显示条数
     * @param string $where 条件
     * @return array
     * @author Wen
     */

    public function LabList( $start, $limit = 10, $where = null ){
        $sql = 'SELECT `label_main_id` AS id';
        $sql .= ', `label_name` AS name';
        $sql .= ', `label_category` AS category';
        $sql .= ', `recommand_level` AS level';
        $sql .= ', `label_desc` AS l_desc';
		$sql .= ', updated_by';
		$sql .= ', updated_at';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `label_state`=0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $sql .= ' ORDER BY `recommand_level` ASC,`updated_at` DESC';
        $sql .= ' LIMIT '.$start.','.$limit;
        return $this->query( $sql );
    }

    /**
     * 获取一条数据
     * @param int $id 标签id
     * @return array
     * @author Wen
     */

    public function LabSelOne( $id ){
        $sql = 'SELECT `label_main_id` AS id';
        $sql .= ', `label_name` AS name';
        $sql .= ', `label_category` AS category';
        $sql .= ', `recommand_level` AS level';
        $sql .= ', `label_lang` AS lang';
        $sql .= ', `label_desc` AS l_desc';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `label_state`=0';
        $sql .= ' AND `label_main_id` = '.$id;
        $res = $this->query( $sql );
        return $res['0'];
    }

    /**
     * 修改一条数据
     * @param int $label_main_id 标签id
     * @param string $label_name 标签名
     * @param string $label_category 分类
     * @param string $label_lang 语言
     * @param int $label_level 顺序
     * @param string $label_desc 描述
     * @param string $name 用户名
     * @return true/false
     * @author Wen
     */


    public function LabUpdOne( $label_main_id, $label_name, $label_category, $label_lang, $label_level, $label_desc, $name )
    {
        if ( !$label_name ){
            return '-011'; // 标签名不能为空
        }
        $exist = $this->LabExist( $label_name,$label_category, $label_main_id );

        if ( $exist ){
            return '-012'; // 标签名已经存在了
        }
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `label_name` = "'.$label_name.'"';
        $sql .= ', `label_category` = "'.$label_category.'"';
        $sql .= ', `label_desc`="'.$label_desc.'"';
        $sql .= ', `recommand_level`="'.$label_level.'"';
        $sql .= ', `label_lang`="'.$label_lang.'"';
        $sql .= ', `updated_by` = "'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `label_state` = 0';
        $sql .= ' AND `label_main_id` = '.$label_main_id;
        return $this->execute( $sql );
    }

    /**
     * 删除一条数据 逻辑删除
     * @param int $id 标签id
     * @param string $name
     * @return true/false
     * @author Wen
     */

    public function LabDelOne( $id, $name ) {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `label_state`=1';
        $sql .= ', `updated_by`="'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `label_main_id`='.$id;
        return $this->execute( $sql );
    }

    /**
     * 批量删除数据 逻辑删除
     * @param string $ids 标签id 的字符串
     * @param string $name
     * @return true/false
     * @author Wen
     */

    public function LabDel( $ids, $name )
    {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `label_state`=1';
        $sql .= ', `updated_by`="'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `label_main_id` in('.$ids.')';
        return $this->execute( $sql );
    }




}
