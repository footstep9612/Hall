<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class EsVersionModel extends PublicModel {

    protected $dbName = 'erui_sys'; //数据库名称
    protected $tableName = 'es_version'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取版本
     * @param string $alias

     * @return array|mixed
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function getVersion($alias = 'erui_goods') {

        $condition['alias'] = $alias;

        if (redisGet('es_version')) {
            return json_decode(redisGet('es_version'), true);
        }
        try {
            $field = 'update_version,select_version,alias';
            $result = $this->field($field)->where($condition)->find();
            if ($result) {
                redisSet('es_version', json_encode($result));
                return $result;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 获取版本
     * @param string $alias

     * @return array|mixed
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function UpdateVersion($alias = 'erui_goods', $update_version = null, $select_version = null) {

        $condition['alias'] = $alias;
        if ($update_version) {
            $data['update_version'] = $update_version;
        }
        if ($select_version) {
            $data['select_version'] = $select_version;
        }

        try {

            $result = $this->where($condition)->save($data);

            if ($result) {
                redisDel('es_version');
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return ['alias' => 'erui_goods',
                'update_version' => null,
                'select_version' => null,
            ];
        }
    }

}
