<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/5/30
 * Time: 9:34
 */
class StorageGoodsModel extends Model{
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
                    if($this->_exist(["storage_id" => intval(trim($input['storage_id'])), "sku" => $sku])){
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
                if($this->_exist(["storage_id" => intval(trim($input['storage_id'])), "sku" => trim($input['sku'])])){
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
     * 检测是否存在
     * 注意：当出现异常时默认按存在处理。
     * @param array $where
     * @return bool|mixed
     */
    private function _exist($where = []){
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