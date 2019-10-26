<?php

namespace App\Api\Model\DataBaseModel;

use Library\Virtual\Model\DataBaseModel\AbstractMongoModel;

/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2019/10/26
 * Time: 20:18
 */
class NumberModel extends AbstractMongoModel
{
    /**
     * AdminModel constructor.
     */
    public function __construct()
    {
        parent::__construct('test', 'number');
    }

    /**
     * 获取筛选器结果
     * @param array $filter 筛选条件
     * @return array $filter
     */
    protected function getFilter($filter = [])
    {
        // TODO: Implement getFilter() method.
        return [];
    }

    /**
     * 获取选择器结果
     * @param array $options 选择条件
     * @return array $options
     */
    protected function getOptions($options = [])
    {
        // TODO: Implement getOptions() method.
        return [];
    }
}