<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class Confirm extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'order_id' => 'require|isPositiveInteger',

    ];

    protected $message = [
        'id.isPositiveInteger' => "发货商品id必须是正整数",
        'id.require' => "发货商品id必填",
        'order_id.isPositiveInteger' => "订单id必须是正整数",
        'order_id.require' => "订单id不能为空",

    ];

}