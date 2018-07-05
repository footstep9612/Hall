<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/5/30
 * Time: 9:34
 */
class StorageGoodsModel extends PublicModel{
    //put your code here
    protected $tableName = 'storage_goods';
    protected $dbName = 'erui_stock';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 添加
     * @param array $input
     * @return bool|mixed
     */
    public function createData( $input = [] )
    {
        try{
            if(empty($input) || empty($input['storage_id']) || empty($input['sku'])){
                return false;
            }
            if(is_array($input['sku'])){
                $data = [];
                foreach($input['sku'] as $sku){
                    if($this->getExit(["storage_id" => intval(trim($input['storage_id'])), "sku" => $sku])){
                        continue;
                    }
                    $data_item = [
                        "storage_id" => intval(trim($input['storage_id'])),
                        "sku" => $sku,
                        "created_by" => defined('UID') ? UID : 0,
                        "created_at" => date('Y-m-d H:i:s',time())
                    ];
                    $data[]=$data_item;
                }
                return $this->addAll($data);
            }else{
                if($this->getExit(["storage_id" => intval(trim($input['storage_id'])), "sku" => trim($input['sku'])])){
                    return true;
                }
                $data = [
                    "storage_id" => intval(trim($input['storage_id'])),
                    "sku" => trim($input['sku']),
                    "created_by" => defined('UID') ? UID : 0,
                    "created_at" => date('Y-m-d H:i:s',time())
                ];
                return $this->add($data);
            }
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param array $input
     * @return bool|mixed
     */
    public function deleteData( $input = [] )
    {
        try{
            if(empty($input) || (empty($input['storage_id']) && empty($input['sku']))){
                return false;
            }
            $where = [];
            if(isset($input['storage_id'])){
                $where["storage_id"] = intval(trim($input['storage_id']));
            }
            if(isset($input['sku'])){
                if(is_array($input['sku'])){
                    $where['sku'] = ['in',$input['sku']];
                }else{
                    $where['sku'] = trim($input['sku']);
                }
            }
            $data = [
                "deleted_by" => defined('UID') ? UID : 0,
                "deleted_at" => date('Y-m-d H:i:s',time())
            ];
            return $this->where($where)->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 仓库商品列表
     * @param $condition
     * @return array
     */
    public function getList($condition){
        $sModel = new StorageModel();
        $where_storage = ['id'=>intval($condition['storage_id'])];
        $storageInfo = $sModel->getInfo($where_storage);

        $thisTable = $this->getTableName();
        $where = [
            "$thisTable.storage_id" => intval($condition['storage_id']),
            "$thisTable.deleted_at" => ['exp', 'is null']
        ];

        if(isset($condition['sku'])){
            $where["$thisTable.sku"] = $condition['sku'];
        }

        $model = new StockModel();
        $stockTable = $model->getTableName();
        $field = "$thisTable.id,s.show_name,s.sku,$thisTable.created_at,$thisTable.created_by";
        list($from,$size) = $this->_getPage($condition);

        $data = [];
        $list = $this->field($field)->join("$stockTable as s ON s.country_bn='".$storageInfo['country_bn']."' AND s.lang='".$storageInfo["lang"]."' AND s.sku=".$thisTable.".sku AND s.deleted_at is null AND s.status='VALID'",'LEFT')->where($where)
            ->limit($from,$size)
            ->select();
        if($list){
            $this->_setUser($list);
            $data['data'] = $list;
            $data['count'] = $this->getCount($where);
            $data['current_no'] = isset($condition['current_no']) ? $condition['current_no'] : 1;
            $data['pagesize'] = $size;
        }
        return $data;
    }

    public function getCount($where) {

        return $this->where($where)->count();
    }

    /**
     * 检测是否存在
     * 注意：当出现异常时默认按存在处理。
     * @param array $where
     * @return bool|mixed
     */
    private function getExit($where = []){
        if(empty($where)){
            $where = 1;
        }
        try{
            if(!isset($where['deleted_at'])){
                $where['deleted_at'] = ['exp','is null'];
            }
            return $this->where($where)->find();
        }catch (Exception $e){
            return true;
        }
    }

}