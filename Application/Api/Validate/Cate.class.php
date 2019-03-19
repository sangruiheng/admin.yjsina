<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class Cate extends BaseValidate
{
    protected $rule = [
        'cate_count' => 'require|isPositiveInteger',
        'product_id' => 'require|isPositiveInteger',
        'producttype_id' => 'require|isPositiveInteger',
        'productattr_id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'product_id.require' => "商品id必填",
        'product_id.isPositiveInteger' => "商品id必须是正整数",
        'cate_count.require' => "数量必填",
        'cate_count.isPositiveInteger' => "数量必须是正整数",
        'productattr_id.require' => "属性值必填",
        'productattr_id.isPositiveInteger' => "属性值必须是正整数",
        'producttype_id.require' => "属性值1必填",
        'producttype_id.isPositiveInteger' => "属性值1必须是正整数",
    ];

}