<?php
namespace Manage\Model;
use Think\Model\RelationModel;
class CommentModel extends RelationModel{
	protected $_link = array(
        'user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'user',//要关联的表名
            'foreign_key' => 'user_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        ),
        'product' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'product',//要关联的表名
            'foreign_key' => 'product_id', //本表的字段名称
//            'as_fields' => 'typeName:typeName',  //被关联表中的字段名：要变成的字段名
        )
	);

}