<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class CateCount extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
        'count' => 'isPositiveInteger',

    ];

    protected $message = [
        'id.isPositiveInteger' => "购物车id不能为空",
        'id.require' => "购物车id必填",
        'count.isPositiveInteger' => "数量必须是正整数",
//        'count.require' => "数量不能为空",

    ];

}