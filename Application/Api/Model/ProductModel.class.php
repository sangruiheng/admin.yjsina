<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Think\Model\RelationModel;

class ProductModel extends RelationModel
{
    protected $_link = array(

        //关联头图
        'productimage' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'productimage',//要关联的表名
            'foreign_key' => 'product_id', //外键的字段名称
            'mapping_fields' => 'productimage_url',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
            'condition' => 'is_thumb  = 1',   //条件查询
        ),

        //关联轮播图
        'productimages' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'productimage',//要关联的表名
            'foreign_key' => 'product_id', //外键的字段名称
            'mapping_fields' => 'productimage_url',  //被关联表中的字段名：要变成的字段名
            //       'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
//            'condition' =>'is_thumb  = 1',   //条件查询
        ),
        'comment' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'comment',//要关联的表名
            'foreign_key' => 'product_id', //外键的字段名称
            "mapping_limit" => 1,
            'mapping_fields' => 'comment_addTime,user_id,comment_content,comment_star,comment_reply,product_attr',  //被关联表中的字段名：要变成的字段名
            'relation_deep' => 'user',   //多表关联  关联第三个表的名称
        ),
        'producttype' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'producttype',//要关联的表名
            'foreign_key' => 'producttype_id', //外键的字段名称
//            'mapping_fields' => 'productimage_url',  //被关联表中的字段名：要变成的字段名
                   'relation_deep'    =>    'productattr',   //多表关联  关联第三个表的名称
        ),
        'producttype1' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'producttype1',//要关联的表名
            'foreign_key' => 'product_id', //外键的字段名称
//            "mapping_limit" => 1,
            'mapping_fields' => 'id,color_name',  //被关联表中的字段名：要变成的字段名
            'relation_deep' => 'productarrt1',   //多表关联  关联第三个表的名称
        ),

        //商家
        'business' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'business',//要关联的表名
            'foreign_key' => 'business_id', //外键的字段名称
//            'mapping_fields' => 'productimage_url',  //被关联表中的字段名：要变成的字段名
//            'relation_deep'    =>    'productattr',   //多表关联  关联第三个表的名称
        ),

    );


}
