<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class PayPwd extends BaseValidate
{
    protected $rule = [
        'paypwd' => 'require|isNotEmpty|isPositiveInteger|length:6',
        'confirm_paypwd' => 'require|isNotEmpty|isPositiveInteger|length:6',
    ];

    protected $message = [
        'paypwd.isNotEmpty' => "支付密码不能为空",
        'paypwd.require' => "支付密码必填",
        'paypwd.isPositiveInteger' => "支付密码必须是正整数",
        'paypwd.length' => "支付密码为6位",
        'confirm_paypwd.isNotEmpty' => "确认支付密码不能为空",
        'confirm_paypwd.require' => "确认支付密码必填",
        'confirm_paypwd.isPositiveInteger' => "确认支付密码必须是正整数",
        'confirm_paypwd.length' => "确认支付密码为6位",
    ];

}