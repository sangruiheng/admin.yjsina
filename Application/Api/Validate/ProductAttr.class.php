<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class ProductAttr extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'product_id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id.isPositiveInteger' => "id必须为正整数",
        'id.require' => "id必须存在",
        'product_id.isPositiveInteger' => "product_id必须为正整数",
        'product_id.require' => "product_id必须存在",
    ];

}