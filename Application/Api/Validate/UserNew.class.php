<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class UserNew extends BaseValidate
{
    protected $rule = [
        'tel' => 'isMobile|isPositiveInteger|require',
    ];

    protected $message = [
        'tel.isPositiveInteger' => "手机号必须是正整数",
        'tel.isMobile' => "手机号格式不正确",
        'tel.require' => "手机号必须存在",
    ];




}