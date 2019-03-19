<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-8
 * Time: 下午12:22
 */

namespace Api\Validate;


class Comments extends BaseValidate
{
    protected $rule = [
        'product_id' => 'require|isPositiveInteger',
        'comment_content' => 'require',
        'comment_star' => 'require|isPositiveInteger|in:1,2,3,4,5',
        'product_attr' =>  'require'
    ];

    protected $message = [
        'product_id.require' => "商品id不能为空",
        'product_id.isPositiveInteger' => "商品id必须为正整数",
        'comment_content.require' => "评论内容不能为空",
        'comment_star.require' => "星级评价必须存在",
        'comment_star.isPositiveInteger' => "星级评价必须是正整数",
        'comment_star.in' => "星级评价在1-5之间",
        'product_attr.require' => "商品属性必须存在",
    ];

}