<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class FeedNew extends BaseValidate
{
    protected $rule = [
        'content' => 'require|isNotEmpty',
        'tel' => 'require|isNotEmpty|isMobile',
    ];

    protected $message = [
        'content.isNotEmpty' => "内容不能为空",
        'content.require' => "内容必须存在",
        'tel.isNotEmpty' => "手机号不能为空",
        'tel.require' => "手机号必须存在",
        'tel.isMobile' => "手机号非法",
    ];

}