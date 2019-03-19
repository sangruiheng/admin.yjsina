<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class Count extends BaseValidate
{
    protected $rule = [
        'name' => 'require|length:1,10',
        'id' => 'require',
    ];

    protected $message = [
        'name.require' => "搜索不能为空",
        'id.require' => "id不能为空",
        'name.length' => "搜索长度在1-20"
    ];

}