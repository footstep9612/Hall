<?php

/**
 * Excel相关操作处理
 * Trait ExcelHelperTrait
 * @author 买买提
 */
trait ExcelHelperTrait
{
    /**
     * 测试
     * @param $filename
     *
     * @return string
     */
    static public function read($filename)
    {
        return ucfirst($filename);
    }
}
