<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class readyAddress extends BaseValidate
{
    protected $rule = [
        'order_id' => 'require|isPositiveInteger',
        'address_id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'order_id.require' => "order_id不能为空",
        'order_id.isPositiveInteger' => "order_id必须是正整数",
        'address_id.require' => "address_id不能为空",
        'address_id.isPositiveInteger' => "address_id必须是正整数",
    ];

}