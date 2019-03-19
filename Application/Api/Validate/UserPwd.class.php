<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class UserPwd extends BaseValidate
{
    protected $rule = [
        'pwd' => 'require|isNotEmpty|length:6,16',
        'confirm_pwd' => 'require|isNotEmpty|length:6,16',
        'ord_pwd' => 'require|isNotEmpty|length:6,16',
    ];

    protected $message = [
        'pwd.isNotEmpty' => "密码不能为空",
        'pwd.require' => "密码必填",
        'pwd.length' => "密码为6-16位",
        'confirm_pwd.isNotEmpty' => "确认密码不能为空",
        'confirm_pwd.require' => "确认密码必填",
        'confirm_pwd.length' => "确认密码为6-16位",
        'ord_pwd.isNotEmpty' => "原密码不能为空",
        'ord_pwd.require' => "原密码必填",
        'ord_pwd.length' => "原密码为6-16位",
    ];

}