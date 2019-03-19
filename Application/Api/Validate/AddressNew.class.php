<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class AddressNew extends BaseValidate
{
    protected $rule = [
        'name' => 'require|isNotEmpty',
        'mobile' => 'require|isMobile|isMobile',
        'city' => 'require|isNotEmpty',
        'detail' => 'require|isNotEmpty',
        'is_default' =>  'require'
    ];

    protected $message = [
        'name.isNotEmpty' => "姓名不能为空",
        'name.require' => "姓名必须存在",
        'mobile.isNotEmpty' => "手机号不能为空",
        'mobile.require' => "手机号必须存在",
        'mobile.isMobile' => "手机号非法",
        'city.require' => "地址必须存在",
        'city.isNotEmpty' => "地址不能为空",
        'detail.require' => "详细地址必须存在",
        'detail.isNotEmpty' => "详细地址不能为空",
    ];

}