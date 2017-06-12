<?php
/*
	附件文档Model
*/
class AttachmentModel extends ZysModel {
	private $g_table = 'attachment';

	public function __construct() {
		parent::__construct($this->g_table);
	}

    /**
     * 枚举值
     * ATTACHMENT_STATE  ENABLED:0,DISABLED:1
     * ATTACHMENT_TYPE PRIVATE:0,PUBLIC:1 (一期默认是 0)
     */

	/*
		创建附件文档
		@params array $data
		@return mixed
	*/
	public function AttCreate($data){
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			return false;
		}
	}

    /**
     * 获取总条数
     * @param $where string
     * @return int
     */
    public function AttCount( $where = null ){
        $sql = 'SELECT COUNT(*) as num FROM '.$this->g_table;
        $sql .= ' WHERE `attachment_state` = 0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $res = $this->query( $sql );
        return $res[0]['num'];
    }

    /**
     * 获取文档
     * @param int $start 开始位置
     * @param int $limit 每页显示条数
     * @param string $where 条件
     * @return array (id:文档id, a_type:类型(0/1), o_name:原名, m_type:格式, url_r:相对地址, url_s:域名, a_size:大小, a_desc:描述)
     * @author Wen
     */
    public function AttList( $start = 0 , $limit = 10, $where = null ){
        $sql = 'SELECT `attachment_id` AS id';
        // $sql .= ', `attachment_type` AS a_type';
        $sql .= ', `original_filename` AS o_name';
        $sql .= ', `mime_type` AS m_type';
        $sql .= ', `uri_relativel` AS url_r';
        $sql .= ', `uri_site` AS url_s';
        $sql .= ', `size` AS a_size';
        $sql .= ', `meta_info` AS a_desc';
		$sql .= ', `owner_type` AS o_type';
		$sql .= ', `owner_pk` AS o_id';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `attachment_state`=0';
        if ( $where ){
            $sql .= ' AND '.$where;
        }
        $sql .= ' ORDER BY `updated_at` DESC';
        if ( $limit ){
            $sql .= ' LIMIT '.$start.','.$limit;
        }
        return $this->query( $sql );
    }

    /**
     * 文档详细数据
     * @param int $id 文档id
     * @return array (id:文档id, a_type:类型(0/1), o_name:原名, m_type:格式, url_r:相对地址, url_s:域名, a_size:大小, a_desc:描述)
     * @author Wen
     */
    public function AttListOne( $id ){
        $sql = 'SELECT `attachment_id` AS id';
        // $sql .= ', `attachment_type` AS a_type';
        $sql .= ', `original_filename` AS o_name';
        $sql .= ', `mime_type` AS m_type';
        $sql .= ', `uri_relativel` AS url_r';
        $sql .= ', `uri_site` AS url_s';
        $sql .= ', `size` AS a_size';
        $sql .= ', `meta_info` AS a_desc';
        $sql .= ' FROM '.$this->g_table;
        $sql .= ' WHERE `attachment_state`=0';
        $sql .= ' AND `attachment_id` = '.$id;
        $res = $this->query( $sql );
        return $res['0'];
    }

    /**
     * 修改文档
     * @param int $attachment_id 文件id
     * @param int $attachment_type 文件类型（0/1）
     * @param string $original_filename 原始文件名
     * @param string $mime_type 文件类型
     * @param string $uri_relativel URI相对地址
     * @param string $uri_site URI地址域名部分
     * @param int $size 文件大小
     * @param string $meta_info 对象描述，如图片的分辨率，压缩比例，dpi等
     * @param string $u_name 用户名
     * @return bool(true/false)
     * @author Wen
     */
    public function AttUpd( $attachment_id,$attachment_type,$original_filename,$mime_type,$uri_relativel,$uri_site,$size,$meta_info,$u_name){
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `attachment_type` = 0';
        $sql .= ', `original_filename` = "'.$original_filename.'"';
        $sql .= ', `mime_type` = "'.$mime_type.'"';
        $sql .= ', `uri_relativel` = '.$uri_relativel;
        $sql .= ', `uri_site` = "'.$uri_site.'"';
        $sql .= ', `size` = '.$size;
        $sql .= ', `meta_info` = "'.$meta_info.'"';
        $sql .= ', `updated_by` = "'.$u_name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `attachment_state` = 0';
        $sql .= ' AND `attachment_id` = '.$attachment_id;
        return $this->execute( $sql );

    }

    /**
     * 逻辑删除 - 修改一条数据
     * @param int $id 文档id
     * @param string $name
     * @return bool(true/false)
     * @author Wen
     */
    public function AttDelOne( $id, $name ) {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `attachment_state`=1';
        $sql .= ', `updated_by`="'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `attachment_state` = 0';
        $sql .= ' AND `attachment_id`='.$id;
        return $this->execute( $sql );
    }

    /**
     * 逻辑删除 - 批量修改数据
     * @param string $ids 文档id集合
     * @param string $name
     * @return bool(true/false)
     * @author Wen
     */
    public function AttDel( $ids, $name ) {
        $sql = 'UPDATE '.$this->g_table.' SET';
        $sql .= ' `attachment_state`=1';
        $sql .= ', `updated_by`="'.$name.'"';
        $sql .= ', `updated_at` = "'.date( 'Y-m-d H:i:s', time() ).'"';
        $sql .= ' WHERE `attachment_state` = 0';
        $sql .= ' AND `attachment_id` in('.$ids.')';
        return $this->execute( $sql );
    }

    /**
     * 修改
     * @param $where 条件
     * @param $data 数据
     * @return bool
     * @author Wen
     */

    public function UpdOne( $where, $data )
    {
        return $this->where( $where )->save( $data );
    }

    /**
     * 批量添加
     * @param $data
     * @return bool
     * @author Wen
     */

    public function CreateMore( $data )
    {
        // 处理数据获取 要添加数据的字段名
        $sql_key = NULL;
        $data_key = array_keys( $data[0] );
        foreach ( $data_key as $k_k => $k_v ){
            $sql_key .= '`'.$k_v.'`,';
        }
        $sql_key = substr( $sql_key,0,-1);
        // 处理数据 获取要添加的数据
        $sql_value = NULL;
        foreach ( $data as $v_k => $v_v ){
            $sql_value .= '(';
            $data_v_data = array_values( $v_v );
            foreach ( $data_v_data as $v_d_k => $v_d_v ){
                $sql_value .= "'".$v_d_v."',";
            }
            $sql_value = substr( $sql_value,0,-1 );
            $sql_value .= '),';
        }
        $sql_value = substr( $sql_value,0,-1 );
        // 组装sql
        $sql  = 'INSERT INTO '.$this->g_table;
        $sql .= ' ('.$sql_key.')';
        $sql .= ' VALUES '.$sql_value;
        $res = $this->execute( $sql );
        return $res;
    }

}
