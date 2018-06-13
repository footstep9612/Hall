<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:47
 */
class SpecialPositionDataModel extends PublicModel {
    protected $tableName = 'special_position_data';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取推荐位商品
     * @param $condition
     * @return array|bool
     */
    public function getList($condition){
        $gModel = new GoodsModel();
        $gtable = $gModel->getTableName();
        $thistable = $this->getTableName();
        $pModel = new ProductAttachModel();
        $patable = $pModel->getTableName();
        $where =["$thistable.deleted_at"=>['exp', 'is null']];
        if(isset($condition['special_id'])){
            $where["$thistable.special_id"] = intval($condition['special_id']);
        }else{
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(isset($condition['position_id'])){
            $where["$thistable.position_id"] = intval($condition['position_id']);
        }else{
            jsonReturn('', MSG::MSG_FAILED,'请选择推荐位');
        }
        if(isset($condition['created_at_start']) && isset($condition['created_at_end'])){
            $where["$thistable.created_at"] = ['between', trim($condition['created_at_start']).','.trim($condition['created_at_end'])];
        }elseif(isset($condition['created_at_start'])){
            $where["$thistable.created_at"] = ['egt', trim($condition['created_at_start'])];
        }elseif(isset($condition['created_at_end'])){
            $where["$thistable.created_at"] = ['elt', trim($condition['created_at_end'])];
        }

        try{
            $data = [];
            list($from, $size) = $this->_getPage($condition);
            $rel = $this->field("$thistable.id,$thistable.lang,$thistable.special_id,$thistable.position_id,$thistable.sku,$thistable.sort_order,$thistable.created_at,$thistable.created_by,$thistable.spu,$gtable.name,attach.attach_url")
                ->join("$gtable ON $thistable.sku=$gtable.sku AND $gtable.lang=$thistable.lang")
                ->join("(SELECT spu,MAX(sort_order),attach_url,default_flag,deleted_flag,status FROM $patable GROUP BY spu) as attach ON $thistable.spu=attach.spu AND attach.default_flag='Y' AND attach.deleted_flag='N' AND attach.status='VALID'","LEFT")
                ->where($where)
                ->limit($from, $size)
                ->select();
            if($rel){
                $data['data'] = $rel;
                $count = $this->getCount($where);
                $data['count'] = $count ? $count : 0;
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 获取记录数
     * @param $where
     * @return bool
     */
    public function getCount($where) {
        try{
            return $this->field('id')->where($where)
                ->count();
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 根据id获取详情
     * @param $id
     * @return bool|mixed
     */
  /*  public function getInfo($id){
        if(!$id || !is_numeric($id)){
            jsonReturn('', MSG::MSG_FAILED,'id不存在');
        }
        try{
            return $this->where(['id'=>$id,'deleted_at'=>['exp', 'is null']])->find();
        }catch (Exception $e){
            return false;
        }
    }*/

    /**
     * 新增
     * @param array $input
     * @return bool|mixed
     */
    public function createData($input=[]){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择spu');
        }
        if(!isset($input['sku']) || empty($input['sku'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择sku');
        }
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(!isset($input['position_id']) || empty($input['position_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择推荐位');
        }
        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择语言');
        }
        try{
            $data = [
                'special_id' => intval($input['special_id']),
                'position_id' => intval($input['position_id']),
                'spu' => trim($input['spu']),
                'sku' => trim($input['sku']),
                'sort_order' => isset($input['sort_order']) ? intval($input['sort_order']) : 0,
                'lang' => isset($input['lang']) ? strtolower($input['lang']) : 'en',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'special_id' => $data['special_id'],
                'position_id' => $data['position_id'],
                'sku' => $data['sku'],
                'deleted_at' => ['exp', 'is null']
            ];
            if(!self::exist($where)){
                return $this->add($data);
            }else{
                jsonReturn('', MSG::MSG_FAILED,'已经存在');
            }
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 修改
     * @param array $input
     * @return bool
     */
    public function updateData($input=[]){
        if(empty($input)){
            jsonReturn('', MSG::MSG_FAILED,'没有信息');
        }

        try{
            $where = ['deleted_at' => ['exp', 'is null']];
            if(isset($input['id'])){
                $where['id'] = intval($input['id']);
            }else{
                if(!isset($input['special_id']) || empty($input['special_id'])){
                    jsonReturn('', MSG::MSG_FAILED,'请选择专题');
                }
                if(!isset($input['position_id']) || empty($input['position_id'])){
                    jsonReturn('', MSG::MSG_FAILED,'请选择推荐位');
                }
                if(!isset($input['sku']) || empty($input['sku'])){
                    jsonReturn('', MSG::MSG_FAILED,'请选择sku');
                }
                if(isset($input['special_id'])){
                    $where['special_id'] = intval($input['special_id']);
                }
                if(isset($input['position_id'])){
                    $where['position_id'] = intval($input['position_id']);
                }
                if(isset($input['sku'])){
                    $where['sku'] = trim($input['sku']);
                }
            }

            $data = [
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];

            if(isset($input['sort_order'])){
                $data['sort_order'] = intval($input['sort_order']);
            }
            if(isset($input['lang'])){
                $data['lang'] = strtolower($input['lang']);
            }

            return $this->where($where)->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param array $input
     * @return bool
     */
    public function deleteData($input=[]){
        if(empty($input)){
            return false;
        }
        try{
            if(isset($input['id'])){
                if(is_array($input['id'])){
                    $where['id'] = ['in', $input['id']];
                }else{
                    $where['id'] = intval($input['id']);
                }
            }else{
                if(isset($input['special_id'])){
                    $where['special_id'] = intval($input['special_id']);
                }
                if(isset($input['position_id'])){
                    $where['position_id'] = intval($input['position_id']);
                }
                if(isset($input['sku'])){
                    if(is_array($input['sku'])){
                        $where['sku'] = ['in', $input['sku']];
                    }else{
                        $where['sku'] = trim($input['sku']);
                    }
                }
            }

            $data=[
                'deleted_by' => defined('UID') ? UID : 0,
                'deleted_at' => date('Y-m-d H:i:s', time())
            ];
            return $this->where($where)->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    private function exist($where=[]){
        if(empty($where)){
            return true;
        }
        try{
            return $this->field('id')->where($where)->find();
        }catch (Exception $e){
            return true;
        };
    }

}