<?php
/**
 * Created by 有情人好聚好散.
 * Author: ASang
 * Date: 18-8-9
 * Time: 上午9:50
 */

namespace Api\Model;


use Think\Model\RelationModel;

class ProducttypeModel extends RelationModel
{
    protected $_link = array(
//        'newsType' => array(
//            'mapping_type' => self::BELONGS_TO,
//            'class_name' => 'newstype',//要关联的表名
//            'foreign_key' => 'newsTypeID', //本表的字段名称
////            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
////            'relation_deep'    =>    'grouptype',   //多表关联  关联第三个表的名称
//        ),
        'productattr' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'productattr',//要关联的表名
            'foreign_key' => 'producttype_id', //外键的字段名称
//            'as_fields' => 'groupID,imgPath',  //被关联表中的字段名：要变成的字段名
                   'relation_deep'    =>    'productvalue',   //多表关联  关联第三个表的名称
        )
    );





}
