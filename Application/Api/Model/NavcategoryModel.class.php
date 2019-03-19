<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Think\Model\RelationModel;

class NavcategoryModel extends RelationModel
{
    protected $_link = array(
//        'newsType' => array(
//            'mapping_type' => self::BELONGS_TO,
//            'class_name' => 'newstype',//要关联的表名
//            'foreign_key' => 'newsTypeID', //本表的字段名称
////            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
////            'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
//        ),
        'product' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'product',//要关联的表名
            'foreign_key' => 'category_id', //外键的字段名称
            'mapping_fields' => 'id,product_name,product_brand,product_type,product_djprice',  //被关联表中的字段名：要变成的字段名
                   'relation_deep'    =>    'productimage',   //多表关联  关联第三个表的名称
            'condition' => 'product_type  != 3',   //条件查询
            'mapping_order' => 'id desc',   //条件查询
        )
    );






}
